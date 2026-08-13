<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

use function array_key_exists;
use function is_int;
use function is_string;

/**
 * Exports Yii log messages to a PSR-3 logger.
 *
 * The Yii category, trace, and memory usage are passed as PSR-3 context. The original Yii timestamp can also be added
 * by enabling {@see addTimestampToContext}. Context keys carried by a round-tripped {@see PsrMessage} take precedence:
 * `trace`, `memory`, `category`, and `timestamp` are only added when the original context does not define them, and
 * `trace` only when Yii collected traces. {@see extractExceptionTrace} deliberately overwrites `trace` when enabled.
 *
 * @property LoggerInterface $logger The PSR-3 logger used to export messages.
 * @property list<string>|null $psrLevels Exact PSR-3 levels this target exports after Yii level and category filtering.
 * `null` means all PSR-3 levels.
 *
 * @since 22.0
 */
class PsrTarget extends Target
{
    /**
     * @var bool Whether to add the original Yii log timestamp to the PSR-3 context.
     */
    public bool $addTimestampToContext = false;
    /**
     * @var bool Whether to export an exception message and trace separately instead of stringifying the exception.
     */
    public bool $extractExceptionTrace = false;

    /**
     * @var array<int, string> Mapping from Yii levels to PSR-3 levels.
     */
    public array $levelMap = [
        Logger::LEVEL_ERROR => LogLevel::ERROR,
        Logger::LEVEL_WARNING => LogLevel::WARNING,
        Logger::LEVEL_INFO => LogLevel::INFO,
        Logger::LEVEL_TRACE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
    ];

    /**
     * @var array<string, true>
     */
    private const array PSR_LEVELS = [
        LogLevel::EMERGENCY => true,
        LogLevel::ALERT => true,
        LogLevel::CRITICAL => true,
        LogLevel::ERROR => true,
        LogLevel::WARNING => true,
        LogLevel::NOTICE => true,
        LogLevel::INFO => true,
        LogLevel::DEBUG => true,
    ];

    private LoggerInterface|null $_logger = null;

    /**
     * `null` means that all levels are enabled.
     *
     * @var array<string, true>|null
     */
    private array|null $_psrLevels = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        foreach ($this->levelMap as $yiiLevel => $psrLevel) {
            if (!is_int($yiiLevel) || !is_string($psrLevel) || !isset(self::PSR_LEVELS[$psrLevel])) {
                throw new InvalidConfigException(
                    'PsrTarget::levelMap must map integer Yii levels to valid PSR-3 level strings.',
                );
            }
        }

        $this->getLogger();
    }

    /**
     * Returns the configured PSR-3 logger.
     *
     * @throws InvalidConfigException If no logger is configured.
     */
    public function getLogger(): LoggerInterface
    {
        if ($this->_logger === null) {
            throw new InvalidConfigException(
                'PsrTarget::logger must be configured with a PSR-3 logger.',
            );
        }

        return $this->_logger;
    }

    /**
     * Sets the PSR-3 logger.
     *
     * @throws InvalidConfigException If a Yii-to-PSR adapter is configured as the destination.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        if ($logger instanceof PsrLogger) {
            throw new InvalidConfigException(
                'PsrTarget cannot use PsrLogger as its destination because that would route messages back to Yii.',
            );
        }

        $this->_logger = $logger;
    }

    /**
     * Exports accumulated messages to the configured PSR-3 logger.
     */
    public function export(): void
    {
        foreach ($this->messages as $message) {
            $text = $message[0];

            $level = $this->resolvePsrLevel($message);

            if ($this->_psrLevels !== null && !isset($this->_psrLevels[$level])) {
                continue;
            }

            $context = [];

            if ($text instanceof PsrMessage) {
                $context = $text->getContext();
                $text = $text->getMessage();
            }

            if (!array_key_exists('trace', $context) && isset($message[4]) && $message[4] !== []) {
                $context['trace'] = $message[4];
            }

            if (!array_key_exists('memory', $context) && isset($message[5])) {
                $context['memory'] = $message[5];
            }

            if (!array_key_exists('category', $context) && isset($message[2])) {
                $context['category'] = $message[2];
            }

            if (
                $this->addTimestampToContext
                && !array_key_exists('timestamp', $context)
                && isset($message[3])
            ) {
                $context['timestamp'] = $message[3];
            }

            if (!is_string($text)) {
                if ($text instanceof Throwable) {
                    $context['exception'] = $text;
                    if ($this->extractExceptionTrace) {
                        $context['trace'] = explode(PHP_EOL, $text->getTraceAsString());
                        $text = $text->getMessage();
                    } else {
                        $text = (string) $text;
                    }
                } else {
                    $text = VarDumper::export($text);
                }
            }

            $this->getLogger()->log($level, $text, $context);
        }
    }

    /**
     * Returns the exact PSR-3 levels this target exports.
     *
     * @return list<string>|null `null` means all PSR-3 levels.
     */
    public function getPsrLevels(): array|null
    {
        return $this->_psrLevels === null ? null : array_keys($this->_psrLevels);
    }

    /**
     * Sets the exact PSR-3 levels this target exports after normal Yii target filtering.
     *
     * @param list<string>|null $levels `null` or an empty array enables all PSR-3 levels.
     * @throws InvalidConfigException If a level is not recognized.
     */
    public function setPsrLevels(array|null $levels): void
    {
        if ($levels === null || $levels === []) {
            $this->_psrLevels = null;
            return;
        }

        $normalized = [];
        foreach ($levels as $level) {
            if (!is_string($level) || !isset(self::PSR_LEVELS[$level])) {
                $displayLevel = is_scalar($level) ? (string) $level : get_debug_type($level);
                throw new InvalidConfigException("Unrecognized PSR-3 log level: $displayLevel.");
            }
            $normalized[$level] = true;
        }

        $this->_psrLevels = $normalized;
    }

    /**
     * Resolves the PSR-3 level for a Yii message.
     *
     * @param array $message Yii log message.
     * @throws LogRuntimeException If the level cannot be mapped.
     */
    protected function resolvePsrLevel(array $message): string
    {
        if ($message[0] instanceof PsrMessage) {
            return $message[0]->getLevel();
        }

        $level = $message[1];

        if (is_int($level) && isset($this->levelMap[$level])) {
            return $this->levelMap[$level];
        }

        $displayLevel = is_scalar($level) ? (string) $level : get_debug_type($level);

        throw new LogRuntimeException(
            "Unable to map Yii log level '$displayLevel' to a PSR-3 level.",
        );
    }
}

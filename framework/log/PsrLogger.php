<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Stringable;
use Yii;

use function array_key_exists;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Adapts PSR-3 log calls to a Yii logger.
 *
 * When no Yii logger is passed to the constructor, the current logger returned by {@see Yii::getLogger()} is resolved
 * for every call. This ensures that replacing the global Yii logger does not leave this adapter with a stale reference.
 *
 * @since 22.0
 */
final class PsrLogger extends AbstractLogger
{
    /**
     * Default mapping from PSR-3 levels to Yii log levels.
     *
     * @var array<string, int>
     */
    public const array DEFAULT_LEVEL_MAP = [
        LogLevel::EMERGENCY => Logger::LEVEL_ERROR,
        LogLevel::ALERT => Logger::LEVEL_ERROR,
        LogLevel::CRITICAL => Logger::LEVEL_ERROR,
        LogLevel::ERROR => Logger::LEVEL_ERROR,
        LogLevel::WARNING => Logger::LEVEL_WARNING,
        LogLevel::NOTICE => Logger::LEVEL_WARNING,
        LogLevel::INFO => Logger::LEVEL_INFO,
        LogLevel::DEBUG => Logger::LEVEL_TRACE,
    ];

    /**
     * @param Logger|null $logger Yii logger to use. `null` resolves the current global Yii logger for every call.
     * @param array<string, int> $levelMap Mapping from supported PSR-3 levels to Yii log levels.
     * @param string $category Default Yii log category. A string `category` context value overrides it for that call.
     */
    public function __construct(
        private readonly Logger|null $logger = null,
        private readonly array $levelMap = self::DEFAULT_LEVEL_MAP,
        private readonly string $category = 'application',
    ) {
        foreach ($this->levelMap as $level => $yiiLevel) {
            if (!is_string($level) || !is_int($yiiLevel)) {
                throw new InvalidArgumentException('The PSR-3 level map must contain string keys and integer values.');
            }
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param array<array-key, mixed> $context
     * @throws InvalidArgumentException If the level is not supported.
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        if (!is_string($level)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The PSR-3 log level must be a string, %s given.',
                    get_debug_type($level),
                ),
            );
        }

        if (!array_key_exists($level, $this->levelMap)) {
            throw new InvalidArgumentException(
                "Unsupported PSR-3 log level: $level.",
            );
        }

        $category = isset($context['category']) && is_string($context['category'])
            ? $context['category']
            : $this->category;

        $this->getLogger()->log(
            new PsrMessage($message, $context, $level),
            $this->levelMap[$level],
            $category,
        );
    }

    /**
     * Returns the configured Yii logger or the current global logger.
     */
    public function getLogger(): Logger
    {
        return $this->logger ?? Yii::getLogger();
    }
}

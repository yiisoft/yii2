<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use yii\base\InvalidConfigException;

/**
 * PsrTarget is a log target which uses PSR-2 compatible logger
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.12
 */
class PsrTarget extends Target implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public $exportInterval = 1;

    public $levelsMap = [
        Logger::LEVEL_ERROR => 'error',
        Logger::LEVEL_WARNING => 'warning',
        Logger::LEVEL_INFO => 'info',
        Logger::LEVEL_TRACE => 'debug',
    ];

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        foreach ($this->messages as $message) {
            $this->logger->log($this->levelsMap[$message[1]],
                $message[0],
            [
                'category' => $message[2],
                'memory' => $message[5],
                'trace' => $message[4],
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    public function setLevels($levels)
    {
        if (is_array($levels)) {
            foreach ($levels as $level) {
                if (!isset($this->levelsMap[$level])) {
                    throw new InvalidConfigException('PsrTarget supports only error, warning, info and trace levels.');
                }
            }
        } else {
            $bitmapValues = array_reduce(array_flip($this->levelsMap),
                function ($carry, $item) {
                    return $carry | $item;
                });
            if (!($bitmapValues & $levels) && $levels !== 0) {
                throw new InvalidConfigException("Incorrect $levels value. PsrTarget supports only error, warning, info and trace levels.");
            }
        }

        parent::setLevels($levels);
    }
}
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\LoggerInterface;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\VarDumper;

/**
 * PsrTarget is a log target which passes messages to PSR-3 compatible logger.
 *
 * @property LoggerInterface $logger logger to be used by this target. Refer to [[setLogger()]] for details.
 *
 * @author Paul Klimov <klimov-paul@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.1
 */
class PsrTarget extends Target
{
    /**
     * @var LoggerInterface logger instance to be used for messages processing.
     */
    private $_logger;


    /**
     * Sets the PSR logger used to save messages of this target.
     * @param LoggerInterface|\Closure|array $logger logger instance or its DI compatible configuration.
     * @throws InvalidConfigException
     */
    public function setLogger($logger)
    {
        if ($logger instanceof \Closure) {
            $logger = call_user_func($logger);
        }
        $this->_logger = Instance::ensure($logger, LoggerInterface::class);
    }

    /**
     * @return LoggerInterface logger instance.
     * @throws InvalidConfigException if logger is not set.
     */
    public function getLogger()
    {
        if ($this->_logger === null) {
            throw new InvalidConfigException('"' . get_class($this) . '::$logger" must be set to be "' . LoggerInterface::class . '" instance');
        }
        return $this->_logger;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        foreach ($this->messages as $message) {
            $text = $message[1];

            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string)$text;
                } else {
                    $text = VarDumper::export($text);
                }
            }

            $this->getLogger()->log($message[0], $text, $message[2]);
        }
    }
}
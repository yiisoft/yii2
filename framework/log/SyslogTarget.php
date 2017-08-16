<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Psr\Log\LogLevel;
use Yii;
use yii\helpers\VarDumper;
use yii\profile\Profiler;

/**
 * SyslogTarget writes log to syslog.
 *
 * @author miramir <gmiramir@gmail.com>
 * @since 2.0
 */
class SyslogTarget extends Target
{
    /**
     * @var string syslog identity
     */
    public $identity;
    /**
     * @var int syslog facility.
     */
    public $facility = LOG_USER;
    /**
     * @var array syslog levels
     */
    private $_syslogLevels = [
        LogLevel::EMERGENCY => LOG_EMERG,
        LogLevel::ALERT => LOG_ALERT,
        LogLevel::CRITICAL => LOG_CRIT,
        LogLevel::ERROR => LOG_ERR,
        LogLevel::WARNING => LOG_WARNING,
        LogLevel::NOTICE => LOG_NOTICE,
        LogLevel::INFO => LOG_INFO,
        LogLevel::DEBUG => LOG_DEBUG,
    ];

    /**
     * @var int openlog options. This is a bitfield passed as the `$option` parameter to [openlog()](http://php.net/openlog).
     * Defaults to `null` which means to use the default options `LOG_ODELAY | LOG_PID`.
     * @see http://php.net/openlog for available options.
     * @since 2.0.11
     */
    public $options;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->options === null) {
            $this->options = LOG_ODELAY | LOG_PID;
        }
    }

    /**
     * Writes log messages to syslog
     */
    public function export()
    {
        openlog($this->identity, $this->options, $this->facility);
        foreach ($this->messages as $message) {
            syslog($this->_syslogLevels[$message[0]], $this->formatMessage($message));
        }
        closelog();
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        [$level, $text, $context] = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return $prefix. '[' . $level . '][' . ($context['category'] ?? '') . '] ' .$text;
    }
}

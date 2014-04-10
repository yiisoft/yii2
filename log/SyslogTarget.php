<?php
/**
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;

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
     * @var integer syslog facility.
     */
    public $facility = LOG_SYSLOG;

    /**
     * @var array syslog levels
     */
    private $syslogLevels = [
        Logger::LEVEL_TRACE => LOG_DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LOG_DEBUG,
        Logger::LEVEL_PROFILE_END => LOG_DEBUG,
        Logger::LEVEL_INFO => LOG_INFO,
        Logger::LEVEL_WARNING => LOG_WARNING,
        Logger::LEVEL_ERROR => LOG_ERR,
    ];

    /**
     * Writes log messages to syslog
     */
    public function export()
    {
        openlog($this->identity, LOG_ODELAY | LOG_PID, $this->facility);
        foreach ($this->messages as $message) {
            syslog($this->syslogLevels[$message[1]], $this->formatMessage($message));
        }
        closelog();
    }

    /**
     * @inheritdoc
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            $text = var_export($text, true);
        }

        $prefix = $this->prefix ? call_user_func($this->prefix, $message) : $this->getMessagePrefix($message);

        return "{$prefix}[$level][$category] $text";
    }
}

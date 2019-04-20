<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\helpers\VarDumper;

/**
 * SyslogTarget 将日志写入系统日志（syslog）中。
 *
 * @author miramir <gmiramir@gmail.com>
 * @since 2.0
 */
class SyslogTarget extends Target
{
    /**
     * @var string 系统日志（syslog）标识
     */
    public $identity;
    /**
     * @var int 系统日志（syslog）类型
     */
    public $facility = LOG_USER;
    /**
     * @var int openlog（打开与系统记录器的连接）选项。这是传递给函数 [openlog()](http://php.net/openlog) 中 `$option` 参数的值。
     * 当值为 `null` 时，表示使用默认值 `LOG_ODELAY | LOG_PID`。
     * @see 访问 http://php.net/openlog 可获取更多可用选项。
     * @since 2.0.11
     */
    public $options;

    /**
     * @var array 系统日志（syslog）级别
     */
    private $_syslogLevels = [
        Logger::LEVEL_TRACE => LOG_DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LOG_DEBUG,
        Logger::LEVEL_PROFILE_END => LOG_DEBUG,
        Logger::LEVEL_PROFILE => LOG_DEBUG,
        Logger::LEVEL_INFO => LOG_INFO,
        Logger::LEVEL_WARNING => LOG_WARNING,
        Logger::LEVEL_ERROR => LOG_ERR,
    ];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->options === null) {
            $this->options = LOG_ODELAY | LOG_PID;
        }
    }

    /**
     * 将日志消息写入系统日志（syslog）中。
     * 从版本 2.0.14 开始，如果日志无法导出，将抛出异常 LogRuntimeException。
     * @throws LogRuntimeException
     */
    public function export()
    {
        openlog($this->identity, $this->options, $this->facility);
        foreach ($this->messages as $message) {
            if (syslog($this->_syslogLevels[$message[1]], $this->formatMessage($message)) === false) {
                throw new LogRuntimeException('Unable to export log through system log!');
            }
        }
        closelog();
    }

    /**
     * {@inheritdoc}
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Throwable || $text instanceof \Exception) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }

        $prefix = $this->getMessagePrefix($message);
        return "{$prefix}[$level][$category] $text";
    }
}

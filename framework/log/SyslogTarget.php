<?php
/**
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log;

use Yii;
use yii\log\Target;

/**
 * SyslogTarget write log to syslog.
 *
 * @author miramir <gmiramir@gmail.com>
 * @since 2.0
 */
class SyslogTarget extends Target
{
    /**
     * @var string Syslog identity
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
		'trace' => LOG_DEBUG,
		'info' => LOG_INFO,
		'profile' => LOG_NOTICE,
		'warning' => LOG_WARNING,
		'error' => LOG_ERR,
	];

    /**
     * Writes log messages to a syslog.
     */
    public function export()
    {
        openlog($this->identity, LOG_ODELAY | LOG_PID, $this->facility);
        foreach($this->messages as $message){
            list($text, $level, $category, $timestamp) = $message;
            syslog($this->syslogLevels[$level], $category . ':' . $text);
        }
        closelog();
    }
}

<?php
/**
 * Logger class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use yii\base\Event;
use yii\base\Exception;

/**
 * Logger records logged messages in memory.
 *
 * When [[flushInterval()]] is reached or when application terminates, it will
 * call [[flush()]] to send logged messages to different log targets, such as
 * file, email, Web.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends \yii\base\Component
{
	/**
	 * @event Event an event that is triggered when [[flush()]] is called.
	 */
	const EVENT_FLUSH = 'flush';
	/**
	 * @event Event an event that is triggered when [[flush()]] is called at the end of application.
	 */
	const EVENT_FINAL_FLUSH = 'finalFlush';

	/**
	 * Error message level. An error message is one that indicates the abnormal termination of the
	 * application and may require developer's handling.
	 */
	const LEVEL_ERROR = 0x01;
	/**
	 * Warning message level. A warning message is one that indicates some abnormal happens but
	 * the application is able to continue to run. Developers should pay attention to this message.
	 */
	const LEVEL_WARNING = 0x02;
	/**
	 * Informational message level. An informational message is one that includes certain information
	 * for developers to review.
	 */
	const LEVEL_INFO = 0x04;
	/**
	 * Tracing message level. An tracing message is one that reveals the code execution flow.
	 */
	const LEVEL_TRACE = 0x08;
	/**
	 * Profiling message level. This indicates the message is for profiling purpose.
	 */
	const LEVEL_PROFILE = 0x40;
	/**
	 * Profiling message level. This indicates the message is for profiling purpose. It marks the
	 * beginning of a profiling block.
	 */
	const LEVEL_PROFILE_BEGIN = 0x50;
	/**
	 * Profiling message level. This indicates the message is for profiling purpose. It marks the
	 * end of a profiling block.
	 */
	const LEVEL_PROFILE_END = 0x60;


	/**
	 * @var integer how many messages should be logged before they are flushed from memory and sent to targets.
	 * Defaults to 1000, meaning the [[flush]] method will be invoked once every 1000 messages logged.
	 * Set this property to be 0 if you don't want to flush messages until the application terminates.
	 * This property mainly affects how much memory will be taken by the logged messages.
	 * A smaller value means less memory, but will increase the execution time due to the overhead of [[flush()]].
	 */
	public $flushInterval = 1000;
	/**
	 * @var array logged messages. This property is mainly managed by [[log()]] and [[flush()]].
	 * Each log message is of the following structure:
	 *
	 * ~~~
	 * array(
	 *   [0] => message (mixed)
	 *   [1] => level (integer)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true))
	 * )
	 * ~~~
	 */
	public $messages = array();

	/**
	 * Initializes the logger by registering [[flush()]] as a shutdown function.
	 */
	public function init()
	{
		parent::init();
		register_shutdown_function(array($this, 'flush'), true);
	}

	/**
	 * Logs a message with the given type and category.
	 * If `YII_DEBUG` is true and `YII_TRACE_LEVEL` is greater than 0, then additional
	 * call stack information about application code will be appended to the message.
	 * @param string $message the message to be logged.
	 * @param integer $level the level of the message. This must be one of the following:
	 * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
	 * `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`.
	 * @param string $category the category of the message.
	 */
	public function log($message, $level, $category = 'application')
	{
		$time = microtime(true);
		if (YII_DEBUG && YII_TRACE_LEVEL > 0) {
			$traces = debug_backtrace();
			$count = 0;
			foreach ($traces as $trace) {
				if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII_PATH) !== 0) {
					$message .= "\nin {$trace['file']} ({$trace['line']})";
					if (++$count >= YII_TRACE_LEVEL) {
						break;
					}
				}
			}
		}
		$this->messages[] = array($message, $level, $category, $time);
		if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
			$this->flush();
		}
	}

	/**
	 * Flushes log messages from memory to targets.
	 * This method will trigger an [[EVENT_FLUSH]] or [[EVENT_FINAL_FLUSH]] event depending on the $final value.
	 * @param boolean $final whether this is a final call during a request.
	 */
	public function flush($final = false)
	{
		$this->trigger($final ? 'finalFlush' : 'flush');
		$this->messages = array();
	}

	/**
	 * Returns the total elapsed time since the start of the current request.
	 * This method calculates the difference between now and the timestamp
	 * defined by constant `YII_BEGIN_TIME` which is evaluated at the beginning
	 * of [[YiiBase]] class file.
	 * @return float the total elapsed time in seconds for current request.
	 */
	public function getExecutionTime()
	{
		return microtime(true) - YII_BEGIN_TIME;
	}

	/**
	 * Returns the profiling results.
	 *
	 * By default, all profiling results will be returned. You may provide
	 * `$categories` and `$excludeCategories` as parameters to retrieve the
	 * results that you are interested in.
	 *
	 * @param array $categories list of categories that you are interested in.
	 * You can use an asterisk at the end of a category to do a prefix match.
	 * For example, 'yii\db\*' will match categories starting with 'yii\db\',
	 * such as 'yii\db\Connection'.
	 * @param array $excludeCategories list of categories that you want to exclude
	 * @return array the profiling results. Each array element has the following structure:
	 *  `array($token, $category, $time)`.
	 */
	public function getProfiling($categories = array(), $excludeCategories = array())
	{
		$timings = $this->calculateTimings();
		if (empty($categories) && empty($excludeCategories)) {
			return $timings;
		}

		foreach ($timings as $i => $timing) {
			$matched = empty($categories);
			foreach ($categories as $category) {
				$prefix = rtrim($category, '*');
				if (strpos($timing[1], $prefix) === 0 && ($timing[1] === $category || $prefix !== $category)) {
					$matched = true;
					break;
				}
			}

			if ($matched) {
				foreach ($excludeCategories as $category) {
					$prefix = rtrim($category, '*');
					foreach ($timings as $i => $timing) {
						if (strpos($timing[1], $prefix) === 0 && ($timing[1] === $category || $prefix !== $category)) {
							$matched = false;
							break;
						}
					}
				}
			}

			if (!$matched) {
				unset($timings[$i]);
			}
		}
		return array_values($timings);
	}

	private function calculateTimings()
	{
		$timings = array();

		$stack = array();
		foreach ($this->messages as $log) {
			list($token, $level, $category, $timestamp) = $log;
			if ($level == self::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($level == self::LEVEL_PROFILE_END) {
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[] = array($token, $category, $timestamp - $last[3]);
				} else {
					throw new Exception("Unmatched profiling block: $token");
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$timings[] = array($last[0], $last[2], $delta);
		}

		return $timings;
	}

}

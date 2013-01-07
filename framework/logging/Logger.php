<?php
/**
 * Logger class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * Logger records logged messages in memory.
 *
 * When [[flushInterval]] is reached or when application terminates, it will
 * call [[flush]] to send logged messages to different log targets, such as
 * file, email, Web.
 *
 * Logger provides a set of events for further customization:
 *
 * - `flush`. Raised on logs flush.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends \yii\base\Component
{
	const LEVEL_ERROR = 'error';
	const LEVEL_WARNING = 'warning';
	const LEVEL_INFO = 'info';
	const LEVEL_TRACE = 'trace';
	const LEVEL_PROFILE_BEGIN = 'profile-begin';
	const LEVEL_PROFILE_END = 'profile-end';

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
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true))
	 * )
	 * ~~~
	 */
	public $messages = array();

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 * @param mixed $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function error($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_ERROR, $category);
	}

	/**
	 * Logs a trace message.
	 * Trace messages are logged mainly for development purpose to see
	 * the execution work flow of some code.
	 * @param mixed $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function trace($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_TRACE, $category);
	}

	/**
	 * Logs a warning message.
	 * A warning message is typically logged when an error occurs while the execution
	 * can still continue.
	 * @param mixed $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function warning($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_WARNING, $category);
	}

	/**
	 * Logs an informative message.
	 * An informative message is typically logged by an application to keep record of
	 * something important (e.g. an administrator logs in).
	 * @param mixed $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function info($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_INFO, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see endProfile
	 */
	public function beginProfile($token, $category = 'application')
	{
		$this->log($token, self::LEVEL_PROFILE_BEGIN, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $token token for the code block
	 * @param string $category the category of this log message
	 * @see beginProfile
	 */
	public function endProfile($token, $category = 'application')
	{
		$this->log($token, self::LEVEL_PROFILE_END, $category);
	}

	/**
	 * Logs a message with the given type and category.
	 * If `YII_DEBUG` is true and `YII_TRACE_LEVEL` is greater than 0, then additional
	 * call stack information about application code will be appended to the message.
	 * @param string $message the message to be logged.
	 * @param string $level the level of the message. This must be one of the following:
	 * 'trace', 'info', 'warning', 'error', 'profile'.
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
		if (count($this->messages) >= $this->flushInterval && $this->flushInterval > 0) {
			$this->flush();
		}
	}

	/**
	 * Removes all recorded messages from the memory.
	 * This method will raise a `flush` event.
	 */
	public function flush()
	{
		$this->trigger('flush');
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
	 * @param array $excludeCategories list of categories that you are interested in.
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
			if ($log[1] === self::LEVEL_PROFILE_BEGIN) {
				$stack[] = $log;
			} elseif ($log[1] === self::LEVEL_PROFILE_END) {
				list($token, $level, $category, $timestamp) = $log;
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$timings[] = array($token, $category, $timestamp - $last[3]);
				} else {
					throw new \yii\base\Exception("Unmatched profiling block: $token");
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

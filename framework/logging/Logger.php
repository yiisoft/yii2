<?php
/**
 * Logger class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
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
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Logger extends \yii\base\Component
{
	const LEVEL_TRACE = 'trace';
	const LEVEL_WARN = 'warn';
	const LEVEL_ERROR = 'error';
	const LEVEL_INFO = 'info';
	const LEVEL_PROFILE = 'profile';

	/**
	 * @var integer how many messages should be logged before they are flushed from memory and sent to targets.
	 * Defaults to 1000, meaning the [[flush]] method will be invoked once every 1000 messages logged.
	 * Set this property to be 0 if you don't want to flush messages until the application terminates.
	 * This property mainly affects how much memory will be taken by the logged messages.
	 * A smaller value means less memory, but will increase the execution time due to the overhead of [[flush]].
	 */
	public $flushInterval = 1000;
	/**
	 * @var boolean this property will be passed as the parameter to [[flush]] when it is
	 * called due to the [[flushInterval]] is reached. Defaults to false, meaning the flushed
	 * messages are still kept in the memory by each log target. If this is true, they will
	 * be exported to the actual storage medium (e.g. DB, email) defined by each log target.
	 * @see flushInterval
	 */
	public $autoExport = false;
	/**
	 * @var array logged messages. This property is mainly managed by [[log]] and [[flush]].
	 */
	public $messages = array();
	/**
	 * @var array the profiling results (category, token => time in seconds)
	 */
	private $_timings;

	/**
	 * Logs an error message.
	 * An error message is typically logged when an unrecoverable error occurs
	 * during the execution of an application.
	 * @param string $message the message to be logged.
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
	 * @param string $message the message to be logged.
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
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function warn($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_TRACE, $category);
	}

	/**
	 * Logs an informative message.
	 * An informative message is typically logged by an application to keep record of
	 * something important (e.g. an administrator logs in).
	 * @param string $message the message to be logged.
	 * @param string $category the category of the message.
	 */
	public function info($message, $category = 'application')
	{
		$this->log($message, self::LEVEL_TRACE, $category);
	}

	/**
	 * Marks the beginning of a code block for profiling.
	 * This has to be matched with a call to [[endProfile]] with the same category name.
	 * The begin- and end- calls must also be properly nested. For example,
	 * @param string $category the category of this profile block
	 * @see endProfile
	 */
	public function beginProfile($category)
	{
		$this->log('begin', self::LEVEL_PROFILE, $category);
	}

	/**
	 * Marks the end of a code block for profiling.
	 * This has to be matched with a previous call to [[beginProfile]] with the same category name.
	 * @param string $category the category of this profile block
	 * @see beginProfile
	 */
	public function endProfile($category)
	{
		$this->log('end', self::LEVEL_PROFILE, $category);
	}

	/**
	 * Logs a message with the given type and category.
	 * If `YII_DEBUG` is true and `YII_TRACE_LEVEL` is greater than 0, then additional
	 * call stack information about application code will be appended to the message.
	 * @param string $message the message to be logged.
	 * @param string $level the level of the message. This must be one of the following:
	 * 'trace', 'info', 'warn', 'error', 'profile'.
	 * @param string $category the category of the message.
	 */
	public function log($message, $level, $category)
	{
		if (YII_DEBUG && YII_TRACE_LEVEL > 0 && $level !== self::LEVEL_PROFILE) {
			$traces = debug_backtrace();
			$count = 0;
			foreach ($traces as $trace) {
				if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII_DIR) !== 0) {
					$message .= "\nin " . $trace['file'] . ' (' . $trace['line'] . ')';
					if (++$count >= YII_TRACE_LEVEL) {
						break;
					}
				}
			}
		}

		$this->messages[] = array($message, $level, $category, microtime(true));
		if (count($this->messages) >= $this->flushInterval && $this->flushInterval > 0) {
			$this->flush($this->autoExport);
		}
	}

	/**
	 * Retrieves log messages.
	 *
	 * Messages may be filtered by log levels and/or categories.
	 * A level filter is specified by a list of levels separated by comma or space
	 * (e.g. 'trace, error'). A category filter is similar to level filter
	 * (e.g. 'system, system.web'). A difference is that in category filter
	 * you can use pattern like 'system.*' to indicate all categories starting
	 * with 'system'.
	 *
	 * If you do not specify level filter, it will bring back logs at all levels.
	 * The same applies to category filter.
	 *
	 * Level filter and category filter are combinational, i.e., only messages
	 * satisfying both filter conditions will be returned.
	 *
	 * @param string $levels level filter
	 * @param string $categories category filter
	 * @return array list of messages. Each array elements represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	public function getLogs($levels = '', $categories = '')
	{
		$this->_levels = preg_split('/[\s,]+/', strtolower($levels), -1, PREG_SPLIT_NO_EMPTY);
		$this->_categories = preg_split('/[\s,]+/', strtolower($categories), -1, PREG_SPLIT_NO_EMPTY);
		if (empty($levels) && empty($categories))
			return $this->_logs;
		elseif (empty($levels))
			return array_values(array_filter(array_filter($this->_logs, array($this, 'filterByCategory'))));
		elseif (empty($categories))
			return array_values(array_filter(array_filter($this->_logs, array($this, 'filterByLevel'))));
		else
		{
			$ret = array_values(array_filter(array_filter($this->_logs, array($this, 'filterByLevel'))));
			return array_values(array_filter(array_filter($ret, array($this, 'filterByCategory'))));
		}
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array $value element to be filtered
	 * @return array valid log, false if not.
	 */
	private function filterByCategory($value)
	{
		foreach ($this->_categories as $category)
		{
			$cat = strtolower($value[2]);
			if ($cat === $category || (($c = rtrim($category, '.*')) !== $category && strpos($cat, $c) === 0))
				return $value;
		}
		return false;
	}

	/**
	 * Filter function used by {@link getLogs}
	 * @param array $value element to be filtered
	 * @return array valid log, false if not.
	 */
	private function filterByLevel($value)
	{
		return in_array(strtolower($value[1]), $this->_levels) ? $value : false;
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
	 * The results may be filtered by token and/or category.
	 * If no filter is specified, the returned results would be an array with each element
	 * being `array($token, $category, $time)`.
	 * If a filter is specified, the results would be an array of timings.
	 * @param string $token token filter. Defaults to null, meaning not filtered by token.
	 * @param string $category category filter. Defaults to null, meaning not filtered by category.
	 * @param boolean $refresh whether to refresh the internal timing calculations. If false,
	 * only the first time calling this method will the timings be calculated internally.
	 * @return array the profiling results.
	 */
	public function getProfilingResults($token = null, $category = null, $refresh = false)
	{
		if ($this->_timings === null || $refresh) {
			$this->calculateTimings();
		}
		if ($token === null && $category === null) {
			return $this->_timings;
		}
		$results = array();
		foreach ($this->_timings as $timing) {
			if (($category === null || $timing[1] === $category) && ($token === null || $timing[0] === $token)) {
				$results[] = $timing[2];
			}
		}
		return $results;
	}

	private function calculateTimings()
	{
		$this->_timings = array();

		$stack = array();
		foreach ($this->messages as $log) {
			if ($log[1] !== self::LEVEL_PROFILE) {
				continue;
			}
			list($message, $level, $category, $timestamp) = $log;
			if (!strncasecmp($message, 'begin:', 6)) {
				$log[0] = substr($message, 6);
				$stack[] = $log;
			}
			elseif (!strncasecmp($message, 'end:', 4)) {
				$token = substr($message, 4);
				if (($last = array_pop($stack)) !== null && $last[0] === $token) {
					$delta = $log[3] - $last[3];
					$this->_timings[] = array($message, $category, $delta);
				}
				else {
					throw new \yii\base\Exception('Found a mismatching profiling block: ' . $token);
				}
			}
		}

		$now = microtime(true);
		while (($last = array_pop($stack)) !== null) {
			$delta = $now - $last[3];
			$this->_timings[] = array($last[0], $last[2], $delta);
		}
	}

	/**
	 * Removes all recorded messages from the memory.
	 * This method will raise an {@link onFlush} event.
	 * The attached event handlers can process the log messages before they are removed.
	 * @param boolean $export whether to notify log targets to export the filtered messages they have received.
	 */
	public function flush($export = false)
	{
		$this->onFlush(new \yii\base\Event($this, array('export' => $export)));
		$this->messages = array();
	}

	/**
	 * Raises an `onFlush` event.
	 * @param \yii\base\Event $event the event parameter
	 */
	public function onFlush($event)
	{
		$this->raiseEvent('onFlush', $event);
	}
}

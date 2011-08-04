<?php
/**
 * Target class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * Target is the base class for all log target classes.
 *
 * A log target object retrieves log messages from a logger and sends it
 * somewhere, such as files, emails.
 * The messages being retrieved may be filtered first before being sent
 * to the destination. The filters include log level filter and log category filter.
 *
 * To specify level filter, set {@link levels} property,
 * which takes a string of comma-separated desired level names (e.g. 'Error, Debug').
 * To specify category filter, set {@link categories} property,
 * which takes a string of comma-separated desired category names (e.g. 'System.Web, System.IO').
 *
 * Level filter and category filter are combinational, i.e., only messages
 * satisfying both filter conditions will they be returned.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Target extends \yii\base\Component implements \yii\base\Initable
{
	/**
	 * @var boolean whether to enable this log target. Defaults to true.
	 */
	public $enabled = true;
	/**
	 * @var string list of levels separated by comma or space. Defaults to empty, meaning all levels.
	 */
	public $levels;
	/**
	 * @var string list of categories separated by comma or space. Defaults to empty, meaning all categories.
	 */
	public $categories;
	/**
	 * @var string list of categories that should be excluded.
	 */
	public $excludeCategories;
	/**
	 * @var mixed the additional filter (eg {@link CLogFilter}) that can be applied to the log messages.
	 * The value of this property will be passed to {@link Yii::createComponent} to create
	 * a log filter object. As a result, this can be either a string representing the
	 * filter class name or an array representing the filter configuration.
	 * In general, the log filter class should be {@link CLogFilter} or a child class of it.
	 * Defaults to null, meaning no filter will be used.
	 */
	public $filter;
	/**
	 * @var array the messages that are collected so far by this log target.
	 */
	public $messages;

	/**
	 * Pre-initializes this component.
	 * This method is required by the [[Initable]] interface. It is invoked by
	 * [[\Yii::createComponent]] after its creates the new component instance but
	 * BEFORE the component properties are initialized.
	 *
	 * You may override this method to do work such as setting property default values.
	 */
	public function preinit()
	{
	}

	/**
	 * Initializes this component.
	 * This method is invoked after the component is created and its property values are
	 * initialized.
	 */
	public function init()
	{
	}

	/**
	 * Formats a log message given different fields.
	 * @param string $message message content
	 * @param integer $level message level
	 * @param string $category message category
	 * @param integer $time timestamp
	 * @return string formatted message
	 */
	protected function formatMessage($message, $level, $category, $time)
	{
		return @date('Y/m/d H:i:s', $time) . " [$level] [$category] $message\n";
	}

	/**
	 * Retrieves filtered log messages from logger for further processing.
	 * @param CLogger $logger logger instance
	 * @param boolean $processLogs whether to process the messages after they are collected from the logger
	 */
	public function processMessages($logger, $export)
	{
		$messages = $logger->getLogs($this->levels, $this->categories);
		$this->messages = empty($this->messages) ? $messages : array_merge($this->messages, $messages);
		if ($processLogs && !empty($this->messages))
		{
			if ($this->filter !== null)
				Yii::createComponent($this->filter)->filter($this->messages);
			$this->processLogs($this->messages);
			$this->messages = array();
		}
	}

	protected function filterMessages($levels = '', $categories = '')
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
	protected function filterByCategory($value)
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
	protected function filterByLevel($value)
	{
		return in_array(strtolower($value[1]), $this->_levels) ? $value : false;
	}

	/**
	 * Processes log messages and sends them to specific destination.
	 * Derived child classes must implement this method.
	 * @param array $messages list of messages.  Each array elements represents one message
	 * with the following structure:
	 * array(
	 *   [0] => message (string)
	 *   [1] => level (string)
	 *   [2] => category (string)
	 *   [3] => timestamp (float, obtained by microtime(true));
	 */
	abstract protected function processLogs($messages);
}

<?php
/**
 * Target class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * Target is the base class for all log target classes.
 *
 * A log target object will filter the messages logged by [[Logger]] according
 * to its [[levels]] and [[categories]] properties. It may also export the filtered
 * messages to specific destination defined by the target, such as emails, files.
 *
 * Level filter and category filter are combinatorial, i.e., only messages
 * satisfying both filter conditions will be handled. Additionally, you
 * may specify [[except]] to exclude messages of certain categories.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Target extends \yii\base\Component
{
	/**
	 * @var boolean whether to enable this log target. Defaults to true.
	 */
	public $enabled = true;
	/**
	 * @var array list of message levels that this target is interested in. Defaults to empty, meaning all levels.
	 */
	public $levels = array();
	/**
	 * @var array list of message categories that this target is interested in. Defaults to empty, meaning all categories.
	 * You can use an asterisk at the end of a category so that the category may be used to
	 * match those categories sharing the same common prefix. For example, 'yii\db\*' will match
	 * categories starting with 'yii\db\', such as 'yii\db\Connection'.
	 */
	public $categories = array();
	/**
	 * @var array list of message categories that this target is NOT interested in. Defaults to empty, meaning no uninteresting messages.
	 * If this property is not empty, then any category listed here will be excluded from [[categories]].
	 * You can use an asterisk at the end of a category so that the category can be used to
	 * match those categories sharing the same common prefix. For example, 'yii\db\*' will match
	 * categories starting with 'yii\db\', such as 'yii\db\Connection'.
	 * @see categories
	 */
	public $except = array();
	/**
	 * @var boolean whether to prefix each log message with the current session ID. Defaults to false.
	 */
	public $prefixSession = false;
	/**
	 * @var boolean whether to prefix each log message with the current user name and ID. Defaults to false.
	 * @see \yii\web\User
	 */
	public $prefixUser = false;
	/**
	 * @var boolean whether to log a message containing the current user name and ID. Defaults to false.
	 * @see \yii\web\User
	 */
	public $logUser = false;
	/**
	 * @var array list of the PHP predefined variables that should be logged in a message.
	 * Note that a variable must be accessible via `$GLOBALS`. Otherwise it won't be logged.
	 * Defaults to `array('_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER')`.
	 */
	public $logVars = array('_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER');
	/**
	 * @var boolean whether this target should export the collected messages to persistent storage
	 * (e.g. DB, email) whenever [[processMessages()]] is called. Defaults to true. If false,
	 * the collected messages will be stored in [[messages]] without any further processing.
	 */
	public $autoExport = true;
	/**
	 * @var array the messages that are retrieved from the logger so far by this log target.
	 * @see autoExport
	 */
	public $messages = array();

	/**
	 * Exports log messages to a specific destination.
	 * Child classes must implement this method. Note that you may need
	 * to clean up [[messages]] in this method to avoid re-exporting messages.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	abstract public function exportMessages($final);

	/**
	 * Processes the given log messages.
	 * This method will filter the given messages with [[levels]] and [[categories]].
	 * And if requested, it will also export the filtering result to specific medium (e.g. email).
	 * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
	 * of each message.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function processMessages($messages, $final)
	{
		$messages = $this->filterMessages($messages);
		$this->messages = array_merge($this->messages, $messages);

		if (!empty($this->messages) && ($this->autoExport || $final)) {
			$this->prepareExport($final);
			$this->exportMessages($final);
			$this->messages = array();
		}
	}

	/**
	 * Prepares the [[messages]] for exporting.
	 * This method will modify each message by prepending extra information
	 * if [[prefixSession]] and/or [[prefixUser]] are set true.
	 * It will also add an additional message showing context information if
	 * [[logUser]] and/or [[logVars]] are set.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	protected function prepareExport($final)
	{
		$prefix = array();
		if ($this->prefixSession && ($id = session_id()) !== '') {
			$prefix[] = "[$id]";
		}
		if ($this->prefixUser && ($user = \Yii::$application->getComponent('user', false)) !== null) {
			$prefix[] = '[' . $user->getName() . ']';
			$prefix[] = '[' . $user->getId() . ']';
		}
		if ($prefix !== array()) {
			$prefix = implode(' ', $prefix);
			foreach ($this->messages as $i => $message) {
				$this->messages[$i][0] = $prefix . ' ' . $this->messages[$i][0];
			}
		}
		if ($final && ($context = $this->getContextMessage()) !== '') {
			$this->messages[] = array($context, Logger::LEVEL_INFO, 'application', YII_BEGIN_TIME);
		}
	}

	/**
	 * Generates the context information to be logged.
	 * The default implementation will dump user information, system variables, etc.
	 * @return string the context information. If an empty string, it means no context information.
	 */
	protected function getContextMessage()
	{
		$context = array();
		if ($this->logUser && ($user = \Yii::$application->getComponent('user', false)) !== null) {
			$context[] = 'User: ' . $user->getName() . ' (ID: ' . $user->getId() . ')';
		}

		foreach ($this->logVars as $name) {
			if (!empty($GLOBALS[$name])) {
				$context[] = "\${$name} = " . var_export($GLOBALS[$name], true);
			}
		}

		return implode("\n\n", $context);
	}

	/**
	 * Filters the given messages according to their categories and levels.
	 * @param array $messages messages to be filtered
	 * @return array the filtered messages.
	 * @see filterByCategory
	 * @see filterByLevel
	 */
	protected function filterMessages($messages)
	{
		foreach ($messages as $i => $message) {
			if (!empty($this->levels) && !in_array($message[1], $this->levels)) {
				unset($messages[$i]);
				continue;
			}

			$matched = empty($this->categories);
			foreach ($this->categories as $category) {
				$prefix = rtrim($category, '*');
				if (strpos($message[2], $prefix) === 0 && ($message[2] === $category || $prefix !== $category)) {
					$matched = true;
					break;
				}
			}

			if ($matched) {
				foreach ($this->except as $category) {
					$prefix = rtrim($category, '*');
					foreach ($messages as $i => $message) {
						if (strpos($message[2], $prefix) === 0 && ($message[2] === $category || $prefix !== $category)) {
							$matched = false;
							break;
						}
					}
				}
			}

			if (!$matched) {
				unset($messages[$i]);
			}
		}
		return $messages;
	}

	/**
	 * Formats a log message.
	 * The message structure follows that in [[Logger::messages]].
	 * @param array $message the log message to be formatted.
	 * @return string the formatted message
	 */
	public function formatMessage($message)
	{
		$s = is_string($message[0]) ? $message[0] : var_export($message[0], true);
		return date('Y/m/d H:i:s', $message[3]) . " [{$message[1]}] [{$message[2]}] $s\n";
	}
}

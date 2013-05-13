<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use yii\base\Component;
use yii\base\InvalidConfigException;

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
 * @property integer $levels the message levels that this target is interested in.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Target extends Component
{
	/**
	 * @var boolean whether to enable this log target. Defaults to true.
	 */
	public $enabled = true;
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
	 * @var integer how many messages should be accumulated before they are exported.
	 * Defaults to 1000. Note that messages will always be exported when the application terminates.
	 * Set this property to be 0 if you don't want to export messages until the application terminates.
	 */
	public $exportInterval = 1000;
	/**
	 * @var array the messages that are retrieved from the logger so far by this log target.
	 */
	public $messages = array();

	private $_levels = 0;

	/**
	 * Exports log messages to a specific destination.
	 * Child classes must implement this method.
	 * @param array $messages the messages to be exported. See [[Logger::messages]] for the structure
	 * of each message.
	 */
	abstract public function export($messages);

	/**
	 * Processes the given log messages.
	 * This method will filter the given messages with [[levels]] and [[categories]].
	 * And if requested, it will also export the filtering result to specific medium (e.g. email).
	 * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
	 * of each message.
	 * @param boolean $final whether this method is called at the end of the current application
	 */
	public function collect($messages, $final)
	{
		$this->messages = array_merge($this->messages, $this->filterMessages($messages));
		$count = count($this->messages);
		if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
			if (($context = $this->getContextMessage()) !== '') {
				$this->messages[] = array($context, Logger::LEVEL_INFO, 'application', YII_BEGIN_TIME);
			}
			$this->export($this->messages);
			$this->messages = array();
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
		if ($this->logUser && ($user = \Yii::$app->getComponent('user', false)) !== null) {
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
	 * @return integer the message levels that this target is interested in. This is a bitmap of
	 * level values. Defaults to 0, meaning  all available levels.
	 */
	public function getLevels()
	{
		return $this->_levels;
	}

	/**
	 * Sets the message levels that this target is interested in.
	 *
	 * The parameter can be either an array of interested level names or an integer representing
	 * the bitmap of the interested level values. Valid level names include: 'error',
	 * 'warning', 'info', 'trace' and 'profile'; valid level values include:
	 * [[Logger::LEVEL_ERROR]], [[Logger::LEVEL_WARNING]], [[Logger::LEVEL_INFO]],
	 * [[Logger::LEVEL_TRACE]] and [[Logger::LEVEL_PROFILE]].
	 *
	 * For example,
	 *
	 * ~~~
	 * array('error', 'warning')
	 * // which is equivalent to:
	 * Logger::LEVEL_ERROR | Logger::LEVEL_WARNING
	 * ~~~
	 *
	 * @param array|integer $levels message levels that this target is interested in.
	 * @throws InvalidConfigException if an unknown level name is given
	 */
	public function setLevels($levels)
	{
		static $levelMap = array(
			'error' => Logger::LEVEL_ERROR,
			'warning' => Logger::LEVEL_WARNING,
			'info' => Logger::LEVEL_INFO,
			'trace' => Logger::LEVEL_TRACE,
			'profile' => Logger::LEVEL_PROFILE,
		);
		if (is_array($levels)) {
			$this->_levels = 0;
			foreach ($levels as $level) {
				if (isset($levelMap[$level])) {
					$this->_levels |= $levelMap[$level];
				} else {
					throw new InvalidConfigException("Unrecognized level: $level");
				}
			}
		} else {
			$this->_levels = $levels;
		}
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
		$levels = $this->getLevels();

		foreach ($messages as $i => $message) {
			if ($levels && !($levels & $message[1])) {
				unset($messages[$i]);
				continue;
			}

			$matched = empty($this->categories);
			foreach ($this->categories as $category) {
				if ($message[2] === $category || substr($category, -1) === '*' && strpos($message[2], rtrim($category, '*')) === 0) {
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
		static $levels = array(
			Logger::LEVEL_ERROR => 'error',
			Logger::LEVEL_WARNING => 'warning',
			Logger::LEVEL_INFO => 'info',
			Logger::LEVEL_TRACE => 'trace',
			Logger::LEVEL_PROFILE_BEGIN => 'profile begin',
			Logger::LEVEL_PROFILE_END => 'profile end',
		);
		list($text, $level, $category, $timestamp) = $message;
		$level = isset($levels[$level]) ? $levels[$level] : 'unknown';
		if (!is_string($text)) {
			$text = var_export($text, true);
		}
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		return date('Y/m/d H:i:s', $timestamp) . " [$ip] [$level] [$category] $text\n";
	}
}

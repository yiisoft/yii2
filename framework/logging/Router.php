<?php
/**
 * Router class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

/**
 * Router manages [[Target|log targets]] that record log messages in different media.
 *
 * For example, a [[FileTarget|file log target]] records log messages
 * in files; an [[EmailTarget|email log target]] sends log messages
 * to specific email addresses. Each log target may specify filters on
 * message levels and categories to record specific messages only.
 *
 * Router and the targets it manages may be configured in application configuration,
 * like the following:
 *
 * ~~~
 * array(
 *     // preload log component when application starts
 *     'preload' => array('log'),
 *     'components' => array(
 *         'log' => array(
 *             'class' => '\yii\logging\Router',
 *             'targets' => array(
 *                 'file' => array(
 *                     'class' => '\yii\logging\FileTarget',
 *                     'levels' => 'trace, info',
 *                     'categories' => 'yii\*',
 *                 ),
 *                 'email' => array(
 *                     'class' => '\yii\logging\EmailTarget',
 *                     'levels' => 'error, warning',
 *                     'emails' => array('admin@example.com'),
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 * ~~~
 *
 * Each log target can have a name and can be referenced via the [[targets]] property
 * as follows:
 *
 * ~~~
 * \Yii::app()->log->targets['file']->enabled = false;
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Router extends \yii\base\ApplicationComponent
{
	private $_targets;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_targets = new \yii\base\Dictionary;
	}

	/**
	 * Initializes this application component.
	 * This method is invoked when the Router component is created by the application.
	 * The method attaches the [[processLogs]] method to both the [[Logger::onFlush]] event
	 * and the [[\yii\base\Application::onEndRequest]] event.
	 */
	public function init()
	{
		parent::init();
		\Yii::getLogger()->attachEventHandler('onFlush', array($this, 'processMessages'));
		if (($app = \Yii::app()) !== null) {
			$app->attachEventHandler('onEndRequest', array($this, 'processMessages'));
		}
	}

	/**
	 * Returns the log targets managed by this log router.
	 * The keys of the dictionary are the names of the log targets.
	 * You can use the name to access a specific log target. For example,
	 *
	 * ~~~
	 * $target = $router->targets['file'];
	 * ~~~
	 * @return \yii\base\Dictionary the targets managed by this log router.
	 */
	public function getTargets()
	{
		return $this->_targets;
	}

	/**
	 * Sets the log targets.
	 * @param array $config list of log target configurations. Each array element
	 * represents the configuration for creating a single log target. It will be
	 * passed to [[\Yii::createObject]] to create the target instance.
	 */
	public function setTargets($config)
	{
		foreach ($config as $name => $target) {
			if ($target instanceof Target) {
				$this->_targets[$name] = $target;
			}
			else {
				$this->_targets[$name] = \Yii::createObject($target);
			}
		}
	}

	/**
	 * Retrieves and processes log messages from the system logger.
	 * This method mainly serves the event handler to [[Logger::onFlush]]
	 * and [[\yii\base\Application::onEndRequest]] events.
	 * It will retrieve the available log messages from the [[\Yii::getLogger|system logger]]
	 * and invoke the registered [[targets|log targets]] to do the actual processing.
	 * @param \yii\base\Event $event event parameter
	 */
	public function processMessages($event)
	{
		$messages = \Yii::getLogger()->messages;
		$export = !isset($event->params['export']) || $event->params['export'];
		$final = !isset($event->params['flush']) || !$event->params['flush'];
		foreach ($this->_targets as $target) {
			if ($target->enabled) {
				$target->processMessages($messages, $export, $final);
			}
		}
	}
}

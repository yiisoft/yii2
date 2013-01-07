<?php
/**
 * Router class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
 * \Yii::$application->log->targets['file']->enabled = false;
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Router extends \yii\base\ApplicationComponent
{
	/**
	 * @var Target[] list of log target objects or configurations. If the latter, target objects will
	 * be created in [[init()]] by calling [[\Yii::createObject()]] with the corresponding object configuration.
	 */
	public $targets = array();

	/**
	 * Initializes this application component.
	 * This method is invoked when the Router component is created by the application.
	 * The method attaches the [[processLogs]] method to both the [[Logger::flush]] event
	 * and the [[\yii\base\Application::afterRequest]] event.
	 */
	public function init()
	{
		parent::init();

		foreach ($this->targets as $name => $target) {
			if (!$target instanceof Target) {
				$this->targets[$name] = \Yii::createObject($target);
			}
		}

		\Yii::getLogger()->on('flush', array($this, 'processMessages'));
		if (\Yii::$application !== null) {
			\Yii::$application->on('afterRequest', array($this, 'processMessages'));
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
		$final = $event->name !== 'flush';
		foreach ($this->targets as $target) {
			if ($target->enabled) {
				$target->processMessages($messages, $final);
			}
		}
	}
}

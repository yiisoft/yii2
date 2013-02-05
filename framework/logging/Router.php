<?php
/**
 * Router class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\logging;

use Yii;
use yii\base\Component;
use yii\base\Application;

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
 * Yii::$app->log->targets['file']->enabled = false;
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Router extends Component
{
	/**
	 * @var Target[] list of log target objects or configurations. If the latter, target objects will
	 * be created in [[init()]] by calling [[Yii::createObject()]] with the corresponding object configuration.
	 */
	public $targets = array();

	/**
	 * Initializes this application component.
	 * This method is invoked when the Router component is created by the application.
	 * The method attaches the [[processLogs]] method to both the [[Logger::EVENT_FLUSH]] event
	 * and the [[Logger::EVENT_FINAL_FLUSH]] event.
	 */
	public function init()
	{
		parent::init();

		foreach ($this->targets as $name => $target) {
			if (!$target instanceof Target) {
				$this->targets[$name] = Yii::createObject($target);
			}
		}
		Yii::getLogger()->router = $this;
	}

	/**
	 * Dispatches log messages to [[targets]].
	 * This method is called by [[Logger]] when its [[Logger::flush()]] method is called.
	 * It will forward the messages to each log target registered in [[targets]].
	 * @param array $messages the messages to be processed
	 * @param boolean $final whether this is the final call during a request cycle
	 */
	public function dispatch($messages, $final = false)
	{
		foreach ($this->targets as $target) {
			if ($target->enabled) {
				$target->collect($messages, $final);
			}
		}
	}
}

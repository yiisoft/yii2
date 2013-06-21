<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class MutexFilter extends Behavior
{
	/**
	 * @var array atomic actions configuration. Keys are action names and values are arrays.
	 * Each array element may contain elements with the following keys: 'expire', 'lockName', 'errorMessage'.
	 */
	public $actions = array();
	/**
	 * @var Mutex|string the mutex object or the application component ID of the mutex.
	 */
	public $mutex = 'mutex';

	public function init()
	{
		parent::init();
		if (is_string($this->mutex)) {
			$this->mutex = Yii::$app->getComponent($this->mutex);
		}
		if (!$this->mutex instanceof Mutex) {
			throw new InvalidConfigException("MutexFilter::mutex must be either a Mutex instance or the application component ID of a Mutex.");
		}
	}

	public function events()
	{
		return array(
			Controller::EVENT_BEFORE_ACTION => 'beforeAction',
			Controller::EVENT_AFTER_ACTION => 'afterAction',
		);
	}

	/**
	 * @param ActionEvent $event
	 * @return boolean
	 */
	public function beforeAction($event)
	{
		$id = $event->action->id;
		if (isset($this->actions[$id])) {
			$event->isValid = $this->mutex->acquireLock(
				@$this->actions[$id]['lockName'] ?: $event->action->getUniqueId(),
				@$this->actions[$id]['expire'] ?: 0
			);
			if (!$event->isValid && isset($this->actions[$id]['errorMessage'])) {
				echo $this->actions[$id]['errorMessage'];
			}
		}
		return $event->isValid;
	}

	/**
	 * @param ActionEvent $event
	 */
	public function afterAction($event)
	{
		if (isset($this->actions[$event->action->id])) {
			$this->mutex->releaseLock();
		}
	}
}

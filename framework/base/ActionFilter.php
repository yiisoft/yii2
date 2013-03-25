<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Filter extends Behavior
{
	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return array(
			'beforeAction' => 'beforeAction',
			'afterAction' => 'afterAction',
		);
	}

	/**
	 * @param ActionEvent $event
	 * @return boolean
	 */
	public function beforeAction($event)
	{
		return $event->isValid;
	}

	/**
	 * @param ActionEvent $event
	 * @return boolean
	 */
	public function afterAction($event)
	{

	}
}
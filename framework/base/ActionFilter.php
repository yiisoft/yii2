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
class ActionFilter extends Behavior
{
	/**
	 * @var array list of action IDs that this filter should apply to. If this property is not set,
	 * then the filter applies to all actions, unless they are listed in [[except]].
	 */
	public $only;
	/**
	 * @var array list of action IDs that this filter should not apply to.
	 */
	public $except = array();

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
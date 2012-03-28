<?php
/**
 * ModelBehavior class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelBehavior class.
 *
 * ModelBehavior is a base class for behaviors that are attached to a model object.
 * The model should be an instance of [[Model]] or its child classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelBehavior extends Behavior
{
	/**
	 * Declares event handlers for owner's events.
	 * The default implementation returns the following event handlers:
	 *
	 * - `beforeValidate` event
	 * - `afterValidate` event
	 *
	 * You may override these event handler methods to respond to the corresponding owner events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return array(
			'beforeValidate' => 'beforeValidate',
			'afterValidate' => 'afterValidate',
		);
	}

	/**
	 * Responds to [[Model::onBeforeValidate]] event.
	 * Override this method if you want to handle the corresponding event of the [[owner]].
	 * You may set the [[ModelEvent::isValid|isValid]] property of the event parameter
	 * to be false to cancel the validation process.
	 * @param ModelEvent $event event parameter
	 */
	public function beforeValidate($event)
	{
	}

	/**
	 * Responds to [[Model::onAfterValidate]] event.
	 * Override this method if you want to handle the corresponding event of the [[owner]].
	 * @param Event $event event parameter
	 */
	public function afterValidate($event)
	{
	}
}

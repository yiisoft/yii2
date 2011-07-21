<?php
/**
 * CModelBehavior class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CModelBehavior is a base class for behaviors that are attached to a model component.
 * The model should extend from {@link CModel} or its child classes.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CModelBehavior.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.base
 * @since 1.0.2
 */
class CModelBehavior extends CBehavior
{
	/**
	 * Declares events and the corresponding event handler methods.
	 * The default implementation returns 'onBeforeValidate' and 'onAfterValidate' events and handlers.
	 * If you override this method, make sure you merge the parent result to the return value.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return array(
			'onAfterConstruct'=>'afterConstruct',
			'onBeforeValidate'=>'beforeValidate',
			'onAfterValidate'=>'afterValidate',
		);
	}

	/**
	 * Responds to {@link CModel::onAfterConstruct} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 */
	public function afterConstruct($event)
	{
	}

	/**
	 * Responds to {@link CModel::onBeforeValidate} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the validation process.
	 * @param CModelEvent $event event parameter
	 */
	public function beforeValidate($event)
	{
	}

	/**
	 * Responds to {@link CModel::onAfterValidate} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link owner}.
	 * @param CEvent $event event parameter
	 */
	public function afterValidate($event)
	{
	}
}

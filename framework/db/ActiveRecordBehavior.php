<?php
/**
 * ActiveRecordBehavior class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\ModelBehavior;

/**
 * ActiveRecordBehavior is the base class for behaviors that can be attached to [[ActiveRecord]].
 *
 * Compared to [[\yii\base\ModelBehavior]], ActiveRecordBehavior responds to more events
 * that are specific to [[ActiveRecord]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveRecordBehavior extends ModelBehavior
{
	/**
	 * Declares events and the corresponding event handler methods.
	 * If you override this method, make sure you merge the parent result to the return value.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see \yii\base\Behavior::events()
	 */
	public function events()
	{
		return array_merge(parent::events(), array(
			'beforeInsert' => 'beforeInsert',
			'afterInsert' => 'afterInsert',
			'beforeUpdate' => 'beforeUpdate',
			'afterUpdate' => 'afterUpdate',
			'beforeDelete' => 'beforeDelete',
			'afterDelete' => 'afterDelete',
		));
	}

	/**
	 * Responds to the owner's `beforeInsert` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * You may set the [[ModelEvent::isValid|isValid]] property of the event parameter
	 * to be false to quit the ActiveRecord inserting process.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function beforeInsert($event)
	{
	}

	/**
	 * Responds to the owner's `afterInsert` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function afterInsert($event)
	{
	}

	/**
	 * Responds to the owner's `beforeUpdate` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * You may set the [[ModelEvent::isValid|isValid]] property of the event parameter
	 * to be false to quit the ActiveRecord updating process.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function beforeUpdate($event)
	{
	}

	/**
	 * Responds to the owner's `afterUpdate` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function afterUpdate($event)
	{
	}

	/**
	 * Responds to the owner's `beforeDelete` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * You may set the [[ModelEvent::isValid|isValid]] property of the event parameter
	 * to be false to quit the ActiveRecord deleting process.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function beforeDelete($event)
	{
	}

	/**
	 * Responds to the owner's `afterDelete` event.
	 * Overrides this method if you want to handle the corresponding event of the owner.
	 * @param \yii\base\ModelEvent $event event parameter
	 */
	public function afterDelete($event)
	{
	}
}

<?php
/**
 * CActiveRecordBehavior class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveRecordBehavior is the base class for behaviors that can be attached to {@link CActiveRecord}.
 * Compared with {@link CModelBehavior}, CActiveRecordBehavior attaches to more events
 * that are only defined by {@link CActiveRecord}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CActiveRecordBehavior.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.db.ar
 * @since 1.0.2
 */
class CActiveRecordBehavior extends CModelBehavior
{
	/**
	 * Declares events and the corresponding event handler methods.
	 * If you override this method, make sure you merge the parent result to the return value.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return array_merge(parent::events(), array(
			'onBeforeSave' => 'beforeSave',
			'onAfterSave' => 'afterSave',
			'onBeforeDelete' => 'beforeDelete',
			'onAfterDelete' => 'afterDelete',
			'onBeforeFind' => 'beforeFind',
			'onAfterFind' => 'afterFind',
		));
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeSave} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the saving process.
	 * @param CModelEvent $event event parameter
	 */
	public function beforeSave($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterSave} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CModelEvent $event event parameter
	 */
	public function afterSave($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeDelete} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * You may set {@link CModelEvent::isValid} to be false to quit the deletion process.
	 * @param CEvent $event event parameter
	 */
	public function beforeDelete($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterDelete} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 */
	public function afterDelete($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onBeforeFind} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 * @since 1.0.9
	 */
	public function beforeFind($event)
	{
	}

	/**
	 * Responds to {@link CActiveRecord::onAfterFind} event.
	 * Overrides this method if you want to handle the corresponding event of the {@link CBehavior::owner owner}.
	 * @param CEvent $event event parameter
	 */
	public function afterFind($event)
	{
	}
}

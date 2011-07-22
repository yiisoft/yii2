<?php
/**
 * CModelEvent class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelEvent class.
 *
 * ModelEvent represents the event parameters needed by events raised by a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelEvent extends Event
{
	/**
	 * @var CDbCrireria the query criteria that is passed as a parameter to a find method of {@link CActiveRecord}.
	 * Note that this property is only used by {@link CActiveRecord::onBeforeFind} event.
	 * This property could be null.
	 * @since 1.1.5
	 */
	public $criteria;
}

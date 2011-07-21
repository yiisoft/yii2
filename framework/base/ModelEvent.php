<?php
/**
 * CModelEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelEvent class.
 *
 * CModelEvent represents the event parameters needed by events raised by a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CModelEvent.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CModelEvent extends CEvent
{
	/**
	 * @var boolean whether the model is in valid status and should continue its normal method execution cycles. Defaults to true.
	 * For example, when this event is raised in a {@link CFormModel} object that is executing {@link CModel::beforeValidate},
	 * if this property is set false by the event handler, the {@link CModel::validate} method will quit after handling this event.
	 * If true, the normal execution cycles will continue, including performing the real validations and calling
	 * {@link CModel::afterValidate}.
	 */
	public $isValid=true;
	/**
	 * @var CDbCrireria the query criteria that is passed as a parameter to a find method of {@link CActiveRecord}.
	 * Note that this property is only used by {@link CActiveRecord::onBeforeFind} event.
	 * This property could be null.
	 * @since 1.1.5
	 */
	public $criteria;
}

<?php
/**
 * ValidationEvent class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ValidationEvent class.
 *
 * ValidationEvent represents the parameter needed by model validation events.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ValidationEvent extends Event
{
	/**
	 * @var boolean whether the model passes the validation by the event handler.
	 * Defaults to true. If it is set false, the [[Model::validate|model validation]] will be cancelled.
	 * @see Model::onBeforeValidate
	 */
	public $isValid = true;
}

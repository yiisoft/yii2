<?php
/**
 * CUnsafeValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * CUnsafeValidator marks the associated attributes to be unsafe so that they cannot be massively assigned.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CUnsafeValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CUnsafeValidator extends Validator
{
	/**
	 * @var boolean whether attributes listed with this validator should be considered safe for massive assignment.
	 * Defaults to false.
	 * @since 1.1.4
	 */
	public $safe = false;
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
	}
}


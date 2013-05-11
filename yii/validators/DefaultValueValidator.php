<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

/**
 * DefaultValueValidator sets the attribute to be the specified default value.
 *
 * DefaultValueValidator is not really a validator. It is provided mainly to allow
 * specifying attribute default values when they are empty.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultValueValidator extends Validator
{
	/**
	 * @var mixed the default value to be set to the specified attributes.
	 */
	public $value;
	/**
	 * @var boolean this property is overwritten to be false so that this validator will
	 * be applied when the value being validated is empty.
	 */
	public $skipOnEmpty = false;

	/**
	 * Validates the attribute of the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if ($this->isEmpty($object->$attribute)) {
			$object->$attribute = $this->value;
		}
	}
}


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
 * By default, when the attribute being validated is [[isEmpty|empty]], the validator
 * will assign a default [[value]] to it. However, if [[setOnEmpty]] is false, the validator
 * will always assign the default [[value]] to the attribute, no matter it is empty or not.
 *
 * DefaultValueValidator is not really a validator. It is provided mainly to allow
 * specifying attribute default values in a dynamic way.
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
	 * @var boolean whether to set the default [[value]] only when the attribute is [[isEmpty|empty]].
	 * Defaults to true. If false, the attribute will always be assigned with the default [[value]],
	 * no matter it is empty or not.
	 */
	public $setOnEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * @param \yii\base\Model $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		if (!$this->setOnEmpty || $this->isEmpty($object->$attribute)) {
			$object->$attribute = $this->value;
		}
	}
}


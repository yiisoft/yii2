<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * ExistValidator validates that the attribute value exists in a table.
 *
 * This validator is often used to verify that a foreign key contains a value
 * that can be found in the foreign table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExistValidator extends Validator
{
	/**
	 * @var string the ActiveRecord class name or alias of the class
	 * that should be used to look for the attribute value being validated.
	 * Defaults to null, meaning using the ActiveRecord class of
	 * the attribute being validated.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var string the yii\db\ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated.
	 * @see className
	 */
	public $attributeName;


	/**
	 * Initializes the validator.
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is invalid.');
		}
	}

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 *
	 * @param \yii\db\ActiveRecord $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if (is_array($value)) {
			$this->addError($object, $attribute, $this->message);
			return;
		}

		/** @var $className \yii\db\ActiveRecord */
		$className = $this->className === null ? get_class($object) : Yii::import($this->className);
		$attributeName = $this->attributeName === null ? $attribute : $this->attributeName;
		$query = $className::find();
		$query->where(array($attributeName => $value));
		if (!$query->exists()) {
			$this->addError($object, $attribute, $this->message);
		}
	}

	/**
	 * Validates the given value.
	 * @param mixed $value the value to be validated.
	 * @return boolean whether the value is valid.
	 * @throws InvalidConfigException if either [[className]] or [[attributeName]] is not set.
	 */
	public function validateValue($value)
	{
		if (is_array($value)) {
			return false;
		}
		if ($this->className === null) {
			throw new InvalidConfigException('The "className" property must be set.');
		}
		if ($this->attributeName === null) {
			throw new InvalidConfigException('The "attributeName" property must be set.');
		}
		/** @var $className \yii\db\ActiveRecord */
		$className = $this->className;
		$query = $className::find();
		$query->where(array($this->attributeName => $value));
		return $query->exists();
	}
}


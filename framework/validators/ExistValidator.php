<?php
/**
 * ExistValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;
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
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 *
	 * @param \yii\db\ActiveRecord $object the object being validated
	 * @param string $attribute the attribute being validated
	 * @throws InvalidConfigException if table doesn't have column specified
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}

		/** @var $className \yii\db\ActiveRecord */
		$className = ($this->className === null) ? get_class($object) : \Yii::import($this->className);
		$attributeName = ($this->attributeName === null) ? $attribute : $this->attributeName;
		$table = $className::getTableSchema();
		if (($column = $table->getColumn($attributeName)) === null) {
			throw new InvalidConfigException('Table "' . $table->name . '" does not have a column named "' . $attributeName . '"');
		}

		$query = $className::find();
		$query->where(array($column->name => $value));
		if (!$query->exists()) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii', '{attribute} "{value}" is invalid.');
			$this->addError($object, $attribute, $message);
		}
	}
}


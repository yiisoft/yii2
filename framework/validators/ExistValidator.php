<?php
/**
 * ExistValidator class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

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
	 * @var string the yii\db\ar\ActiveRecord class name or alias of the class
	 * that should be used to look for the attribute value being validated.
	 * Defaults to null, meaning using the yii\db\ar\ActiveRecord class of
	 * the attribute being validated.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var string the yii\db\ar\ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated.
	 * @see className
	 */
	public $attributeName;
	/**
	 * @var \yii\db\dao\BaseQuery additional query criteria. This will be combined
	 * with the condition that checks if the attribute value exists in the
	 * corresponding table column.
	 */
	public $query = null;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 *
	 * @param \yii\db\ar\ActiveRecord $object the object being validated
	 * @param string $attribute the attribute being validated
	 *
	 * @throws \yii\base\Exception if table doesn't have column specified
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if ($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}

		$className = ($this->className === null) ? get_class($object) : \Yii::import($this->className);
		$attributeName = ($this->attributeName === null) ? $attribute : $this->attributeName;
		$table = $object::getMetaData()->table;
		if (($column = $table->getColumn($attributeName)) === null) {
			throw new \yii\base\Exception('Table "' . $table->name . '" does not have a column named "' . $attributeName . '"');
		}

		$finder = $object->find()->where(array($column->name => $value));

		if ($this->query instanceof \yii\db\dao\BaseQuery) {
			$finder->mergeWith($this->query);
		}

		if (!$finder->exists()) {
			$message = ($this->message !== null) ? $this->message : \Yii::t('yii', '{attribute} "{value}" is invalid.');
			$this->addError($object, $attribute, $message, array('{value}' => $value));
		}
	}
}


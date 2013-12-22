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
	 * @var string|array the ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated. Use a string
	 * to specify the attribute that is different from the attribute being validated
	 * (often used together with [[className]]). Use an array to validate the existence about
	 * multiple columns. For example,
	 *
	 * ```php
	 * // a1 needs to exist
	 * array('a1', 'exist')
	 * // a1 needs to exist, but its value will use a2 to check for the existence
	 * array('a1', 'exist', 'attributeName' => 'a2')
	 * // a1 and a2 need to exist together, and they both will receive error message
	 * array('a1, a2', 'exist', 'attributeName' => array('a1', 'a2'))
	 * // a1 and a2 need to exist together, only a1 will receive error message
	 * array('a1', 'exist', 'attributeName' => array('a1', 'a2'))
	 * // a1 and a2 need to exist together, a2 will take value 10, only a1 will receive error message
	 * array('a1', 'exist', 'attributeName' => array('a1', 'a2' => 10))
	 * ```
	 */
	public $attributeName;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if ($this->message === null) {
			$this->message = Yii::t('yii', '{attribute} is invalid.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if (is_array($value)) {
			$this->addError($object, $attribute, $this->message);
			return;
		}

		/** @var \yii\db\ActiveRecordInterface $className */
		$className = $this->className === null ? get_class($object) : $this->className;
		$attributeName = $this->attributeName === null ? $attribute : $this->attributeName;
		if (!$this->exists($className, $attributeName, $object, $value)) {
			$this->addError($object, $attribute, $this->message);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function validateValue($value)
	{
		if (is_array($value)) {
			return [$this->message, []];
		}
		if ($this->className === null) {
			throw new InvalidConfigException('The "className" property must be set.');
		}
		if ($this->attributeName === null) {
			throw new InvalidConfigException('The "attributeName" property must be set.');
		}
		return $this->exists($this->className, $this->attributeName, null, $value) ? null : [$this->message, []];
	}

	/**
	 * Performs existence check.
	 * @param string $className the AR class name to be checked against
	 * @param string|array $attributeName the attribute(s) to be checked
	 * @param \yii\db\ActiveRecordInterface $object the object whose value is being validated
	 * @param mixed $value the attribute value currently being validated
	 * @return boolean whether the data being validated exists in the database already
	 */
	protected function exists($className, $attributeName, $object, $value)
	{
		/** @var \yii\db\ActiveRecordInterface $className */
		$query = $className::find();
		if (is_array($attributeName)) {
			$params = [];
			foreach ($attributeName as $k => $v) {
				if (is_integer($k)) {
					$params[$v] = $this->className === null && $object !== null ? $object->$v : $value;
				} else {
					$params[$k] = $v;
				}
			}
		} else {
			$params = [$attributeName => $value];
		}
		return $query->where($params)->exists();
	}
}

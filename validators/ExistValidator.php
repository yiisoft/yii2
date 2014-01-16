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
 * ExistValidator checks if the value being validated can be found in the table column specified by
 * the ActiveRecord class [[targetClass]] and the attribute [[targetAttribute]].
 *
 * This validator is often used to verify that a foreign key contains a value
 * that can be found in the foreign table.
 *
 * The followings are examples of validation rules using this validator:
 *
 * ```php
 * // a1 needs to exist
 * ['a1', 'exist']
 * // a1 needs to exist, but its value will use a2 to check for the existence
 * ['a1', 'exist', 'targetAttribute' => 'a2']
 * // a1 and a2 need to exist together, and they both will receive error message
 * [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']]
 * // a1 and a2 need to exist together, only a1 will receive error message
 * ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']]
 * // a1 needs to exist by checking the existence of both a2 and a3 (using a1 value)
 * ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExistValidator extends Validator
{
	/**
	 * @var string the name of the ActiveRecord class that should be used to validate the existence
	 * of the current attribute value. It not set, it will use the ActiveRecord class of the attribute being validated.
	 * @see targetAttribute
	 */
	public $targetClass;
	/**
	 * @var string|array the name of the ActiveRecord attribute that should be used to
	 * validate the existence of the current attribute value. If not set, it will use the name
	 * of the attribute currently being validated. You may use an array to validate the existence
	 * of multiple columns at the same time. The array values are the attributes that will be
	 * used to validate the existence, while the array keys are the attributes whose values are to be validated.
	 * If the key and the value are the same, you can just specify the value.
	 */
	public $targetAttribute;
	/**
	 * @var string|array|\Closure additional filter to be applied to the DB query used to check the existence of the attribute value.
	 * This can be a string or an array representing the additional query condition (refer to [[\yii\db\Query::where()]]
	 * on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
	 * is the [[\yii\db\Query|Query]] object that you can modify in the function.
	 */
	public $filter;


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
		$targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;

		if (is_array($targetAttribute)) {
			$params = [];
			foreach ($targetAttribute as $k => $v) {
				$params[$v] = is_integer($k) ? $object->$v : $object->$k;
			}
		} else {
			$params = [$targetAttribute => $object->$attribute];
		}

		foreach ($params as $value) {
			if (is_array($value)) {
				$this->addError($object, $attribute, Yii::t('yii', '{attribute} is invalid.'));
				return;
			}
		}

		$targetClass = $this->targetClass === null ? get_class($object) : $this->targetClass;
		$query = $this->createQuery($targetClass, $params);

		if (!$query->exists()) {
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
		if ($this->targetClass === null) {
			throw new InvalidConfigException('The "targetClass" property must be set.');
		}
		if (!is_string($this->targetAttribute)) {
			throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
		}

		$query = $this->createQuery($this->targetClass, [$this->targetAttribute => $value]);

		return $query->exists() ? null : [$this->message, []];
	}

	/**
	 * Creates a query instance with the given condition.
	 * @param string $targetClass the target AR class
	 * @param mixed $condition query condition
	 * @return \yii\db\ActiveQueryInterface the query instance
	 */
	protected function createQuery($targetClass, $condition)
	{
		/** @var \yii\db\ActiveRecordInterface $targetClass */
		$query = $targetClass::find()->where($condition);
		if ($this->filter instanceof \Closure) {
			call_user_func($this->filter, $query);
		} elseif ($this->filter !== null) {
			$query->andWhere($this->filter);
		}
		return $query;
	}
}

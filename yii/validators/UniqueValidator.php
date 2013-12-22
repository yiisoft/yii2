<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\db\ActiveRecordInterface;

/**
 * UniqueValidator validates that the attribute value is unique in the corresponding database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UniqueValidator extends Validator
{
	/**
	 * @var string the ActiveRecord class name or alias of the class
	 * that should be used to look for the attribute value being validated.
	 * Defaults to null, meaning using the ActiveRecord class of the attribute being validated.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var string|array the ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated. Use a string
	 * to specify the attribute that is different from the attribute being validated
	 * (often used together with [[className]]). Use an array to validate uniqueness about
	 * multiple columns. For example,
	 *
	 * ```php
	 * // a1 needs to be unique
	 * array('a1', 'unique')
	 * // a1 needs to be unique, but its value will use a2 to check for the uniqueness
	 * array('a1', 'unique', 'attributeName' => 'a2')
	 * // a1 and a2 need to unique together, and they both will receive error message
	 * array('a1, a2', 'unique', 'attributeName' => array('a1', 'a2'))
	 * // a1 and a2 need to unique together, only a1 will receive error message
	 * array('a1', 'unique', 'attributeName' => array('a1', 'a2'))
	 * // a1 and a2 need to unique together, a2 will take value 10, only a1 will receive error message
	 * array('a1', 'unique', 'attributeName' => array('a1', 'a2' => 10))
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
			$this->message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;

		if (is_array($value)) {
			$this->addError($object, $attribute, Yii::t('yii', '{attribute} is invalid.'));
			return;
		}

		/** @var ActiveRecordInterface $className */
		$className = $this->className === null ? get_class($object) : $this->className;
		$attributeName = $this->attributeName === null ? $attribute : $this->attributeName;

		$query = $className::find();

		if (is_array($attributeName)) {
			$params = [];
			foreach ($attributeName as $k => $v) {
				if (is_integer($k)) {
					$params[$v] = $this->className === null ? $object->$v : $value;
				} else {
					$params[$k] = $v;
				}
			}
		} else {
			$params = [$attributeName => $value];
		}
		$query->where($params);

		if (!$object instanceof ActiveRecordInterface || $object->getIsNewRecord()) {
			// if current $object isn't in the database yet then it's OK just to call exists()
			$exists = $query->exists();
		} else {
			// if current $object is in the database already we can't use exists()
			/** @var ActiveRecordInterface[] $objects */
			$objects = $query->limit(2)->all();
			$n = count($objects);
			if ($n === 1) {
				$keys = array_keys($params);
				$pks = $className::primaryKey();
				sort($keys);
				sort($pks);
				if ($keys === $pks) {
					// primary key is modified and not unique
					$exists = $object->getOldPrimaryKey() != $object->getPrimaryKey();
				} else {
					// non-primary key, need to exclude the current record based on PK
					$exists = $objects[0]->getPrimaryKey() != $object->getOldPrimaryKey();
				}
			} else {
				$exists = $n > 1;
			}
		}

		if ($exists) {
			$this->addError($object, $attribute, $this->message);
		}
	}
}

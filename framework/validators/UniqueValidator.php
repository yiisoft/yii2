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
 * UniqueValidator validates that the attribute value is unique in the specified database table.
 *
 * UniqueValidator checks if the value being validated is unique in the table column specified by
 * the ActiveRecord class [[targetClass]] and the attribute [[targetAttribute]].
 *
 * The followings are examples of validation rules using this validator:
 *
 * ```php
 * // a1 needs to be unique
 * ['a1', 'unique']
 * // a1 needs to be unique, but column a2 will be used to check the uniqueness of the a1 value
 * ['a1', 'unique', 'targetAttribute' => 'a2']
 * // a1 and a2 need to be unique together, and they both will receive error message
 * [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']]
 * // a1 and a2 need to be unique together, only a1 will receive error message
 * ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']]
 * // a1 needs to be unique by checking the uniqueness of both a2 and a3 (using a1 value)
 * ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UniqueValidator extends Validator
{
    /**
     * @var string the name of the ActiveRecord class that should be used to validate the uniqueness
     * of the current attribute value. If not set, it will use the ActiveRecord class of the attribute being validated.
     * @see targetAttribute
     */
    public $targetClass;
    /**
     * @var string|array the name of the ActiveRecord attribute that should be used to
     * validate the uniqueness of the current attribute value. If not set, it will use the name
     * of the attribute currently being validated. You may use an array to validate the uniqueness
     * of multiple columns at the same time. The array values are the attributes that will be
     * used to validate the uniqueness, while the array keys are the attributes whose values are to be validated.
     * If the key and the value are the same, you can just specify the value.
     */
    public $targetAttribute;
    /**
     * @var string|array|\Closure additional filter to be applied to the DB query used to check the uniqueness of the attribute value.
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
            $this->message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        /* @var $targetClass ActiveRecordInterface */
        $targetClass = $this->targetClass === null ? get_class($object) : $this->targetClass;
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

        $query = $targetClass::find();
        $query->where($params);

        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        if (!$object instanceof ActiveRecordInterface || $object->getIsNewRecord()) {
            // if current $object isn't in the database yet then it's OK just to call exists()
            $exists = $query->exists();
        } else {
            // if current $object is in the database already we can't use exists()
            /* @var $objects ActiveRecordInterface[] */
            $objects = $query->limit(2)->all();
            $n = count($objects);
            if ($n === 1) {
                $keys = array_keys($params);
                $pks = $targetClass::primaryKey();
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

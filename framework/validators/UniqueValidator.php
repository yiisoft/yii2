<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;

/**
 * UniqueValidator validates that the attribute value is unique in the specified database table.
 *
 * UniqueValidator checks if the value being validated is unique in the table column specified by
 * the ActiveRecord class [[targetClass]] and the attribute [[targetAttribute]].
 *
 * The following are examples of validation rules using this validator:
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
     * @var string the user-defined error message. When validating single attribute, it may contain
     * the following placeholders which will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * When validating mutliple attributes, it may contain the following placeholders:
     *
     * - `{attributes}`: the labels of the attributes being validated.
     * - `{values}`: the values of the attributes being validated.
     *
     */
    public $message;
    /**
     * @var string
     * @since 2.0.9
     * @deprecated Deprecated since version 2.0.10, to be removed in 2.1. Use [[message]] property
     * to setup custom message for multiple target attributes.
     */
    public $comboNotUnique;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message !== null) {
            return;
        }
        if (is_array($this->targetAttribute) && count($this->targetAttribute) > 1) {
            // fallback for deprecated `comboNotUnique` property - use it as message if is set
            if ($this->comboNotUnique === null) {
                $this->message = Yii::t('yii', 'The combination {values} of {attributes} has already been taken.');
            } else {
                $this->message = $this->comboNotUnique;
            }
        } else {
            $this->message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /* @var $targetClass ActiveRecordInterface */
        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;

        if (is_array($targetAttribute)) {
            $params = [];
            foreach ($targetAttribute as $k => $v) {
                $params[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        } else {
            $params = [$targetAttribute => $model->$attribute];
        }

        foreach ($params as $value) {
            if (is_array($value)) {
                $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));

                return;
            }
        }

        $query = $targetClass::find();
        $query->andWhere($params);

        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        if (!$model instanceof ActiveRecordInterface || $model->getIsNewRecord() || $model->className() !== $targetClass::className()) {
            // if current $model isn't in the database yet then it's OK just to call exists()
            // also there's no need to run check based on primary keys, when $targetClass is not the same as $model's class
            $exists = $query->exists();
        } else {
            // if current $model is in the database already we can't use exists()
            /* @var $models ActiveRecordInterface[] */
            $models = $query->limit(2)->all();
            $n = count($models);
            if ($n === 1) {
                $keys = array_keys($params);
                $pks = $targetClass::primaryKey();
                sort($keys);
                sort($pks);
                if ($keys === $pks) {
                    // primary key is modified and not unique
                    $exists = $model->getOldPrimaryKey() != $model->getPrimaryKey();
                } else {
                    // non-primary key, need to exclude the current record based on PK
                    $exists = reset($models)->getPrimaryKey() != $model->getOldPrimaryKey();
                }
            } else {
                $exists = $n > 1;
            }
        }

        if ($exists) {
            if (count($targetAttribute) > 1) {
                $this->addComboNotUniqueError($model, $attribute);
            } else {
                $this->addError($model, $attribute, $this->message);
            }
        }
    }

    /**
     * Builds and adds [[comboNotUnique]] error message to the specified model attribute.
     * @param \yii\base\Model $model the data model.
     * @param string $attribute the name of the attribute.
     */
    private function addComboNotUniqueError($model, $attribute)
    {
        $attributeCombo = [];
        $valueCombo = [];
        foreach ($this->targetAttribute as $key => $value) {
            if(is_int($key)) {
                $attributeCombo[] = $model->getAttributeLabel($value);
                $valueCombo[] = '"' . $model->$value . '"';
            } else {
                $attributeCombo[] = $model->getAttributeLabel($key);
                $valueCombo[] = '"' . $model->$key . '"';
            }
        }
        $this->addError($model, $attribute, $this->message, [
            'attributes' => Inflector::sentence($attributeCombo),
            'values' => implode('-', $valueCombo)
        ]);
    }
}

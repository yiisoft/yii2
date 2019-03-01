<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;

/**
 * UniqueValidator 校验指定的属性值在数据库表中是唯一的。
 *
 * UniqueValidator 检查指定的值是否在由 AR 类 [[targetClass]] 和 [[targetAttribute]]
 * 属性指定的数据库表列中唯一。
 *
 * 如下是使用这个校验器的校验规则示例：
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
     * @var string 用于校验当前属性值唯一性的 AR 类的名字。
     * 如果没有设置，它将会使用被校验的属性所在的 AR 类。
     * @see targetAttribute
     */
    public $targetClass;
    /**
     * @var string|array 用于校验当前属性值唯一性的 [[\yii\db\ActiveRecord|ActiveRecord]] 的属性名称。
     * 如果没有设置，它会使用当前被校验的属性名称。
     * 你可以使用一个数组来在同一时刻校验多个列。
     * 数组的值是被用于校验唯一性的属性，
     * 数组的键则是将要被校验其值的属性。
     */
    public $targetAttribute;
    /**
     * @var string|array|\Closure 用于应用在检查属性值唯一性的 DB 查询中的额外过滤器。
     * 这个过滤器可以是一个字符串或者一个数组，代表额外的查询条件。（格式参考 [[\yii\db\Query::where()]]）
     * 或者是一个匿名函数，签名为 `function ($query)`，
     * 其中 `$query` 是 [[\yii\db\Query|Query]] 对象，你可以在函数中修改。
     */
    public $filter;
    /**
     * @var string 用户自定义错误消息。
     *
     * 当校验单个属性时，它可以包含如下占位符，
     * 这些占位符将会根据具体的值做替换：
     *
     * - `{attribute}`: 被校验的属性标签
     * - `{value}`: 被校验的属性值
     *
     * 当校验多个属性时，它可以包含如下占位符：
     *
     * - `{attributes}`: 被校验的属性值标签列表
     * - `{values}`: 被校验的属性值列表
     */
    public $message;
    /**
     * @var string
     * @since 2.0.9
     * @deprecated since version 2.0.10, to be removed in 2.1. Use [[message]] property
     * to setup custom message for multiple target attributes.
     */
    public $comboNotUnique;
    /**
     * @var string and|or define how target attributes are related
     * @since 2.0.11
     */
    public $targetAttributeJunction = 'and';
    /**
     * @var bool whether this validator is forced to always use master DB
     * @since 2.0.14
     */
    public $forceMasterDb =  true;


    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        /* @var $targetClass ActiveRecordInterface */
        $targetClass = $this->getTargetClass($model);
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $rawConditions = $this->prepareConditions($targetAttribute, $model, $attribute);
        $conditions = [$this->targetAttributeJunction === 'or' ? 'or' : 'and'];

        foreach ($rawConditions as $key => $value) {
            if (is_array($value)) {
                $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));
                return;
            }
            $conditions[] = [$key => $value];
        }

        $db = $targetClass::getDb();

        $modelExists = false;

        if ($this->forceMasterDb && method_exists($db, 'useMaster')) {
            $db->useMaster(function () use ($targetClass, $conditions, $model, &$modelExists) {
                $modelExists = $this->modelExists($targetClass, $conditions, $model);
            });
        } else {
            $modelExists = $this->modelExists($targetClass, $conditions, $model);
        }

        if ($modelExists) {
            if (is_array($targetAttribute) && count($targetAttribute) > 1) {
                $this->addComboNotUniqueError($model, $attribute);
            } else {
                $this->addError($model, $attribute, $this->message);
            }
        }
    }

    /**
     * @param Model $model the data model to be validated
     * @return string Target class name
     */
    private function getTargetClass($model)
    {
        return $this->targetClass === null ? get_class($model) : $this->targetClass;
    }

    /**
     * 检测 $model 对应的表是否存在于数据库中。
     *
     * @param string $targetClass  AR 类，
     * 用于校验当前属性值唯一性。
     * @param array $conditions 兼容 [[\yii\db\Query::where()|Query::where()]] 键值对格式的条件数组
     * @param Model $model 待校验的数据模型
     *
     * @return bool 模型对应的表是否存在。
     */
    private function modelExists($targetClass, $conditions, $model)
    {
        /** @var ActiveRecordInterface|\yii\base\BaseObject $targetClass $query */
        $query = $this->prepareQuery($targetClass, $conditions);

        if (!$model instanceof ActiveRecordInterface || $model->getIsNewRecord() || $model->className() !== $targetClass::className()) {
            // if current $model isn't in the database yet then it's OK just to call exists()
            // also there's no need to run check based on primary keys, when $targetClass is not the same as $model's class
            $exists = $query->exists();
        } else {
            // if current $model is in the database already we can't use exists()
            if ($query instanceof \yii\db\ActiveQuery) {
                // only select primary key to optimize query
                $columnsCondition = array_flip($targetClass::primaryKey());
                $query->select(array_flip($this->applyTableAlias($query, $columnsCondition)));
                
                // any with relation can't be loaded because related fields are not selected
                $query->with = null;
            }
            $models = $query->limit(2)->asArray()->all();
            $n = count($models);
            if ($n === 1) {
                // if there is one record, check if it is the currently validated model
                $dbModel = reset($models);
                $pks = $targetClass::primaryKey();
                $pk = [];
                foreach ($pks as $pkAttribute) {
                    $pk[$pkAttribute] = $dbModel[$pkAttribute];
                }
                $exists = ($pk != $model->getOldPrimaryKey(true));
            } else {
                // if there is more than one record, the value is not unique
                $exists = $n > 1;
            }
        }

        return $exists;
    }

    /**
     * 通过应用方法参数 $conditions 定义的过滤条件和类属性 [[filter]]
     * 构建一个查询对象。
     *
     * @param ActiveRecordInterface $targetClass  AR 类名字，
     * 用于校验当前属性值唯一性。
     * @param array $conditions 兼容 [[\yii\db\Query::where()|Query::where()]] 键值对格式的条件数组
     * @param array $conditions
     * @return ActiveQueryInterface|ActiveQuery
     */
    private function prepareQuery($targetClass, $conditions)
    {
        $query = $targetClass::find();
        $query->andWhere($conditions);
        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }

    /**
     * 将 $targetAttribute 参数中描述的属性关系处理为条件表达式，
     * 兼容 [[\yii\db\Query::where()|Query::where()]] 键值对格式。
     *
     * @param string|array $targetAttribute  用于校验当前属性值唯一性的 [[\yii\db\ActiveRecord|ActiveRecord]] 类属性名。
     * 你可以用一个数组来同时校验多列。
     * 数组的值是用来校验唯一性的属性，
     * 数组的键则是需要被校验其值唯一性的属性。
     * 如果键和值是一样的，你可以只指定值。
     * @param Model $model 被校验的数据模型。
     * @param string $attribute $model 中被校验的属性名称。
     *
     * @return array conditions, compatible with [[\yii\db\Query::where()|Query::where()]] key-value format.
     */
    private function prepareConditions($targetAttribute, $model, $attribute)
    {
        if (is_array($targetAttribute)) {
            $conditions = [];
            foreach ($targetAttribute as $k => $v) {
                $conditions[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        } else {
            $conditions = [$targetAttribute => $model->$attribute];
        }

        $targetModelClass = $this->getTargetClass($model);
        if (!is_subclass_of($targetModelClass, 'yii\db\ActiveRecord')) {
            return $conditions;
        }

        /** @var ActiveRecord $targetModelClass */
        return $this->applyTableAlias($targetModelClass::find(), $conditions);
    }

    /**
     * 创建和添加 [[comboNotUnique]] 错误消息到指定属性模型。
     * @param \yii\base\Model $model the data model.
     * @param string $attribute the name of the attribute.
     */
    private function addComboNotUniqueError($model, $attribute)
    {
        $attributeCombo = [];
        $valueCombo = [];
        foreach ($this->targetAttribute as $key => $value) {
            if (is_int($key)) {
                $attributeCombo[] = $model->getAttributeLabel($value);
                $valueCombo[] = '"' . $model->$value . '"';
            } else {
                $attributeCombo[] = $model->getAttributeLabel($key);
                $valueCombo[] = '"' . $model->$key . '"';
            }
        }
        $this->addError($model, $attribute, $this->message, [
            'attributes' => Inflector::sentence($attributeCombo),
            'values' => implode('-', $valueCombo),
        ]);
    }

    /**
     * 返还包含别名的条件表达式。
     * @param ActiveQuery $query
     * @param array $conditions array of condition, keys to be modified
     * @param null|string $alias set empty string for no apply alias. Set null for apply primary table alias
     * @return array
     */
    private function applyTableAlias($query, $conditions, $alias = null)
    {
        if ($alias === null) {
            $alias = array_keys($query->getTablesUsedInFrom())[0];
        }
        $prefixedConditions = [];
        foreach ($conditions as $columnName => $columnValue) {
            if (strpos($columnName, '(') === false) {
                $columnName = preg_replace('/^' . preg_quote($alias) . '\.(.*)$/', '$1', $columnName);
                if (strpos($columnName, '[[') === 0) {
                    $prefixedColumn = "{$alias}.{$columnName}";
                } else {
                    $prefixedColumn = "{$alias}.[[{$columnName}]]";
                }
            } else {
                // there is an expression, can't prefix it reliably
                $prefixedColumn = $columnName;
            }

            $prefixedConditions[$prefixedColumn] = $columnValue;
        }

        return $prefixedConditions;
    }
}

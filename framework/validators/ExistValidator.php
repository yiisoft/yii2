<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\QueryInterface;

/**
 * ExistValidator 校验指定的属性值是否在数据表中存在。
 *
 * ExistValidator 检测待校验的值能否在某个数据库表的某列中被找到，
 * 这个数据库表列由 [[targetClass]] 对应的AR类 和 [[targetAttribute]] 对应的属性名所指定。
 * 从2.0.14起，你可以使用更方便的属性 [[targetRelation]] 来实现类似的目的（译注：见示例）。
 *
 * 这个校验器通常用于校验一个外键包含的某个值
 * 能否在外表中被找到。
 *
 * 以下是使用这个校验器的校验规则示例：
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
 * // type_id needs to exist in the column "id" in the table defined in ProductType class
 * ['type_id', 'exist', 'targetClass' => ProductType::class, 'targetAttribute' => ['type_id' => 'id']],
 * // the same as the previous, but using already defined relation "type"
 * ['type_id', 'exist', 'targetRelation' => 'type'],
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ExistValidator extends Validator
{
    /**
     * @var string 用于校验当前属性值是否存在的 AR 类的名字。
     * 如果没有设置，它默认使用被校验属性所在的 AR 类。
     * @see targetAttribute
     */
    public $targetClass;
    /**
     * @var string|array 用于校验当前属性值是否存在的 AR 属性的名字。
     * 如果没有设置，它默认使用被校验属性的名字。
     * 你可以用一个数组来同时校验多列。
     * 数组的键是校验的属性名字，
     * 数组的值是搜索的数据库字段。
     */
    public $targetAttribute;
    /**
     * @var string 用于校验当前属性值存在性的关系名称。
     * 这个参数会覆盖 $targetClass 和 $targetAttribute
     * @since 2.0.14
     */
    public $targetRelation;
    /**
     * @var string|array|\Closure 一个额外的过滤器作用于数据库查询，这个数据库查询用于校验属性值的存在性。
     * 它可以是一个字符串或者数组代表额外的查询条件（查询条件格式参考 [[\yii\db\Query::where()]]），
     * 或者一个匿名函数，声明形式为 `function ($query)`，
     * 其中 `$query` 是类 [[\yii\db\Query|Query]] 对象，你可以在函数中修改这个对象。
     */
    public $filter;
    /**
     * @var bool whether to allow array type attribute.
     */
    public $allowArray = false;
    /**
     * @var string and|or define how target attributes are related
     * @since 2.0.11
     */
    public $targetAttributeJunction = 'and';
    /**
     * @var bool whether this validator is forced to always use master DB
     * @since 2.0.14
     */
    public $forceMasterDb = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if (!empty($this->targetRelation)) {
            $this->checkTargetRelationExistence($model, $attribute);
        } else {
            $this->checkTargetAttributeExistence($model, $attribute);
        }
    }

    /**
     * 基于关系名称来校验属性值的存在性
     * @param \yii\db\ActiveRecord $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    private function checkTargetRelationExistence($model, $attribute)
    {
        $exists = false;
        /** @var ActiveQuery $relationQuery */
        $relationQuery = $model->{'get' . ucfirst($this->targetRelation)}();

        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $relationQuery);
        } elseif ($this->filter !== null) {
            $relationQuery->andWhere($this->filter);
        }

        if ($this->forceMasterDb && method_exists($model::getDb(), 'useMaster')) {
            $model::getDb()->useMaster(function() use ($relationQuery, &$exists) {
                $exists = $relationQuery->exists();
            });
        } else {
            $exists = $relationQuery->exists();
        }


        if (!$exists) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * 基于目标属性来校验当前属性值的存在性
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    private function checkTargetAttributeExistence($model, $attribute)
    {
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $params = $this->prepareConditions($targetAttribute, $model, $attribute);
        $conditions = [$this->targetAttributeJunction == 'or' ? 'or' : 'and'];

        if (!$this->allowArray) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $this->addError($model, $attribute, Yii::t('yii', '{attribute} is invalid.'));

                    return;
                }
                $conditions[] = [$key => $value];
            }
        } else {
            $conditions[] = $params;
        }

        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $query = $this->createQuery($targetClass, $conditions);

        if (!$this->valueExists($targetClass, $query, $model->$attribute)) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    /**
     * 根据 $targetAttribute 参数的描述将关系属性处理为 conditions 的方式，符合 [[\yii\db\Query::where()|Query::where()]] 要求
     * 的 k-v 格式。
     *
     * @param $targetAttribute array|string $attribute 用于校验当前属性值存在性的 AR 属性名。
     * 如果没有设置，它会使用当前被校验的属性。
     * 你可以使用数组来同时校验多列。
     * 数组的键是校验的属性名字，
     * 数组的值是搜索的数据库字段。
     * 如果键和值是一样的，你可以只指定值。
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated in the $model
     * @return array conditions, compatible with [[\yii\db\Query::where()|Query::where()]] key-value format.
     * @throws InvalidConfigException
     */
    private function prepareConditions($targetAttribute, $model, $attribute)
    {
        if (is_array($targetAttribute)) {
            if ($this->allowArray) {
                throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
            }
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
     * @param Model $model the data model to be validated
     * @return string Target class name
     */
    private function getTargetClass($model)
    {
        return $this->targetClass === null ? get_class($model) : $this->targetClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if ($this->targetClass === null) {
            throw new InvalidConfigException('The "targetClass" property must be set.');
        }
        if (!is_string($this->targetAttribute)) {
            throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
        }

        if (is_array($value) && !$this->allowArray) {
            return [$this->message, []];
        }

        $query = $this->createQuery($this->targetClass, [$this->targetAttribute => $value]);

        return $this->valueExists($this->targetClass, $query, $value) ? null : [$this->message, []];
    }

    /**
     * 检查值是否在指定表中存在
     *
     * @param string $targetClass
     * @param QueryInterface $query
     * @param mixed $value the value want to be checked
     * @return bool
     */
    private function valueExists($targetClass, $query, $value)
    {
        $db = $targetClass::getDb();
        $exists = false;

        if ($this->forceMasterDb && method_exists($db, 'useMaster')) {
            $db->useMaster(function ($db) use ($query, $value, &$exists) {
                $exists = $this->queryValueExists($query, $value);
            });
        } else {
            $exists = $this->queryValueExists($query, $value);
        }

        return $exists;
    }


    /**
     * 执行查询以检查值的存在性
     *
     * @param QueryInterface $query
     * @param mixed $value the value to be checked
     * @return bool
     */
    private function queryValueExists($query, $value)
    {
        if (is_array($value)) {
            return $query->count("DISTINCT [[$this->targetAttribute]]") == count($value) ;
        }
        return $query->exists();
    }

    /**
     * 使用指定的条件创建一个查询实例。
     * @param string $targetClass the target AR class
     * @param mixed $condition query condition
     * @return \yii\db\ActiveQueryInterface the query instance
     */
    protected function createQuery($targetClass, $condition)
    {
        /* @var $targetClass \yii\db\ActiveRecordInterface */
        $query = $targetClass::find()->andWhere($condition);
        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }

    /**
     * 返回包含别名的条件。
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
                $prefixedColumn = "{$alias}.[[" . preg_replace(
                    '/^' . preg_quote($alias) . '\.(.*)$/',
                    '$1',
                    $columnName) . ']]';
            } else {
                // there is an expression, can't prefix it reliably
                $prefixedColumn = $columnName;
            }

            $prefixedConditions[$prefixedColumn] = $columnValue;
        }

        return $prefixedConditions;
    }
}

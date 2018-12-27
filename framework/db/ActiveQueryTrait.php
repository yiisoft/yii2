<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveQueryTrait 实现了活动记录查询类的通用方法和属性。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
trait ActiveQueryTrait
{
    /**
     * @var string ActiveRecord 类的名称。
     */
    public $modelClass;
    /**
     * @var array 此查询应使用的关系列表。
     */
    public $with;
    /**
     * @var bool 是否将每个记录作为数组返回。如果为 false（默认值），
     * 将创建 [[modelClass]] 的对象来表示每个记录。
     */
    public $asArray;


    /**
     * 设置 [[asArray]] 属性。
     * @param bool $value 是否按数组而不是活动记录返回查询结果。
     * @return $this 查询对象本身
     */
    public function asArray($value = true)
    {
        $this->asArray = $value;
        return $this;
    }

    /**
     * 指定应该执行此查询的关系。
     *
     * 此方法的参数可以是一个或多个字符串，
     * 也可以是单个关系名称数组和自定义关系的可选回调。
     *
     * 关系名称可以指在 [[modelClass]]
     * 中定义的关系或代表相关记录关系的子关系。
     * 例如，`orders.address` 是指模型类中定义的对应于
     * `orders` 关系的 `address` 关系。
     *
     * 以下是一些用法示例：
     *
     * ```php
     * // find customers together with their orders and country
     * Customer::find()->with('orders', 'country')->all();
     * // find customers together with their orders and the orders' shipping address
     * Customer::find()->with('orders.address')->all();
     * // find customers together with their country and orders of status 1
     * Customer::find()->with([
     *     'orders' => function (\yii\db\ActiveQuery $query) {
     *         $query->andWhere('status = 1');
     *     },
     *     'country',
     * ])->all();
     * ```
     *
     * 你可以多次调用 `with()`。每次调用都将在现有基础上增加新的关系。
     * 例如，以下两个语句是等效的：
     *
     * ```php
     * Customer::find()->with('orders', 'country')->all();
     * Customer::find()->with('orders')->with('country')->all();
     * ```
     *
     * @return $this 查询对象本身
     */
    public function with()
    {
        $with = func_get_args();
        if (isset($with[0]) && is_array($with[0])) {
            // the parameter is given as an array
            $with = $with[0];
        }

        if (empty($this->with)) {
            $this->with = $with;
        } elseif (!empty($with)) {
            foreach ($with as $name => $value) {
                if (is_int($name)) {
                    // repeating relation is fine as normalizeRelations() handle it well
                    $this->with[] = $value;
                } else {
                    $this->with[$name] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * 将找到的行转换为模型实例。
     * @param array $rows
     * @return array|ActiveRecord[]
     * @since 2.0.11
     */
    protected function createModels($rows)
    {
        if ($this->asArray) {
            return $rows;
        } else {
            $models = [];
            /* @var $class ActiveRecord */
            $class = $this->modelClass;
            foreach ($rows as $row) {
                $model = $class::instantiate($row);
                $modelClass = get_class($model);
                $modelClass::populateRecord($model, $row);
                $models[] = $model;
            }
            return $models;
        }
    }

    /**
     * 查找对应于一个或多个关系的记录，并将它们填充到主模型中。
     * @param array $with 这个查询应该使用的关系列表。
     * 有关指定此参数的详细信息，请参考 [[with()]]。
     * @param array|ActiveRecord[] $models 主要模型（可以是 AR 实例或数组）。
     */
    public function findWith($with, &$models)
    {
        $primaryModel = reset($models);
        if (!$primaryModel instanceof ActiveRecordInterface) {
            /* @var $modelClass ActiveRecordInterface */
            $modelClass = $this->modelClass;
            $primaryModel = $modelClass::instance();
        }
        $relations = $this->normalizeRelations($primaryModel, $with);
        /* @var $relation ActiveQuery */
        foreach ($relations as $name => $relation) {
            if ($relation->asArray === null) {
                // inherit asArray from primary query
                $relation->asArray($this->asArray);
            }
            $relation->populateRelation($name, $models);
        }
    }

    /**
     * @param ActiveRecord $model
     * @param array $with
     * @return ActiveQueryInterface[]
     */
    private function normalizeRelations($model, $with)
    {
        $relations = [];
        foreach ($with as $name => $callback) {
            if (is_int($name)) {
                $name = $callback;
                $callback = null;
            }
            if (($pos = strpos($name, '.')) !== false) {
                // with sub-relations
                $childName = substr($name, $pos + 1);
                $name = substr($name, 0, $pos);
            } else {
                $childName = null;
            }

            if (!isset($relations[$name])) {
                $relation = $model->getRelation($name);
                $relation->primaryModel = null;
                $relations[$name] = $relation;
            } else {
                $relation = $relations[$name];
            }

            if (isset($childName)) {
                $relation->with[$childName] = $callback;
            } elseif ($callback !== null) {
                call_user_func($callback, $relation);
            }
        }

        return $relations;
    }
}

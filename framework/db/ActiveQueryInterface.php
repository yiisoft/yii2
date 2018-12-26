<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveQueryInterface 定义了由活动记录查询类实现的通用接口。
 *
 * 这是用于返回活动记录的普通查询的方法，也是关系查询的方法。
 * 其中查询表示两个活动记录类之间的关系，
 * 并将仅返回已关联的记录。
 *
 * 实现此接口的类还应该使用 [[ActiveQueryTrait]] 和 [[ActiveRelationTrait]]。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveQueryInterface extends QueryInterface
{
    /**
     * 设置 [[asArray]] 属性。
     * @param bool $value 是否按数组而不是活动记录返回查询结果。
     * @return $this 查询对象本身
     */
    public function asArray($value = true);

    /**
     * 执行查询并返回单行结果。
     * @param Connection $db 用于创建数据库命令的数据库连接。
     * 如果为 `null`，将使用 [[ActiveQueryTrait::$modelClass|modelClass]] 返回的数据库连接。
     * @return ActiveRecordInterface|array|null 查询结果的单行。取决于 [[asArray]] 的设置，
     * 查询结果可以是数组或活动记录对象。
     * 如果查询没有结果将返回 `null`。
     */
    public function one($db = null);

    /**
     * 设置 [[indexBy]] 属性。
     * @param string|callable $column 查询结果中应被索引的列的名称。
     * 也可以是基于给定行或模型数据返回索引值的可调用函数（例如匿名函数）。
     * 可调用的签名应该是：
     *
     * ```php
     * // $model is an AR instance when `asArray` is false,
     * // or an array of column values when `asArray` is true.
     * function ($model)
     * {
     *     // return the index value corresponding to $model
     * }
     * ```
     *
     * @return $this 查询对象本身
     */
    public function indexBy($column);

    /**
     * 指定应执行此查询的关系。
     *
     * 此方法的参数可以是一个或多个字符串，
     * 也可以是关系名称的单个数组以及自定义关系的可选回调。
     *
     * 关系名称可以指在 [[ActiveQueryTrait::modelClass|modelClass]]
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
     * @return $this 查询对象本身
     */
    public function with();

    /**
     * 指定与连接表相关联的关系，用于关系查询。
     * @param string $relationName 关系名称。在关系的 [[ActiveRelationTrait::primaryModel|primaryModel]] 中声明的关系。
     * @param callable $callable PHP 回调，用于自定义与连接表相关联的关系。
     * 它的签名应该是 `function($query)`，其中 `$query` 是要定制的查询。
     * @return $this 关系对象本身。
     */
    public function via($relationName, callable $callable = null);

    /**
     * 查找指定主记录的相关记录。
     * 当以惰性方式访问活动记录的关系时，调用此方法。
     * @param string $name 关系名称
     * @param ActiveRecordInterface $model 主模型
     * @return mixed 混合的相关记录
     */
    public function findFor($name, $model);
}

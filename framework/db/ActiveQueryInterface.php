<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveQueryInterface defines the common interface to be implemented by active record query classes.
 *
 * That are methods for either normal queries that return active records but also relational queries
 * in which the query represents a relation between two active record classes and will return related
 * records only.
 *
 * A class implementing this interface should also use [[ActiveQueryTrait]] and [[ActiveRelationTrait]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveQueryInterface extends QueryInterface
{
    /**
     * Sets the [[asArray]] property.
     * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
     * @return static the query object itself
     */
    public function asArray($value = true);

    /**
     * Sets the [[indexBy]] property.
     * @param string|callable $column the name of the column by which the query results should be indexed by.
     * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
     * row or model data. The signature of the callable should be:
     *
     * ~~~
     * // $model is an AR instance when `asArray` is false,
     * // or an array of column values when `asArray` is true.
     * function ($model)
     * {
     *     // return the index value corresponding to $model
     * }
     * ~~~
     *
     * @return static the query object itself
     */
    public function indexBy($column);

    /**
     * Specifies the relations with which this query should be performed.
     *
     * The parameters to this method can be either one or multiple strings, or a single array
     * of relation names and the optional callbacks to customize the relations.
     *
     * A relation name can refer to a relation defined in [[ActiveQueryTrait::modelClass|modelClass]]
     * or a sub-relation that stands for a relation of a related record.
     * For example, `orders.address` means the `address` relation defined
     * in the model class corresponding to the `orders` relation.
     *
     * The followings are some usage examples:
     *
     * ~~~
     * // find customers together with their orders and country
     * Customer::find()->with('orders', 'country')->all();
     * // find customers together with their orders and the orders' shipping address
     * Customer::find()->with('orders.address')->all();
     * // find customers together with their country and orders of status 1
     * Customer::find()->with([
     *     'orders' => function ($query) {
     *         $query->andWhere('status = 1');
     *     },
     *     'country',
     * ])->all();
     * ~~~
     *
     * @return static the query object itself
     */
    public function with();

    /**
     * Specifies the relation associated with the junction table for use in relational query.
     * @param string $relationName the relation name. This refers to a relation declared in the [[ActiveRelationTrait::primaryModel|primaryModel]] of the relation.
     * @param callable $callable a PHP callback for customizing the relation associated with the junction table.
     * Its signature should be `function($query)`, where `$query` is the query to be customized.
     * @return static the relation object itself.
     */
    public function via($relationName, callable $callable = null);

    /**
     * Finds the related records for the specified primary record.
     * This method is invoked when a relation of an ActiveRecord is being accessed in a lazy fashion.
     * @param string $name the relation name
     * @param ActiveRecordInterface $model the primary model
     * @return mixed the related record(s)
     */
    public function findFor($name, $model);
}

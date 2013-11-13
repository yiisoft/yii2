<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\ar;
use yii\db\QueryInterface;

/**
 * ActiveQueryInterface defines the common interface to be implemented by active record relation classes.
 *
 * A class implementing this interface should also use [[ActiveQueryTrait]].
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
	 * Specifies the relations with which this query should be performed.
	 *
	 * The parameters to this method can be either one or multiple strings, or a single array
	 * of relation names and the optional callbacks to customize the relations.
	 *
	 * A relation name can refer to a relation defined in [[modelClass]]
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
	 *     'orders' => function($query) {
	 *         $query->andWhere('status = 1');
	 *     },
	 *     'country',
	 * ])->all();
	 * ~~~
	 *
	 * @return static the query object itself
	 */
	public function with();
}

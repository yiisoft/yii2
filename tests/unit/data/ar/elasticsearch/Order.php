<?php

namespace yiiunit\data\ar\elasticsearch;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $create_time
 * @property string $total
 */
class Order extends ActiveRecord
{
	public static function attributes()
	{
		return ['customer_id', 'create_time', 'total'];
	}

	public function getCustomer()
	{
		return $this->hasOne(Customer::className(), ['primaryKey' => 'customer_id']);
	}

	public function getOrderItems()
	{
		return $this->hasMany(OrderItem::className(), ['order_id' => 'primaryKey']);
	}

	public function getItems()
	{
		return $this->hasMany(Item::className(), ['primaryKey' => 'item_id'])
			->via('orderItems')->orderBy('name');
	}

//	public function getBooks()
//	{
//		return $this->hasMany('Item', ['primaryKey' => 'item_id'])
//			->viaTable('tbl_order_item', ['order_id' => 'primaryKey'])
//			->where(['category_id' => 1]);
//	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
//			$this->create_time = time();
			return true;
		} else {
			return false;
		}
	}
}

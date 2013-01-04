<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
	}

	public function customer()
	{
		return $this->hasOne('Customer', array('id' => 'customer_id'));
	}

	public function orderItems()
	{
		return $this->hasMany('OrderItem', array('order_id' => 'id'));
	}

	public function items()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->via('orderItems')
			->orderBy('id');
	}

	public function books()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->viaTable('tbl_order_item', array('order_id' => 'id'))
			->where(array('category_id' => 1));
	}
}
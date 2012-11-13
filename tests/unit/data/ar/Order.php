<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public function tableName()
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
			->via('orderItems')->orderBy('id');
	}

	public function books()
	{
		return $this->manyMany('Item', array('id' => 'item_id'), 'tbl_order_item', array('item_id', 'id'))
			->where('category_id = 1');
	}
}
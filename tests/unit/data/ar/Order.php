<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
	}

	public function getCustomer()
	{
		return $this->hasOne('Customer', array('id' => 'customer_id'));
	}

	public function getOrderItems()
	{
		return $this->hasMany('OrderItem', array('order_id' => 'id'));
	}

	public function getItems()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->via('orderItems', function ($q) {
				// additional query configuration
			})->orderBy('id');
	}

	public function getBooks()
	{
		return $this->hasMany('Item', array('id' => 'item_id'))
			->viaTable('tbl_order_item', array('order_id' => 'id'))
			->where(array('category_id' => 1));
	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			$this->create_time = time();
			return true;
		} else {
			return false;
		}
	}
}

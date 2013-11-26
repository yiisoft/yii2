<?php

namespace yiiunit\data\ar\redis;

class Order extends ActiveRecord
{
	public static function attributes()
	{
		return ['id', 'customer_id', 'create_time', 'total'];
	}

	public function getCustomer()
	{
		return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
	}

	public function getOrderItems()
	{
		return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
	}

	public function getItems()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', function($q) {
				// additional query configuration
			});
	}

	public function getItemsInOrder1()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', function ($q) {
				$q->orderBy(['subtotal' => SORT_ASC]);
			})->orderBy('name');
	}

	public function getItemsInOrder2()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', function ($q) {
				$q->orderBy(['subtotal' => SORT_DESC]);
			})->orderBy('name');
	}

	public function getBooks()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', ['order_id' => 'id']);
			//->where(['category_id' => 1]);
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
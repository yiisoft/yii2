<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class Order extends ActiveRecord
{
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


	public static function getRecordSchema()
	{
		return new RecordSchema(array(
			'name' => 'orders',
			'primaryKey' => ['id'],
			'columns' => array(
				'id' => 'integer',
				'customer_id' => 'integer',
				'create_time' => 'integer',
				'total' => 'decimal',
			)
		));
	}

}
<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class OrderItem extends ActiveRecord
{
	public function getOrder()
	{
		return $this->hasOne(Order::className(), ['id' => 'order_id']);
	}

	public function getItem()
	{
		return $this->hasOne(Item::className(), ['id' => 'item_id']);
	}

	public static function getRecordSchema()
	{
		return new RecordSchema(array(
			'name' => 'order_item',
			'primaryKey' => ['order_id', 'item_id'],
			'columns' => array(
				'order_id' => 'integer',
				'item_id' => 'integer',
				'quantity' => 'integer',
				'subtotal' => 'decimal',
			)
		));
	}
}
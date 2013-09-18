<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class OrderItem extends ActiveRecord
{
	public function getOrder()
	{
		return $this->hasOne('Order', array('id' => 'order_id'));
	}

	public function getItem()
	{
		return $this->hasOne('Item', array('id' => 'item_id'));
	}

	public static function getTableSchema()
	{
		return new RecordSchema(array(
			'name' => 'order_item',
			'primaryKey' => array('order_id', 'item_id'),
			'columns' => array(
				'order_id' => 'integer',
				'item_id' => 'integer',
				'quantity' => 'integer',
				'subtotal' => 'decimal',
			)
		));
	}
}
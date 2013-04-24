<?php

namespace yiiunit\data\ar\redis;

use yii\db\TableSchema;

class OrderItem extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order_item';
	}

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
		return new TableSchema(array(
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
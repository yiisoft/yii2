<?php

namespace yiiunit\data\ar;

class OrderItem extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order_item';
	}

	public function getOrder()
	{
		return $this->hasOne('Order', ['id' => 'order_id']);
	}

	public function getItem()
	{
		return $this->hasOne('Item', ['id' => 'item_id']);
	}
}

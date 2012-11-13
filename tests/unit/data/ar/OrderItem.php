<?php

namespace yiiunit\data\ar;

class OrderItem extends ActiveRecord
{
	public function tableName()
	{
		return 'tbl_order_item';
	}

	public function order()
	{
		return $this->hasOne('Order', array('id' => 'order_id'));
	}

	public function item()
	{
		return $this->hasOne('Item', array('id' => 'item_id'));
	}
}
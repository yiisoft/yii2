<?php

namespace yiiunit\data\ar;

class OrderItem extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order_item';
	}

	public static function relations()
	{
		return array(
			'order:Order' => array(
				'link' => array('order_id' => 'id'),
			),
			'item:Item' => array(
				'link' => array('item_id' => 'id'),
			),
		);
	}
}
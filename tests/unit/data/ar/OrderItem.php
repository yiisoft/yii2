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
				'on' => '@.order_id = ?.id',
			),
			'item:Item' => array(
				'on' => '@.item_id = ?.id',
			),
		);
	}
}
<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
	}

	public static function relations()
	{
		return array(
			'customer:Customer' => array(
				'link' => array('id' => 'customer_id'),
			),
			'orderItems:OrderItem' => array(
				'link' => array('order_id' => 'id'),
			),
			'items:Item[]' => array(
				'via' => 'orderItems',
				'link' => array(
					'id' => 'item_id',
				),
				'order' => '@.id',
			),
			'books:Item[]' => array(
				'joinType' => 'INNER JOIN',
				'via' => array(
					'table' => 'tbl_order_item',
					'link' => array(
						'order_id' => 'id',
					),
				),
				'link' => array(
					'id' => 'item_id',
				),
				'on' => '@.category_id = 1',
			),
		);
	}
}
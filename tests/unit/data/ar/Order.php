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

	public function customer()
	{
		return $this->hasOne('Customer', array('id' => 'customer_id'));
	}

	public function orderItems()
	{
		return $this->hasMany('OrderItem', array('order_id' => 'id'));
	}

	public function items()
	{
		return $this->hasMany('Item')
			->via('orderItems', array('item_id' => 'id'))
			->order('@.id');
	}

	public function books()
	{
		return $this->hasMany('Item')
			->pivot('tbl_order_item', array('order_id' => 'id'), array('item_id' => 'id'))
			->on('@.category_id = 1');
	}
}
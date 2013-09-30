<?php

namespace yiiunit\data\ar\elasticsearch;

/**
 * Class OrderItem
 *
 * @property integer $order_id
 * @property integer $item_id
 * @property integer $quantity
 * @property string $subtotal
 */
class OrderItem extends ActiveRecord
{
	public static function columns()
	{
		return array(
			'order_id' => 'integer',
			'item_id' => 'integer',
			'quantity' => 'integer',
			'subtotal' => 'integer',
		);
	}

	public function getOrder()
	{
		return $this->hasOne('Order', array('id' => 'order_id'));
	}

	public function getItem()
	{
		return $this->hasOne('Item', array('id' => 'item_id'));
	}
}

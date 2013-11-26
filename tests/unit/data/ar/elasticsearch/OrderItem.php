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
	public static function attributes()
	{
		return ['order_id', 'item_id', 'quantity', 'subtotal'];
	}

	public function getOrder()
	{
		return $this->hasOne(Order::className(), [ActiveRecord::PRIMARY_KEY_NAME => 'order_id']);
	}

	public function getItem()
	{
		return $this->hasOne(Item::className(), [ActiveRecord::PRIMARY_KEY_NAME => 'item_id']);
	}
}

<?php

namespace yiiunit\data\ar\elasticsearch;

use yii\elasticsearch\Command;

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
	public function attributes()
	{
		return ['order_id', 'item_id', 'quantity', 'subtotal'];
	}

	public function getOrder()
	{
		return $this->hasOne(Order::className(), ['id' => 'order_id']);
	}

	public function getItem()
	{
		return $this->hasOne(Item::className(), ['id' => 'item_id']);
	}

	/**
	 * sets up the index for this record
	 * @param Command $command
	 */
	public static function setUpMapping($command)
	{
		$command->deleteMapping(static::index(), static::type());
		$command->setMapping(static::index(), static::type(), [
			static::type() => [
				"properties" => [
					"order_id" => ["type" => "integer"],
					"item_id"  => ["type" => "integer"],
					"quantity" => ["type" => "integer"],
					"subtotal" => ["type" => "integer"],
				]
			]
		]);

	}
}

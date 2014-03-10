<?php

namespace yiiunit\data\ar\elasticsearch;

use yii\elasticsearch\Command;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class Order extends ActiveRecord
{
	public static function primaryKey()
	{
		return ['id'];
	}

	public function attributes()
	{
		return ['id', 'customer_id', 'created_at', 'total'];
	}

	public function getCustomer()
	{
		return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
	}

	public function getOrderItems()
	{
		return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
	}

	public function getItems()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems')->orderBy('id');
	}

	public function getItemsInOrder1()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', function ($q) {
				$q->orderBy(['subtotal' => SORT_ASC]);
			})->orderBy('name');
	}

	public function getItemsInOrder2()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->via('orderItems', function ($q) {
				$q->orderBy(['subtotal' => SORT_DESC]);
			})->orderBy('name');
	}

//	public function getBooks()
//	{
//		return $this->hasMany('Item', ['id' => 'item_id'])
//			->viaTable('tbl_order_item', ['order_id' => 'id'])
//			->where(['category_id' => 1]);
//	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
//			$this->created_at = time();
			return true;
		} else {
			return false;
		}
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
				"_id" => ["path" => "id", "index" => "not_analyzed", "store" => "yes"],
				"properties" => [
					"customer_id" => ["type" => "integer"],
//					"created_at" => ["type" => "string", "index" => "not_analyzed"],
					"total" => ["type" => "integer"],
				]
			]
		]);

	}
}

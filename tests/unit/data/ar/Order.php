<?php

namespace yiiunit\data\ar;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $create_time
 * @property string $total
 */
class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
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
			->via('orderItems', function ($q) {
				// additional query configuration
			})->orderBy('id');
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

	public function getBooks()
	{
		return $this->hasMany(Item::className(), ['id' => 'item_id'])
			->viaTable('tbl_order_item', ['order_id' => 'id'])
			->where(['category_id' => 1]);
	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			$this->create_time = time();
			return true;
		} else {
			return false;
		}
	}
}

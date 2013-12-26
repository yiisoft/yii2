<?php

namespace yiiunit\data\ar\mongodb;

class Customer extends ActiveRecord
{
	public static function collectionName()
	{
		return 'customer';
	}

	public function attributes()
	{
		return [
			'_id',
			'name',
			'email',
			'address',
			'status',
		];
	}

	public static function activeOnly($query)
	{
		$query->andWhere(['status' => 2]);
	}

	public function getOrders()
	{
		return $this->hasMany(CustomerOrder::className(), ['customer_id' => '_id']);
	}
}
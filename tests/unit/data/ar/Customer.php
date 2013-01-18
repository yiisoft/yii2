<?php

namespace yiiunit\data\ar;
use yii\db\ActiveQuery;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public $status2;

	public static function tableName()
	{
		return 'tbl_customer';
	}

	public function getOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'))->orderBy('id');
	}

	public static function active($query)
	{
		return $query->andWhere('status=1');
	}
}
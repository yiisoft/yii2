<?php

namespace yiiunit\data\ar;

class Customer extends ActiveRecord
{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	public static function tableName()
	{
		return 'tbl_customer';
	}

	public static function relations()
	{
		return array(
			'orders:Order[]' => array(
				'link' => array('customer_id' => 'id'),
			),
		);
	}

	public function active($query)
	{
		return $query->andWhere('@.`status` = ' . self::STATUS_ACTIVE);
	}
}
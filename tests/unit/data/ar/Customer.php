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
				'on' => '@.customer_id = ?.id',
			),
		);
	}

	public static function scopes()
	{
		return array(
			'active' => function($q) {
				return $q->andWhere('@.status = ' . self::STATUS_ACTIVE);
			},
		);
	}
}
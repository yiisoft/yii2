<?php

namespace yiiunit\data\ar;

class Customer extends ActiveRecord
{
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
}
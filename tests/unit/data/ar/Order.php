<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
	}

	public static function relations()
	{
		return array(
			'customer:Customer' => array(
				'on' => '@.id = ?.customer_id',
			),
		);
	}
}
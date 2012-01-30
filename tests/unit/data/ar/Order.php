<?php

namespace yiiunit\data\ar;

class Order extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_order';
	}
}
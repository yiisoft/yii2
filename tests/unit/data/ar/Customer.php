<?php

namespace yiiunit\data\ar;

class Customer extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_customer';
	}
}
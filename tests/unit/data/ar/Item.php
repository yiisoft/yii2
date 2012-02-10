<?php

namespace yiiunit\data\ar;

class Item extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_item';
	}

	public static function relations()
	{
		return array(
		);
	}
}
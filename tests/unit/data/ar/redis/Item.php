<?php

namespace yiiunit\data\ar\redis;

class Item extends ActiveRecord
{
	public static function attributes()
	{
		return ['id', 'name', 'category_id'];
	}
}
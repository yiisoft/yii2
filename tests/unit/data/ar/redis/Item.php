<?php

namespace yiiunit\data\ar\redis;

class Item extends ActiveRecord
{
	public function attributes()
	{
		return ['id', 'name', 'category_id'];
	}
}
<?php

namespace yiiunit\data\ar\elasticsearch;

/**
 * Class Item
 *
 * @property integer $id
 * @property string $name
 * @property integer $category_id
 */
class Item extends ActiveRecord
{
	public static function columns()
	{
		return array(
			'id' => 'integer',
			'name' => 'string',
			'category_id' => 'integer',
		);
	}
}

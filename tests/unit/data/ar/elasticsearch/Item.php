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
	public function attributes()
	{
		return ['name', 'category_id'];
	}
}

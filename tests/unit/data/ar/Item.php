<?php

namespace yiiunit\data\ar;

/**
 * Class Item
 *
 * @property integer $id
 * @property string $name
 * @property integer $category_id
 */
class Item extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_item';
	}
}

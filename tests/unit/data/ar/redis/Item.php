<?php

namespace yiiunit\data\ar\redis;

use yii\db\TableSchema;

class Item extends ActiveRecord
{
	public static function tableName()
	{
		return 'tbl_item';
	}

	public static function getTableSchema()
	{
		return new TableSchema(array(
			'primaryKey' => array('id'),
			'columns' => array(
				'id' => 'integer',
				'name' => 'string',
				'category_id' => 'integer'
			)
		));
	}
}
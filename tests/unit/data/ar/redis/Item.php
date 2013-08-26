<?php

namespace yiiunit\data\ar\redis;

use yii\db\redis\RecordSchema;

class Item extends ActiveRecord
{
	public static function getTableSchema()
	{
		return new RecordSchema(array(
			'name' => 'item',
			'primaryKey' => array('id'),
			'sequenceName' => 'id',
			'foreignKeys' => array(
				// TODO for defining relations
			),
			'columns' => array(
				'id' => 'integer',
				'name' => 'string',
				'category_id' => 'integer'
			)
		));
	}
}
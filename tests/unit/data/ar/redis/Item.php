<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class Item extends ActiveRecord
{
	public static function getRecordSchema()
	{
		return new RecordSchema(array(
			'name' => 'item',
			'primaryKey' => array('id'),
			'sequenceName' => 'id',
			'columns' => array(
				'id' => 'integer',
				'name' => 'string',
				'category_id' => 'integer'
			)
		));
	}
}
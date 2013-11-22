<?php

namespace yiiunit\data\ar\redis;

use yii\redis\RecordSchema;

class Item extends ActiveRecord
{
	public static function getRecordSchema()
	{
		return new RecordSchema([
			'name' => 'item',
			'primaryKey' => ['id'],
			'sequenceName' => 'id',
			'columns' => [
				'id' => 'integer',
				'name' => 'string',
				'category_id' => 'integer'
			]
		]);
	}
}
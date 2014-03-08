<?php

namespace yiiunit\data\ar\sphinx;

use yiiunit\data\ar\ActiveRecord as ActiveRecordDb;

class ItemDb extends ActiveRecordDb
{
	public static function tableName()
	{
		return 'yii2_test_item';
	}
}

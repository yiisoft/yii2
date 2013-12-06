<?php

namespace yiiunit\data\ar\mongodb;

/**
 * Test Mongo ActiveRecord
 */
class ActiveRecord extends \yii\mongodb\ActiveRecord
{
	public static $db;

	public static function getDb()
	{
		return self::$db;
	}
}
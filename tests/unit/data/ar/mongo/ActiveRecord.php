<?php

namespace yiiunit\data\ar\mongo;

/**
 * Test Mongo ActiveRecord
 */
class ActiveRecord extends \yii\mongo\ActiveRecord
{
	public static $db;

	public static function getDb()
	{
		return self::$db;
	}
}
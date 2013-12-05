<?php

namespace yiiunit\data\ar\mongo\file;

/**
 * Test Mongo ActiveRecord
 */
class ActiveRecord extends \yii\mongo\file\ActiveRecord
{
	public static $db;

	public static function getDb()
	{
		return self::$db;
	}
}
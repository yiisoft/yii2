<?php

namespace yiiunit\data\sphinx\ar;

/**
 * Test Sphinx ActiveRecord class
 */
class ActiveRecord extends \yii\sphinx\ActiveRecord
{
	public static $db;

	public static function getDb()
	{
		return self::$db;
	}
}
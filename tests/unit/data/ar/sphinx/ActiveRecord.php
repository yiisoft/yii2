<?php

namespace yiiunit\data\ar\sphinx;

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

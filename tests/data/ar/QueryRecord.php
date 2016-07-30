<?php

namespace yiiunit\data\ar;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 */
abstract class QueryRecord extends \yii\db\QueryRecord
{
    public static $db;

    public static function getDb()
    {
        return self::$db;
    }
}

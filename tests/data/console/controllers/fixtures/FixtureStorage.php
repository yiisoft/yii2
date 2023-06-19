<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\console\controllers\fixtures;

class FixtureStorage
{
    public static $globalFixturesData = [];

    public static $firstFixtureData = [];

    public static $secondFixtureData = [];

    public static $subdirFirstFixtureData = [];

    public static $subdirSecondFixtureData = [];

    public static $activeFixtureSequence = [];

    public static function clear()
    {
        static::$globalFixturesData = [];
        static::$firstFixtureData = [];
        static::$secondFixtureData = [];
        static::$subdirFirstFixtureData = [];
        static::$subdirSecondFixtureData = [];
        static::$activeFixtureSequence = [];
    }
}

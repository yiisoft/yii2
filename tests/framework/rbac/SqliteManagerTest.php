<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

/**
 * SqliteManagerTest.
 * @group db
 * @group rbac
 * @group sqlite
 */
class SqliteManagerTest extends DbManagerTestCase
{
    protected static $driverName = 'sqlite';

    protected static $sqliteDb;

    public static function createConnection()
    {
        // sqlite db is in memory so it can not be reused
        if (static::$sqliteDb === null) {
            static::$sqliteDb = parent::createConnection();
        }

        return static::$sqliteDb;
    }
}

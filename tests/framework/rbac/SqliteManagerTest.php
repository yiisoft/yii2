<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Connection;

/**
 * Unit tests for {@see \yii\rbac\DbManager} backed by SQLite.
 */
#[Group('db')]
#[Group('rbac')]
#[Group('sqlite')]
class SqliteManagerTest extends DbManagerTestCase
{
    protected static $driverName = 'sqlite';

    protected static $sqliteDb;

    public static function createConnection(): Connection
    {
        // sqlite db is in memory so it can not be reused
        if (static::$sqliteDb === null) {
            static::$sqliteDb = parent::createConnection();
        }

        return static::$sqliteDb;
    }
}

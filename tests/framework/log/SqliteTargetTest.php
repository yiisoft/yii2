<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Connection;
use yiiunit\base\log\BaseDbTarget;

use function dirname;
use function is_file;
use function unlink;

/**
 * Unit test for {@see \yii\log\DbTarget} with SQLite driver.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('log')]
final class SqliteTargetTest extends BaseDbTarget
{
    protected static $driverName = 'sqlite';

    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection();

            $db->dsn = 'sqlite:' . self::getDbFilePath();

            if (!$db->isActive) {
                $db->open();
            }

            static::$db = $db;
        }

        return static::$db;
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (is_file(self::getDbFilePath())) {
            unlink(self::getDbFilePath());
        }
    }

    private static function getDbFilePath(): string
    {
        return dirname(__DIR__, 2) . '/runtime/sqlite-log-target.sq3';
    }
}

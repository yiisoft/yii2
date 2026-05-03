<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\Group;
use yii\caching\FileCache;
use yii\rbac\DbManager;
use yii\rbac\ManagerInterface;

/**
 * Unit tests for {@see \yii\rbac\DbManager} backed by SQLite with file-based cache enabled.
 *
 * Exercises the cache-hit branches of `DbManager` (`loadFromCache`, `getRolesByUser`, etc.) without requiring an
 * external database server.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('rbac')]
#[Group('sqlite')]
final class SqliteManagerCacheTest extends SqliteManagerTest
{
    /**
     * @return ManagerInterface
     */
    protected function createManager(): ManagerInterface
    {
        return new DbManager(
            [
                'db' => $this->getConnection(),
                'cache' => new FileCache(['cachePath' => '@yiiunit/runtime/cache']),
                'defaultRoles' => ['myDefaultRole'],
            ],
        );
    }
}

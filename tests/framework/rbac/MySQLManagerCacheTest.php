<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\Group;
use yii\rbac\ManagerInterface;
use yii\caching\FileCache;
use yii\rbac\DbManager;

/**
 * Unit tests for {@see \yii\rbac\DbManager} backed by MySQL with file-based cache enabled.
 *
 * Exercises the cache-hit branches of `DbManager` (`loadFromCache`, `getRolesByUser`, etc.) without requiring an
 * external database server.
 */
#[Group('db')]
#[Group('rbac')]
#[Group('mysql')]
final class MySQLManagerCacheTest extends MySQLManagerTest
{
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

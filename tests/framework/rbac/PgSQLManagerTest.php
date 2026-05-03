<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use PHPUnit\Framework\Attributes\Group;

/**
 * Unit tests for {@see \yii\rbac\DbManager} backed by PostgreSQL.
 */
#[Group('db')]
#[Group('rbac')]
#[Group('pgsql')]
final class PgSQLManagerTest extends DbManagerTestCase
{
    protected static $driverName = 'pgsql';
}

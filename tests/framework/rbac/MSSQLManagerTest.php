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
 * Unit tests for {@see \yii\rbac\DbManager} backed by Microsoft SQL Server.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('rbac')]
#[Group('mssql')]
class MSSQLManagerTest extends DbManagerTestCase
{
    protected static $driverName = 'sqlsrv';
}

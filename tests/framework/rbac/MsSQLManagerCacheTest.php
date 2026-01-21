<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\rbac;

use yii\caching\FileCache;
use yii\rbac\DbManager;

/**
 * PgSQLManagerTest.
 * @group db
 * @group rbac
 * @group mssql
 */
class MsSQLManagerCacheTest extends DbManagerTestCase
{
    protected static $driverName = 'sqlsrv';
}

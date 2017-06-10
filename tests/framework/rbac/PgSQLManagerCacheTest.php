<?php
namespace yiiunit\framework\rbac;

use yii\caching\FileCache;
use yii\rbac\DbManager;

/**
 * PgSQLManagerTest
 * @group db
 * @group rbac
 * @group pgsql
 */
class PgSQLManagerCacheTest extends DbManagerTestCase
{
    protected static $driverName = 'pgsql';

    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager([
            'db' => $this->getConnection(),
            'cache' => new FileCache(['cachePath' => '@yiiunit/runtime/cache']),
            'defaultRoles' => ['myDefaultRole']
        ]);
    }

}

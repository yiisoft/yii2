<?php
namespace yiiunit\framework\rbac;

use yii\caching\FileCache;
use yii\rbac\DbManager;

/**
 * MySQLManagerCacheTest
 * @group db
 * @group rbac
 */
class MySQLManagerCacheTest extends MySQLManagerTest
{
    /**
     * @return \yii\rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager([
            'db' => $this->getConnection(),
            'cache' => new FileCache(['cachePath' => '@yiiunit/runtime/cache']),
        ]);
    }
}

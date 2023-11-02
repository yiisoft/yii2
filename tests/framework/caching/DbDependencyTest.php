<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\ArrayCache;
use yii\caching\DbDependency;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group caching
 */
class DbDependencyTest extends DatabaseTestCase
{
    /**
     * {@inheritdoc}
     */
    protected $driverName = 'sqlite';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $db = $this->getConnection(false);

        $db->createCommand()->createTable('dependency_item', [
            'id' => 'pk',
            'value' => 'string',
        ])->execute();

        $db->createCommand()->insert('dependency_item', ['value' => 'initial'])->execute();
    }

    public function testQueryOneIsExecutedWhenQueryCacheEnabled()
    {
        $db = $this->getConnection(false);
        $cache = new ArrayCache();

        // Enable the query cache
        $db->enableQueryCache = true;

        $dependency = new DbDependency();
        $dependency->db = $db;
        $dependency->sql = 'SELECT [[id]] FROM {{dependency_item}} ORDER BY [[id]] DESC LIMIT 1';
        $dependency->reusable = false;

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));

        $db->createCommand()->insert('dependency_item', ['value' => 'new'])->execute();

        $this->assertTrue($dependency->isChanged($cache));
    }

    public function testQueryOneIsExecutedWhenQueryCacheDisabled()
    {
        $db = $this->getConnection(false);
        $cache = new ArrayCache();

        // Disable the query cache
        $db->enableQueryCache = false;

        $dependency = new DbDependency();
        $dependency->db = $db;
        $dependency->sql = 'SELECT [[id]] FROM {{dependency_item}} ORDER BY [[id]] DESC LIMIT 1';
        $dependency->reusable = false;

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));

        $db->createCommand()->insert('dependency_item', ['value' => 'new'])->execute();

        $this->assertTrue($dependency->isChanged($cache));
    }

    public function testMissingSqlThrowsException()
    {
        $this->expectException('\yii\base\InvalidConfigException');

        $db = $this->getConnection(false);
        $cache = new ArrayCache();

        $dependency = new DbDependency();
        $dependency->db = $db;
        $dependency->sql = null;

        $dependency->evaluateDependency($cache);
    }
}

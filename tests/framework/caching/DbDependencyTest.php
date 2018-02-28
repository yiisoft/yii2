<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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

    public function testIsChanged()
    {
        $db = $this->getConnection(false);
        $cache = new ArrayCache();

        $dependency = new DbDependency();
        $dependency->db = $db;
        $dependency->sql = 'SELECT [[id]] FROM {{dependency_item}} ORDER BY [[id]] DESC LIMIT 1';
        $dependency->reusable = false;

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));

        $db->createCommand()->insert('dependency_item', ['value' => 'new'])->execute();

        $this->assertTrue($dependency->isChanged($cache));
    }
}

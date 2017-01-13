<?php

namespace yiiunit\framework\caching;

use yii\caching\ArrayCache;
use yii\caching\DbQueryDependency;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;

class DbQueryDependencyTest extends DatabaseTestCase
{
    /**
     * @inheritdoc
     */
    protected $driverName = 'sqlite';


    /**
     * @inheritdoc
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

        $dependency = new DbQueryDependency();
        $dependency->db = $db;
        $dependency->query = (new Query())
            ->select(['id'])
            ->from('dependency_item')
            ->orderBy(['id' => SORT_DESC])
            ->limit(1);
        $dependency->reusable = false;

        $dependency->evaluateDependency($cache);
        $this->assertFalse($dependency->isChanged($cache));

        $db->createCommand()->insert('dependency_item', ['value' => 'new'])->execute();

        $this->assertTrue($dependency->isChanged($cache));
    }
}
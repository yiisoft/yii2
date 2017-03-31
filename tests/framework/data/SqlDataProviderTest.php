<?php

namespace yiiunit\framework\data;

use yii\data\ArrayDataProvider;
use yii\data\SqlDataProvider;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\sqlite\ConnectionTest;
use yiiunit\TestCase;

/**
 * @group data
 */
class SqlDataProviderTest extends DatabaseTestCase
{
    protected $driverName = 'sqlite';

    public function testGetModels()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => 'select * from `customer`',
            'db' => $this->getConnection()
        ]);
        $this->assertEquals(3, count($dataProvider->getModels()));
    }

    public function testTotalCount()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => 'select * from `customer`',
            'db' => $this->getConnection()
        ]);
        $this->assertEquals(3, $dataProvider->getTotalCount());
    }



}

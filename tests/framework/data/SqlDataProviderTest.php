<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\SqlDataProvider;
use yiiunit\framework\db\DatabaseTestCase;

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
            'db' => $this->getConnection(),
        ]);
        $this->assertCount(3, $dataProvider->getModels());
    }

    public function testTotalCount()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => 'select * from `customer`',
            'db' => $this->getConnection(),
        ]);
        $this->assertEquals(3, $dataProvider->getTotalCount());
    }

    public function testTotalCountWithParams()
    {
        $dataProvider = new SqlDataProvider([
            'sql' => 'select * from `customer` where id > :minimum',
            'params' => [
                ':minimum' => -1,
            ],
            'db' => $this->getConnection(),
        ]);
        $this->assertEquals(3, $dataProvider->getTotalCount());
    }
}

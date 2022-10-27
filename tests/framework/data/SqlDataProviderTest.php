<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

    public function providerForOrderByColumn()
    {
        return [
            'no marks' => ['name'],
            'no marks dot' => ['customer.name'],
            'mysql' => ['`name`'],
            'mysql dot' => ['`customer`.`name`'],
            'sqlite, pgsql, oracle, mysql ansi quotes' => ['"name"'],
            'sqlite, pgsql, oracle, mysql ansi quotes dot' => ['"customer"."name"'],
            'mssql' => ['[name]'],
            'mssql dot' => ['[customer].[name]'],
        ];
    }

    /**
     * @dataProvider providerForOrderByColumn
     * @see https://github.com/yiisoft/yii2/issues/18552
     */
    public function testRemovingOrderBy($column)
    {
        $dataProvider = new SqlDataProvider([
            'sql' => 'select * from `customer` order by ' . $column . ' desc',
            'db' => $this->getConnection(),
            'sort' => [
                'attributes' => ['email'],
                'params' => ['sort' => '-email']
            ],
        ]);
        $modelsSorted = $dataProvider->getModels();
        $this->assertSame('user3', $modelsSorted[0]['name']);
    }
}

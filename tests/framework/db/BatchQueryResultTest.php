<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use Yii;
use yiiunit\data\ar\ActiveRecord;
use yii\db\Query;
use yii\db\BatchQueryResult;
use yiiunit\data\ar\Customer;

/**
 * @group db
 * @group mysql
 */
class BatchQueryResultTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testQuery()
    {
        $db = $this->getConnection();

        // initialize property test
        $query = new Query();
        $query->from('customer')->orderBy('id');
        $result = $query->batch(2, $db);
        $this->assertTrue($result instanceof BatchQueryResult);
        $this->assertEquals(2, $result->batchSize);
        $this->assertTrue($result->query === $query);

        // normal query
        $query = new Query();
        $query->from('customer')->orderBy('id');
        $allRows = [];
        $batch = $query->batch(2, $db);
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertEquals(3, count($allRows));
        $this->assertEquals('user1', $allRows[0]['name']);
        $this->assertEquals('user2', $allRows[1]['name']);
        $this->assertEquals('user3', $allRows[2]['name']);
        // rewind
        $allRows = [];
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertEquals(3, count($allRows));
        // reset
        $batch->reset();

        // empty query
        $query = new Query();
        $query->from('customer')->where(['id' => 100]);
        $allRows = [];
        $batch = $query->batch(2, $db);
        foreach ($batch as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertEquals(0, count($allRows));

        // query with index
        $query = new Query();
        $query->from('customer')->indexBy('name');
        $allRows = [];
        foreach ($query->batch(2, $db) as $rows) {
            $allRows = array_merge($allRows, $rows);
        }
        $this->assertEquals(3, count($allRows));
        $this->assertEquals('address1', $allRows['user1']['address']);
        $this->assertEquals('address2', $allRows['user2']['address']);
        $this->assertEquals('address3', $allRows['user3']['address']);

        // each
        $query = new Query();
        $query->from('customer')->orderBy('id');
        $allRows = [];
        foreach ($query->each(100, $db) as $rows) {
            $allRows[] = $rows;
        }
        $this->assertEquals(3, count($allRows));
        $this->assertEquals('user1', $allRows[0]['name']);
        $this->assertEquals('user2', $allRows[1]['name']);
        $this->assertEquals('user3', $allRows[2]['name']);

        // each with key
        $query = new Query();
        $query->from('customer')->orderBy('id')->indexBy('name');
        $allRows = [];
        foreach ($query->each(100, $db) as $key => $row) {
            $allRows[$key] = $row;
        }
        $this->assertEquals(3, count($allRows));
        $this->assertEquals('address1', $allRows['user1']['address']);
        $this->assertEquals('address2', $allRows['user2']['address']);
        $this->assertEquals('address3', $allRows['user3']['address']);
    }

    public function testActiveQuery()
    {
        $db = $this->getConnection();

        $query = Customer::find()->orderBy('id');
        $customers = [];
        foreach ($query->batch(2, $db) as $models) {
            $customers = array_merge($customers, $models);
        }
        $this->assertEquals(3, count($customers));
        $this->assertEquals('user1', $customers[0]->name);
        $this->assertEquals('user2', $customers[1]->name);
        $this->assertEquals('user3', $customers[2]->name);

        // batch with eager loading
        $query = Customer::find()->with('orders')->orderBy('id');
        $customers = [];
        foreach ($query->batch(2, $db) as $models) {
            $customers = array_merge($customers, $models);
            foreach ($models as $model) {
                $this->assertTrue($model->isRelationPopulated('orders'));
            }
        }
        $this->assertEquals(3, count($customers));
        $this->assertEquals(1, count($customers[0]->orders));
        $this->assertEquals(2, count($customers[1]->orders));
        $this->assertEquals(0, count($customers[2]->orders));
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use PDO;
use Yii;
use yii\db\Connection;
use yii\helpers\Console;
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

    public function pdoAttributesProvider()
    {
        return [
            [[
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ]],
            [[
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
            ]],
        ];
    }

    /**
     * @var int defaults to 10 Mio records
     */
    protected static $largeTableCount = 10000000;

    /**
     * @dataProvider pdoAttributesProvider
     */
    public function testBatchHugeTable($pdoAttrs)
    {
        $db = $this->getConnection(true, false);
        $this->assertFalse($db->isActive);
        $db->attributes = $pdoAttrs;
        $db->open();

        $this->ensureLargeTable($db);

        $query = (new Query)->from('customer_large');
        Console::startProgress($c = 0, static::$largeTableCount, 'Running batch() query... (Memory: ' . memory_get_usage() . ')');
        foreach($query->batch(100, $db) as $batch) {
            $this->assertTrue(is_array($batch));
            $this->assertArrayHasKey('email', reset($batch));
            $this->assertCount(100, $batch);
            $c += 100;
            Console::updateProgress($c, static::$largeTableCount, 'Running batch() query... (Memory: ' . memory_get_usage() . ')');
        }
        Console::endProgress(true, false);

        // peak memory should be less than 100MB
        $this->assertLessThan(100 * 1024 * 1024, memory_get_peak_usage());
        $memUsage = memory_get_peak_usage();
        echo "batch memory: $memUsage\n";

        $query = (new Query)->from('customer_large');
        Console::startProgress($c = 0, static::$largeTableCount, 'Running each() query... (Memory: ' . memory_get_usage() . ')');
        foreach($query->each(100, $db) as $customer) {
            $this->assertTrue(is_array($customer));
            $this->assertArrayHasKey('email', $customer);
            $c++;
            Console::updateProgress($c, static::$largeTableCount, 'Running each() query... (Memory: ' . memory_get_usage() . ')');
        }
        Console::endProgress(true, false);

        // peak memory should be less than 100MB
        $this->assertLessThan(100 * 1024 * 1024, memory_get_peak_usage());
        $memUsage = memory_get_peak_usage();
        echo "each() memory: $memUsage\n";

    }

    /**
     * @param Connection $db
     */
    protected function ensureLargeTable($db)
    {
        if ($db->getTableSchema('customer_large', true) === null) {
            $db->createCommand()->createTable('customer_large', [
                'id' => 'pk',
                'email' => 'string(128)',
                'name' => 'string(64)',
                'address' => 'string(128)',
                'status' => 'int NOT NULL',
            ])->execute();
        } elseif ((new Query)->from('customer_large')->count('*', $db) === static::$largeTableCount) {
            return;
        }

        // clean customer table
        $db->createCommand()->delete('customer_large')->execute();

        Console::startProgress($i = 0, static::$largeTableCount, 'Creating large table... (Memory: ' . memory_get_usage() . ')');
        for(; $i < static::$largeTableCount; $i += $j) {
            $batchRecords = [];
            for($j = 0; $j < 1000; ++$j) {
                $batchRecords[] = ["mail$i.$j@example.com", "customer$i.$j", "address$i.$j", 1];
            }
            $db->createCommand()->batchInsert('customer_large', ['email', 'name', 'address', 'status'], $batchRecords)->execute();
            Console::updateProgress($i, static::$largeTableCount, 'Creating large table... (Memory: ' . memory_get_usage() . ')');
        }
        Console::endProgress(true, false);
        $this->assertEquals($i, (new Query)->from('customer_large')->count('*', $db));
    }

    public function prepareDatabase($config, $fixture, $open = true)
    {
        if ($this->getName(false) === 'testBatchHugeTable') {
            // do not setup any fixtures
            return parent::prepareDatabase($config, null, $open);
        } else {
            return parent::prepareDatabase($config, $fixture, $open);
        }
    }
}

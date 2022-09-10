<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yii\db\Connection;
use yii\db\Transaction;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;

/**
 * @group db
 * @group sqlite
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    protected $driverName = 'sqlite';

    public function testConstruct()
    {
        $connection = $this->getConnection(false);
        $params = $this->database;

        $this->assertEquals($params['dsn'], $connection->dsn);
    }

    public function testQuoteValue()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals(123, $connection->quoteValue(123));
        $this->assertEquals("'string'", $connection->quoteValue('string'));
        $this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
    }

    public function testTransactionIsolation()
    {
        $connection = $this->getConnection(true);

        $transaction = $connection->beginTransaction(Transaction::READ_UNCOMMITTED);
        $transaction->rollBack();

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->rollBack();

        $this->assertTrue(true); // No exceptions means test is passed.
    }

    public function testMasterSlave()
    {
        $counts = [[0, 2], [1, 2], [2, 2]];

        foreach ($counts as $count) {
            list($masterCount, $slaveCount) = $count;

            $db = $this->prepareMasterSlave($masterCount, $slaveCount);

            $this->assertInstanceOf(Connection::className(), $db->getSlave());
            $this->assertTrue($db->getSlave()->isActive);
            $this->assertFalse($db->isActive);

            // test SELECT uses slave
            $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM profile')->queryScalar());
            $this->assertFalse($db->isActive);

            // test UPDATE uses master
            $db->createCommand("UPDATE profile SET description='test' WHERE id=1")->execute();
            $this->assertTrue($db->isActive);
            if ($masterCount > 0) {
                $this->assertInstanceOf(Connection::className(), $db->getMaster());
                $this->assertTrue($db->getMaster()->isActive);
            } else {
                $this->assertNull($db->getMaster());
            }
            $this->assertNotEquals('test', $db->createCommand('SELECT description FROM profile WHERE id=1')->queryScalar());
            $result = $db->useMaster(function (Connection $db) {
                return $db->createCommand('SELECT description FROM profile WHERE id=1')->queryScalar();
            });
            $this->assertEquals('test', $result);

            // test ActiveRecord read/write split
            ActiveRecord::$db = $db = $this->prepareMasterSlave($masterCount, $slaveCount);
            $this->assertFalse($db->isActive);

            $customer = Customer::findOne(1);
            $this->assertInstanceOf(Customer::className(), $customer);
            $this->assertEquals('user1', $customer->name);
            $this->assertFalse($db->isActive);

            $customer->name = 'test';
            $customer->save();
            $this->assertTrue($db->isActive);
            $customer = Customer::findOne(1);
            $this->assertInstanceOf(Customer::className(), $customer);
            $this->assertEquals('user1', $customer->name);
            $result = $db->useMaster(function () {
                return Customer::findOne(1)->name;
            });
            $this->assertEquals('test', $result);
        }
    }

    public function testMastersShuffled()
    {
        $mastersCount = 2;
        $slavesCount = 2;
        $retryPerNode = 10;

        $nodesCount = $mastersCount + $slavesCount;

        $hit_slaves = $hit_masters = [];

        for ($i = $nodesCount * $retryPerNode; $i-- > 0;) {
            $db = $this->prepareMasterSlave($mastersCount, $slavesCount);
            $db->shuffleMasters = true;

            $hit_slaves[$db->getSlave()->dsn] = true;
            $hit_masters[$db->getMaster()->dsn] = true;
            if (\count($hit_slaves) === $slavesCount && \count($hit_masters) === $mastersCount) {
                break;
            }
        }

        $this->assertCount($mastersCount, $hit_masters, 'all masters hit');
        $this->assertCount($slavesCount, $hit_slaves, 'all slaves hit');
    }

    public function testMastersSequential()
    {
        $mastersCount = 2;
        $slavesCount = 2;
        $retryPerNode = 10;

        $nodesCount = $mastersCount + $slavesCount;

        $hit_slaves = $hit_masters = [];

        for ($i = $nodesCount * $retryPerNode; $i-- > 0;) {
            $db = $this->prepareMasterSlave($mastersCount, $slavesCount);
            $db->shuffleMasters = false;

            $hit_slaves[$db->getSlave()->dsn] = true;
            $hit_masters[$db->getMaster()->dsn] = true;
            if (\count($hit_slaves) === $slavesCount) {
                break;
            }
        }

        $this->assertCount(1, $hit_masters, 'same master hit');
        // slaves are always random
        $this->assertCount($slavesCount, $hit_slaves, 'all slaves hit');
    }

    public function testRestoreMasterAfterException()
    {
        $db = $this->prepareMasterSlave(1, 1);
        $this->assertTrue($db->enableSlaves);
        try {
            $db->useMaster(function (Connection $db) {
                throw new \Exception('fail');
            });
            $this->fail('Exception was caught somewhere');
        } catch (\Exception $e) {
            // ok
        }
        $this->assertTrue($db->enableSlaves);
    }

    /**
     * @param int $masterCount
     * @param int $slaveCount
     * @return Connection
     */
    protected function prepareMasterSlave($masterCount, $slaveCount)
    {
        $databases = self::getParam('databases');
        $fixture = $databases[$this->driverName]['fixture'];
        $basePath = \Yii::getAlias('@yiiunit/runtime');

        $config = [
            'class' => 'yii\db\Connection',
            'dsn' => "sqlite:$basePath/yii2test.sq3",
        ];
        $this->prepareDatabase($config, $fixture)->close();

        for ($i = 0; $i < $masterCount; ++$i) {
            $master = ['dsn' => "sqlite:$basePath/yii2test_master{$i}.sq3"];
            $db = $this->prepareDatabase($master, $fixture);
            $db->close();
            $config['masters'][] = $master;
        }

        for ($i = 0; $i < $slaveCount; ++$i) {
            $slave = ['dsn' => "sqlite:$basePath/yii2test_slave{$i}.sq3"];
            $db = $this->prepareDatabase($slave, $fixture);
            $db->close();
            $config['slaves'][] = $slave;
        }

        return \Yii::createObject($config);
    }

    public function testAliasDbPath()
    {
        $config = [
            'dsn' => 'sqlite:@yiiunit/runtime/yii2aliastest.sq3',
        ];
        $connection = new Connection($config);
        $connection->open();
        $this->assertTrue($connection->isActive);
        $this->assertEquals($config['dsn'], $connection->dsn);

        $connection->close();
    }

    public function testExceptionContainsRawQuery()
    {
        $this->markTestSkipped('This test does not work on sqlite because preparing the failing query fails');
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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

    public function testPrimaryReplica()
    {
        $counts = [[0, 2], [1, 2], [2, 2]];

        foreach ($counts as $count) {
            list($primaryCount, $replicaCount) = $count;

            $db = $this->preparePrimaryReplica($primaryCount, $replicaCount);

            $this->assertInstanceOf(Connection::className(), $db->getReplica());
            $this->assertTrue($db->getReplica()->isActive);
            $this->assertFalse($db->isActive);

            // test SELECT uses replica
            $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM profile')->queryScalar());
            $this->assertFalse($db->isActive);

            // test UPDATE uses primary
            $db->createCommand("UPDATE profile SET description='test' WHERE id=1")->execute();
            $this->assertTrue($db->isActive);
            if ($primaryCount > 0) {
                $this->assertInstanceOf(Connection::className(), $db->getPrimary());
                $this->assertTrue($db->getPrimary()->isActive);
            } else {
                $this->assertNull($db->getPrimary());
            }
            $this->assertNotEquals('test', $db->createCommand('SELECT description FROM profile WHERE id=1')->queryScalar());
            $result = $db->usePrimary(function (Connection $db) {
                return $db->createCommand('SELECT description FROM profile WHERE id=1')->queryScalar();
            });
            $this->assertEquals('test', $result);

            // test ActiveRecord read/write split
            ActiveRecord::$db = $db = $this->preparePrimaryReplica($primaryCount, $replicaCount);
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
            $result = $db->usePrimary(function () {
                return Customer::findOne(1)->name;
            });
            $this->assertEquals('test', $result);
        }
    }

    public function testPrimariesShuffled()
    {
        $primariesCount = 2;
        $replicasCount = 2;
        $retryPerNode = 10;

        $nodesCount = $primariesCount + $replicasCount;

        $hit_replicas = $hit_primaries = [];

        for ($i = $nodesCount * $retryPerNode; $i-- > 0;) {
            $db = $this->preparePrimaryReplica($primariesCount, $replicasCount);
            $db->shufflePrimaries = true;

            $hit_replicas[$db->getReplica()->dsn] = true;
            $hit_primaries[$db->getPrimary()->dsn] = true;
            if (\count($hit_replicas) === $replicasCount && \count($hit_primaries) === $primariesCount) {
                break;
            }
        }

        $this->assertCount($primariesCount, $hit_primaries, 'all primaries hit');
        $this->assertCount($replicasCount, $hit_replicas, 'all replicas hit');
    }

    public function testPrimariesSequential()
    {
        $primariesCount = 2;
        $replicasCount = 2;
        $retryPerNode = 10;

        $nodesCount = $primariesCount + $replicasCount;

        $hit_replicas = $hit_primaries = [];

        for ($i = $nodesCount * $retryPerNode; $i-- > 0;) {
            $db = $this->preparePrimaryReplica($primariesCount, $replicasCount);
            $db->shufflePrimaries = false;

            $hit_replicas[$db->getReplica()->dsn] = true;
            $hit_primaries[$db->getPrimary()->dsn] = true;
            if (\count($hit_replicas) === $replicasCount) {
                break;
            }
        }

        $this->assertCount(1, $hit_primaries, 'same primary hit');
        // replicas are always random
        $this->assertCount($replicasCount, $hit_replicas, 'all replicas hit');
    }

    public function testRestorePrimaryAfterException()
    {
        $db = $this->preparePrimaryReplica(1, 1);
        $this->assertTrue($db->enableReplicas);
        try {
            $db->usePrimary(function (Connection $db) {
                throw new \Exception('fail');
            });
            $this->fail('Exception was caught somewhere');
        } catch (\Exception $e) {
            // ok
        }
        $this->assertTrue($db->enableReplicas);
    }

    /**
     * @param int $primaryCount
     * @param int $replicaCount
     * @return Connection
     */
    protected function preparePrimaryReplica($primaryCount, $replicaCount)
    {
        $databases = self::getParam('databases');
        $fixture = $databases[$this->driverName]['fixture'];
        $basePath = \Yii::getAlias('@yiiunit/runtime');

        $config = [
            'class' => 'yii\db\Connection',
            'dsn' => "sqlite:$basePath/yii2test.sq3",
        ];
        $this->prepareDatabase($config, $fixture)->close();

        for ($i = 0; $i < $primaryCount; ++$i) {
            $primary = ['dsn' => "sqlite:$basePath/yii2test_primary{$i}.sq3"];
            $db = $this->prepareDatabase($primary, $fixture);
            $db->close();
            $config['primaries'][] = $primary;
        }

        for ($i = 0; $i < $replicaCount; ++$i) {
            $replica = ['dsn' => "sqlite:$basePath/yii2test_replica{$i}.sq3"];
            $db = $this->prepareDatabase($replica, $fixture);
            $db->close();
            $config['replicas'][] = $replica;
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

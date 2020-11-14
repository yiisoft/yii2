<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\Connection;
use yii\db\Transaction;

/**
 * @group db
 * @group oci
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    protected $driverName = 'oci';

    public function testSerialize()
    {
        $connection = $this->getConnection(false, false);
        $connection->open();
        $serialized = serialize($connection);
        $unserialized = unserialize($serialized);
        $this->assertInstanceOf('yii\db\Connection', $unserialized);

        $this->assertEquals(123, $unserialized->createCommand('SELECT 123 FROM DUAL')->queryScalar());
    }

    public function testQuoteTableName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('"table"', $connection->quoteTableName('table'));
        $this->assertEquals('"table"', $connection->quoteTableName('"table"'));
        $this->assertEquals('"schema"."table"', $connection->quoteTableName('schema.table'));
        $this->assertEquals('"schema"."table"', $connection->quoteTableName('schema."table"'));
        $this->assertEquals('"schema"."table"', $connection->quoteTableName('"schema"."table"'));
        $this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertEquals('(table)', $connection->quoteTableName('(table)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('"column"', $connection->quoteColumnName('column'));
        $this->assertEquals('"column"', $connection->quoteColumnName('"column"'));
        $this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertEquals('(column)', $connection->quoteColumnName('(column)'));

        $this->assertEquals('"column"', $connection->quoteSql('[[column]]'));
        $this->assertEquals('"column"', $connection->quoteSql('{{column}}'));
    }

    public function testQuoteFullColumnName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('table.column'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('table."column"'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('"table".column'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('"table"."column"'));

        $this->assertEquals('[[table.column]]', $connection->quoteColumnName('[[table.column]]'));
        $this->assertEquals('{{table}}."column"', $connection->quoteColumnName('{{table}}.column'));
        $this->assertEquals('{{table}}."column"', $connection->quoteColumnName('{{table}}."column"'));
        $this->assertEquals('{{table}}.[[column]]', $connection->quoteColumnName('{{table}}.[[column]]'));
        $this->assertEquals('{{%table}}."column"', $connection->quoteColumnName('{{%table}}.column'));
        $this->assertEquals('{{%table}}."column"', $connection->quoteColumnName('{{%table}}."column"'));

        $this->assertEquals('"table"."column"', $connection->quoteSql('[[table.column]]'));
        $this->assertEquals('"table"."column"', $connection->quoteSql('{{table}}.[[column]]'));
        $this->assertEquals('"table"."column"', $connection->quoteSql('{{table}}."column"'));
        $this->assertEquals('"table"."column"', $connection->quoteSql('{{%table}}.[[column]]'));
        $this->assertEquals('"table"."column"', $connection->quoteSql('{{%table}}."column"'));
    }

    public function testTransactionIsolation()
    {
        $connection = $this->getConnection(true);

        $transaction = $connection->beginTransaction(Transaction::READ_COMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->commit();
    }

    public function testTransaction()
    {
        $connection = $this->getConnection(false);

        $this->assertNull($connection->transaction);

        $transaction = $connection->beginTransaction();

        $this->assertNotNull($connection->transaction);
        $this->assertTrue($transaction->isActive);

        $connection->createCommand()->insert('profile', ['description' => 'test transaction'])->execute();

        $transaction->rollBack();
        $this->assertFalse($transaction->isActive);
        $this->assertNull($connection->transaction);

        $this->assertEquals(0, $connection->createCommand(
            "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'"
        )->queryScalar());

        $transaction = $connection->beginTransaction();

        $connection->createCommand()->insert(
            'profile',
            ['description' => 'test transaction']
        )->execute();

        $transaction->commit();
        $this->assertFalse($transaction->isActive);
        $this->assertNull($connection->transaction);
    }

    public function testTransactionShortcutCorrect()
    {
        $connection = $this->getConnection(true);

        $result = $connection->transaction(function () use ($connection) {
            $connection->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            return true;
        });

        $this->assertTrue($result, 'transaction shortcut valid value should be returned from callback');

        $profilesCount = $connection->createCommand(
            "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction shortcut'"
        )->queryScalar();

        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

    /**
     * Note: The READ UNCOMMITTED isolation level allows dirty reads. Oracle Database doesn't use dirty reads, nor does
     * it even allow them.
     *
     * Change Transaction::READ_UNCOMMITTED => Transaction::READ_COMMITTED.
     */
    public function testTransactionShortcutCustom()
    {
        $connection = $this->getConnection(true);

        $result = $connection->transaction(static function (Connection $db) {
            $db->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            return true;
        }, Transaction::READ_COMMITTED);

        $this->assertTrue($result, 'transaction shortcut valid value should be returned from callback');

        $profilesCount = $connection->createCommand(
            "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction shortcut'"
        )->queryScalar();
        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

    public function testEnableQueryLog()
    {
        $connection = $this->getConnection();

        foreach (['qlog1', 'qlog2', 'qlog3', 'qlog4'] as $table) {
            if ($connection->getTableSchema($table, true) !== null) {
                $connection->createCommand()->dropTable($table)->execute();
            }
        }

        // profiling and logging
        $connection->enableLogging = true;
        $connection->enableProfiling = true;

        \Yii::getLogger()->messages = [];
        $connection->createCommand()->createTable('qlog1', ['id' => 'pk'])->execute();
        $this->assertCount(3, \Yii::getLogger()->messages);
        $this->assertNotNull($connection->getTableSchema('qlog1', true));

        \Yii::getLogger()->messages = [];
        $connection->createCommand('SELECT * FROM {{qlog1}}')->queryAll();
        $this->assertCount(3, \Yii::getLogger()->messages);

        // profiling only
        $connection->enableLogging = false;
        $connection->enableProfiling = true;

        \Yii::getLogger()->messages = [];
        $connection->createCommand()->createTable('qlog2', ['id' => 'pk'])->execute();
        $this->assertCount(2, \Yii::getLogger()->messages);
        $this->assertNotNull($connection->getTableSchema('qlog2', true));

        \Yii::getLogger()->messages = [];
        $connection->createCommand('SELECT * FROM {{qlog2}}')->queryAll();
        $this->assertCount(2, \Yii::getLogger()->messages);

        // logging only
        $connection->enableLogging = true;
        $connection->enableProfiling = false;

        \Yii::getLogger()->messages = [];
        $connection->createCommand()->createTable('qlog3', ['id' => 'pk'])->execute();
        $this->assertCount(1, \Yii::getLogger()->messages);
        $this->assertNotNull($connection->getTableSchema('qlog3', true));

        \Yii::getLogger()->messages = [];
        $connection->createCommand('SELECT * FROM {{qlog3}}')->queryAll();
        $this->assertCount(1, \Yii::getLogger()->messages);

        // disabled
        $connection->enableLogging = false;
        $connection->enableProfiling = false;

        \Yii::getLogger()->messages = [];
        $connection->createCommand()->createTable('qlog4', ['id' => 'pk'])->execute();
        $this->assertNotNull($connection->getTableSchema('qlog4', true));
        $this->assertCount(0, \Yii::getLogger()->messages);
        $connection->createCommand('SELECT * FROM {{qlog4}}')->queryAll();
        $this->assertCount(0, \Yii::getLogger()->messages);
    }
}

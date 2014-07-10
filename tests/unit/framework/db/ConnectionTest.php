<?php

namespace yiiunit\framework\db;

use yii\db\Connection;
use yii\db\Transaction;

/**
 * @group db
 * @group mysql
 */
class ConnectionTest extends DatabaseTestCase
{
    public function testConstruct()
    {
        $connection = $this->getConnection(false);
        $params = $this->database;

        $this->assertEquals($params['dsn'], $connection->dsn);
        $this->assertEquals($params['username'], $connection->username);
        $this->assertEquals($params['password'], $connection->password);
    }

    public function testOpenClose()
    {
        $connection = $this->getConnection(false, false);

        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->pdo);

        $connection->open();
        $this->assertTrue($connection->isActive);
        $this->assertTrue($connection->pdo instanceof \PDO);

        $connection->close();
        $this->assertFalse($connection->isActive);
        $this->assertEquals(null, $connection->pdo);

        $connection = new Connection;
        $connection->dsn = 'unknown::memory:';
        $this->setExpectedException('yii\db\Exception');
        $connection->open();
    }

    public function testGetDriverName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertEquals($this->driverName, $connection->driverName);
    }

    public function testQuoteValue()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals(123, $connection->quoteValue(123));
        $this->assertEquals("'string'", $connection->quoteValue('string'));
        $this->assertEquals("'It\\'s interesting'", $connection->quoteValue("It's interesting"));
    }

    public function testQuoteTableName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('`table`', $connection->quoteTableName('table'));
        $this->assertEquals('`table`', $connection->quoteTableName('`table`'));
        $this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.table'));
        $this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.`table`'));
        $this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertEquals('(table)', $connection->quoteTableName('(table)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals('`column`', $connection->quoteColumnName('column'));
        $this->assertEquals('`column`', $connection->quoteColumnName('`column`'));
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.column'));
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.`column`'));
        $this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertEquals('(column)', $connection->quoteColumnName('(column)'));
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

        $this->assertEquals(0, $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction';")->queryScalar());

        $transaction = $connection->beginTransaction();
        $connection->createCommand()->insert('profile', ['description' => 'test transaction'])->execute();
        $transaction->commit();
        $this->assertFalse($transaction->isActive);
        $this->assertNull($connection->transaction);

        $this->assertEquals(1, $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction';")->queryScalar());
    }

    public function testTransactionIsolation()
    {
        $connection = $this->getConnection(true);

        $transaction = $connection->beginTransaction(Transaction::READ_UNCOMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction(Transaction::READ_COMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction(Transaction::REPEATABLE_READ);
        $transaction->commit();

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->commit();
    }

    /**
     * @expectedException \Exception
     */
    public function testTransactionShortcutException()
    {
        $connection = $this->getConnection(true);
        $connection->transaction(function () use ($connection) {
            $connection->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            throw new \Exception('Exception in transaction shortcut');
        });

        $profilesCount = $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction shortcut';")->queryScalar();
        $this->assertEquals(0, $profilesCount, 'profile should not be inserted in transaction shortcut');
    }

    public function testTransactionShortcutCorrect()
    {
        $connection = $this->getConnection(true);

        $result = $connection->transaction(function () use ($connection) {
            $connection->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            return true;
        });

        $this->assertTrue($result, 'transaction shortcut valid value should be returned from callback');

        $profilesCount = $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction shortcut';")->queryScalar();
        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

    public function testTransactionShortcutCustom()
    {
        $connection = $this->getConnection(true);

        $result = $connection->transaction(function () use ($connection) {
            $connection->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            return true;
        }, Transaction::READ_UNCOMMITTED);

        $this->assertTrue($result, 'transaction shortcut valid value should be returned from callback');

        $profilesCount = $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction shortcut';")->queryScalar();
        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

}

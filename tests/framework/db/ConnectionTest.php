<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\db\conditions\AndCondition;
use yii\db\conditions\ExistsConditionBuilder;
use yii\db\conditions\OrCondition;
use yii\db\Connection;
use yii\db\Transaction;

abstract class ConnectionTest extends DatabaseTestCase
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
        $this->assertNull($connection->pdo);

        $connection->open();
        $this->assertTrue($connection->isActive);
        $this->assertInstanceOf('\\PDO', $connection->pdo);

        $connection->close();
        $this->assertFalse($connection->isActive);
        $this->assertNull($connection->pdo);

        $connection = new Connection();
        $connection->dsn = 'unknown::memory:';
        $this->expectException('yii\db\Exception');
        $connection->open();
    }

    public function testQueryBuilderConfigurationAfterOpenClose()
    {
        $connection = $this->getConnection(false, false);
        $connection->setQueryBuilder([
            'expressionBuilders' => [
                // Just a dumb mapping to make sure it's applied
                'yii\db\conditions\OrCondition' => 'yii\db\conditions\ExistsConditionBuilder'
            ],
        ]);
        // Second call to make sure that consecutive calls are handled correctly
        $connection->setQueryBuilder([
            'expressionBuilders' => [
                'yii\db\conditions\AndCondition' => 'yii\db\conditions\InConditionBuilder'
            ],
        ]);

        $orCondition = new OrCondition(['dumb']);
        $andCondition = new AndCondition(['dumb']);

        $connection->open();

        $this->assertInstanceOf(
            '\yii\db\conditions\ExistsConditionBuilder',
            $connection->getQueryBuilder()->getExpressionBuilder($orCondition)
        );
        $this->assertInstanceOf(
            '\yii\db\conditions\InConditionBuilder',
            $connection->getQueryBuilder()->getExpressionBuilder($andCondition)
        );

        $connection->close();
        $this->assertNull($connection->pdo);
        $connection->open();

        $this->assertInstanceOf(
            '\yii\db\conditions\ExistsConditionBuilder',
            $connection->getQueryBuilder()->getExpressionBuilder($orCondition)
        );
        $this->assertInstanceOf(
            '\yii\db\conditions\InConditionBuilder',
            $connection->getQueryBuilder()->getExpressionBuilder($andCondition)
        );
    }

    public function testSerialize()
    {
        $connection = $this->getConnection(false, false);
        $connection->open();
        $serialized = serialize($connection);

        $this->assertNotNull($connection->pdo);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf('yii\db\Connection', $unserialized);
        $this->assertNull($unserialized->pdo);

        $this->assertEquals(123, $unserialized->createCommand('SELECT 123')->queryScalar());
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
        $connection = $this->getConnection(false, false);
        $this->assertEquals('`table`', $connection->quoteTableName('table'));
        $this->assertEquals('`table`', $connection->quoteTableName('`table`'));
        $this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.table'));
        $this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.`table`'));
        $this->assertEquals('`schema`.`table`', $connection->quoteTableName('`schema`.`table`'));
        $this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertEquals('(table)', $connection->quoteTableName('(table)'));
        $this->assertEquals('`table(0)`', $connection->quoteTableName('table(0)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertEquals('`column`', $connection->quoteColumnName('column'));
        $this->assertEquals('`column`', $connection->quoteColumnName('`column`'));
        $this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertEquals('(column)', $connection->quoteColumnName('(column)'));

        $this->assertEquals('`column`', $connection->quoteSql('[[column]]'));
        $this->assertEquals('`column`', $connection->quoteSql('{{column}}'));
    }

    public function testQuoteFullColumnName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.column'));
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.`column`'));
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('`table`.column'));
        $this->assertEquals('`table`.`column`', $connection->quoteColumnName('`table`.`column`'));

        $this->assertEquals('[[table.column]]', $connection->quoteColumnName('[[table.column]]'));
        $this->assertEquals('{{table}}.`column`', $connection->quoteColumnName('{{table}}.column'));
        $this->assertEquals('{{table}}.`column`', $connection->quoteColumnName('{{table}}.`column`'));
        $this->assertEquals('{{table}}.[[column]]', $connection->quoteColumnName('{{table}}.[[column]]'));
        $this->assertEquals('{{%table}}.`column`', $connection->quoteColumnName('{{%table}}.column'));
        $this->assertEquals('{{%table}}.`column`', $connection->quoteColumnName('{{%table}}.`column`'));

        $this->assertEquals('`table`.`column`', $connection->quoteSql('[[table.column]]'));
        $this->assertEquals('`table`.`column`', $connection->quoteSql('{{table}}.[[column]]'));
        $this->assertEquals('`table`.`column`', $connection->quoteSql('{{table}}.`column`'));
        $this->assertEquals('`table`.`column`', $connection->quoteSql('{{%table}}.[[column]]'));
        $this->assertEquals('`table`.`column`', $connection->quoteSql('{{%table}}.`column`'));
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

        $this->assertEquals(
            0,
            $connection->createCommand(
                "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'"
            )->queryScalar()
        );

        $transaction = $connection->beginTransaction();
        $connection->createCommand()->insert('profile', ['description' => 'test transaction'])->execute();
        $transaction->commit();
        $this->assertFalse($transaction->isActive);
        $this->assertNull($connection->transaction);

        $this->assertEquals(
            1,
            $connection->createCommand(
                "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'"
            )->queryScalar()
        );
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

        $this->assertTrue(true); // should not be any exception so far
    }

    public function testTransactionShortcutException()
    {
        $connection = $this->getConnection(true);

        $this->expectException(\Exception::class);

        $connection->transaction(function () use ($connection) {
            $connection->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            throw new \Exception('Exception in transaction shortcut');
        });

        $profilesCount = $connection
            ->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction shortcut';")
            ->queryScalar();
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

        $profilesCount = $connection->createCommand(
            "SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction shortcut'"
        )->queryScalar();

        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

    public function testTransactionShortcutCustom()
    {
        $connection = $this->getConnection(true);

        $result = $connection->transaction(function (Connection $db) {
            $db->createCommand()->insert('profile', ['description' => 'test transaction shortcut'])->execute();
            return true;
        }, Transaction::READ_UNCOMMITTED);

        $this->assertTrue($result, 'transaction shortcut valid value should be returned from callback');

        $profilesCount = $connection->createCommand("SELECT COUNT(*) FROM profile WHERE description = 'test transaction shortcut';")->queryScalar();
        $this->assertEquals(1, $profilesCount, 'profile should be inserted in transaction shortcut');
    }

    /**
     * Tests nested transactions with partial rollback.
     * @see https://github.com/yiisoft/yii2/issues/9851
     */
    public function testNestedTransaction()
    {
        /** @var Connection $connection */
        $connection = $this->getConnection(true);
        $connection->transaction(function (Connection $db) {
            $this->assertNotNull($db->transaction);
            $db->transaction(function (Connection $db) {
                $this->assertNotNull($db->transaction);
                $db->transaction->rollBack();
            });
            $this->assertNotNull($db->transaction);
        });
    }

    public function testNestedTransactionNotSupported()
    {
        $connection = $this->getConnection();
        $connection->enableSavepoint = false;
        $connection->transaction(function (Connection $db) {
            $this->assertNotNull($db->transaction);
            $this->expectException('yii\base\NotSupportedException');
            $db->beginTransaction();
        });
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

    public function testExceptionContainsRawQuery()
    {
        $connection = $this->getConnection();
        if ($connection->getTableSchema('qlog1', true) === null) {
            $connection->createCommand()->createTable('qlog1', ['id' => 'pk'])->execute();
        }
        $connection->emulatePrepare = true;

        // profiling and logging
        $connection->enableLogging = true;
        $connection->enableProfiling = true;
        $this->runExceptionTest($connection);


        // profiling only
        $connection->enableLogging = false;
        $connection->enableProfiling = true;
        $this->runExceptionTest($connection);

        // logging only
        $connection->enableLogging = true;
        $connection->enableProfiling = false;
        $this->runExceptionTest($connection);

        // disabled
        $connection->enableLogging = false;
        $connection->enableProfiling = false;
        $this->runExceptionTest($connection);
    }

    /**
     * @param Connection $connection
     */
    private function runExceptionTest($connection)
    {
        $thrown = false;
        try {
            $connection->createCommand('INSERT INTO qlog1(a) VALUES(:a);', [':a' => 1])->execute();
        } catch (\yii\db\Exception $e) {
            $this->assertStringContainsString(
                'INSERT INTO qlog1(a) VALUES(1);',
                $e->getMessage(),
                'Exception message should contain raw SQL query: ' . (string) $e
            );
            $thrown = true;
        }
        $this->assertTrue($thrown, 'An exception should have been thrown by the command.');

        $thrown = false;
        try {
            $connection->createCommand('SELECT * FROM qlog1 WHERE id=:a ORDER BY nonexistingcolumn;', [':a' => 1])->queryAll();
        } catch (\yii\db\Exception $e) {
            $this->assertStringContainsString(
                'SELECT * FROM qlog1 WHERE id=1 ORDER BY nonexistingcolumn;',
                $e->getMessage(), 'Exception message should contain raw SQL query: ' . (string) $e,
            );
            $thrown = true;
        }
        $this->assertTrue($thrown, 'An exception should have been thrown by the command.');
    }

    /**
     * Ensure database connection is reset on when a connection is cloned.
     * Make sure each connection element has its own PDO instance i.e. own connection to the DB.
     * Also transaction elements should not be shared between two connections.
     */
    public function testClone()
    {
        $connection = $this->getConnection(true, false);
        $this->assertNull($connection->transaction);
        $this->assertNull($connection->pdo);
        $connection->open();
        $this->assertNull($connection->transaction);
        $this->assertNotNull($connection->pdo);

        $conn2 = clone $connection;
        $this->assertNull($connection->transaction);
        $this->assertNotNull($connection->pdo);

        $this->assertNull($conn2->transaction);
        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            $this->assertNotNull($conn2->pdo);
        } else {
            $this->assertNull($conn2->pdo);
        }

        $connection->beginTransaction();

        $this->assertNotNull($connection->transaction);
        $this->assertNotNull($connection->pdo);

        $this->assertNull($conn2->transaction);
        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            $this->assertNotNull($conn2->pdo);
        } else {
            $this->assertNull($conn2->pdo);
        }

        $conn3 = clone $connection;

        $this->assertNotNull($connection->transaction);
        $this->assertNotNull($connection->pdo);
        $this->assertNull($conn3->transaction);
        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            $this->assertNotNull($conn3->pdo);
        } else {
            $this->assertNull($conn3->pdo);
        }
    }


    /**
     * Test whether slave connection is recovered when call getSlavePdo(true) after close().
     *
     * @see https://github.com/yiisoft/yii2/issues/14165
     */
    public function testGetPdoAfterClose()
    {
        $connection = $this->getConnection();
        $connection->slaves[] = [
            'dsn' => $connection->dsn,
            'username' => $connection->username,
            'password' => $connection->password,
        ];
        $this->assertNotNull($connection->getSlavePdo(false));
        $connection->close();

        $masterPdo = $connection->getMasterPdo();
        $this->assertNotFalse($masterPdo);
        $this->assertNotNull($masterPdo);

        $slavePdo = $connection->getSlavePdo(false);
        $this->assertNotFalse($slavePdo);
        $this->assertNotNull($slavePdo);
        $this->assertNotSame($masterPdo, $slavePdo);
    }

    public function testServerStatusCacheWorks()
    {
        $cache = new ArrayCache();
        Yii::$app->set('cache', $cache);

        $connection = $this->getConnection(true, false);
        $connection->masters[] = [
            'dsn' => $connection->dsn,
            'username' => $connection->username,
            'password' => $connection->password,
        ];
        $connection->shuffleMasters = false;

        $cacheKey = ['yii\db\Connection::openFromPoolSequentially', $connection->dsn];

        $this->assertFalse($cache->exists($cacheKey));
        $connection->open();
        $this->assertFalse($cache->exists($cacheKey), 'Connection was successful – cache must not contain information about this DSN');
        $connection->close();

        $cacheKey = ['yii\db\Connection::openFromPoolSequentially', 'host:invalid'];
        $connection->masters[0]['dsn'] = 'host:invalid';
        try {
            $connection->open();
        } catch (InvalidConfigException $e) {
        }
        $this->assertTrue($cache->exists($cacheKey), 'Connection was not successful – cache must contain information about this DSN');
        $connection->close();
    }

    public function testServerStatusCacheCanBeDisabled()
    {
        $cache = new ArrayCache();
        Yii::$app->set('cache', $cache);

        $connection = $this->getConnection(true, false);
        $connection->masters[] = [
            'dsn' => $connection->dsn,
            'username' => $connection->username,
            'password' => $connection->password,
        ];
        $connection->shuffleMasters = false;
        $connection->serverStatusCache = false;

        $cacheKey = ['yii\db\Connection::openFromPoolSequentially', $connection->dsn];

        $this->assertFalse($cache->exists($cacheKey));
        $connection->open();
        $this->assertFalse($cache->exists($cacheKey), 'Caching is disabled');
        $connection->close();

        $cacheKey = ['yii\db\Connection::openFromPoolSequentially', 'host:invalid'];
        $connection->masters[0]['dsn'] = 'host:invalid';
        try {
            $connection->open();
        } catch (InvalidConfigException $e) {
        }
        $this->assertFalse($cache->exists($cacheKey), 'Caching is disabled');
        $connection->close();
    }
}

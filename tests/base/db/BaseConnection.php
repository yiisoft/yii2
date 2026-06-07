<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use yii\db\Exception;
use yiiunit\framework\db\DatabaseTestCase;
use PDO;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\caching\ArrayCache;
use yii\db\conditions\AndCondition;
use yii\db\conditions\ExistsConditionBuilder;
use yii\db\conditions\InConditionBuilder;
use yii\db\conditions\OrCondition;
use yii\db\Connection;
use yii\db\Transaction;

use function serialize;
use function unserialize;

/**
 * Base unit tests for {@see Connection} lifecycle, quoting, transactions, and server status cache across all database
 * drivers.
 */
abstract class BaseConnection extends DatabaseTestCase
{
    public function testConstruct(): void
    {
        $db = $this->getConnection(false);

        $params = $this->database;

        self::assertSame(
            $params['dsn'],
            $db->dsn,
            "'dsn' does not match the configuration.",
        );
        self::assertSame(
            $params['username'],
            $db->username,
            "'username' does not match the configuration.",
        );
        self::assertSame(
            $params['password'],
            $db->password,
            "'password' does not match the configuration.",
        );
    }

    public function testOpenClose(): void
    {
        $db = $this->getConnection(false, false);

        self::assertFalse(
            $db->isActive,
            'Connection should be inactive before open.',
        );
        self::assertNull(
            $db->pdo,
            "PDO instance should be 'null' before open.",
        );

        $db->open();

        self::assertTrue(
            $db->isActive,
            'Connection should be active after open.',
        );
        self::assertInstanceOf(
            PDO::class,
            $db->pdo,
            'PDO instance should be created after open.',
        );

        $db->close();

        self::assertFalse(
            $db->isActive,
            'Connection should be inactive after close.',
        );
        self::assertNull(
            $db->pdo,
            "PDO instance should be 'null' after close.",
        );

        $db = new Connection();

        $db->dsn = 'unknown::memory:';

        $this->expectException(Exception::class);

        $db->open();
    }

    public function testQueryBuilderConfigurationAfterOpenClose(): void
    {
        $db = $this->getConnection(false, false);

        $db->setQueryBuilder(
            [
                'expressionBuilders' => [
                    // Just a dumb mapping to make sure it's applied.
                    OrCondition::class => ExistsConditionBuilder::class,
                ],
            ],
        );

        // Second call to make sure that consecutive calls are handled correctly.
        $db->setQueryBuilder(
            [
                'expressionBuilders' => [
                    AndCondition::class => InConditionBuilder::class,
                ],
            ],
        );

        $orCondition = new OrCondition(['dumb']);
        $andCondition = new AndCondition(['dumb']);

        $db->open();

        self::assertInstanceOf(
            ExistsConditionBuilder::class,
            $db->getQueryBuilder()->getExpressionBuilder($orCondition),
            "Custom builder for 'OrCondition' should be applied after open.",
        );
        self::assertInstanceOf(
            InConditionBuilder::class,
            $db->getQueryBuilder()->getExpressionBuilder($andCondition),
            "Custom builder for 'AndCondition' should be applied after open.",
        );

        $db->close();

        self::assertNull(
            $db->pdo,
            "PDO instance should be 'null' after close.",
        );

        $db->open();

        self::assertInstanceOf(
            ExistsConditionBuilder::class,
            $db->getQueryBuilder()->getExpressionBuilder($orCondition),
            "Custom builder for 'OrCondition' should survive reopen.",
        );
        self::assertInstanceOf(
            InConditionBuilder::class,
            $db->getQueryBuilder()->getExpressionBuilder($andCondition),
            "Custom builder for 'AndCondition' should survive reopen.",
        );
    }

    public function testSerialize(): void
    {
        $db = $this->getConnection(false, false);

        $db->open();

        $serialized = serialize($db);

        self::assertNotNull(
            $db->pdo,
            'PDO instance should be set after open.',
        );

        $unserialized = unserialize($serialized);

        self::assertInstanceOf(
            Connection::class,
            $unserialized,
            'Unserialized value should be a connection instance.',
        );
        self::assertNull(
            $unserialized->pdo,
            'PDO instance should not survive serialization.',
        );
        self::assertEquals(
            123,
            $unserialized->createCommand(
                <<<SQL
                SELECT 123
                SQL
            )->queryScalar(),
            'Unserialized connection should execute queries.',
        );
    }

    public function testGetDriverName(): void
    {
        $db = $this->getConnection(false, false);

        self::assertEquals(
            $this->driverName,
            $db->driverName,
            "'driverName' does not match the configured driver.",
        );
    }

    public function testQuoteValue(): void
    {
        $db = $this->getConnection(false);

        self::assertSame(
            123,
            $db->quoteValue(123),
            'Integer value should not be quoted.',
        );
        self::assertSame(
            "'string'",
            $db->quoteValue('string'),
            'String value should be quoted.',
        );
        self::assertSame(
            "'It\\'s interesting'",
            $db->quoteValue("It's interesting"),
            'Single quote should be escaped.',
        );
    }

    public function testQuoteTableName(): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            '`table`',
            $db->quoteTableName('table'),
            "Quoting of '`table`' does not match.",
        );
        self::assertSame(
            '`table`',
            $db->quoteTableName('`table`'),
            "Quoting of '`table`' does not match.",
        );
        self::assertSame(
            '`schema`.`table`',
            $db->quoteTableName('schema.table'),
            "Quoting of '`schema`.`table`' does not match.",
        );
        self::assertSame(
            '`schema`.`table`',
            $db->quoteTableName('schema.`table`'),
            "Quoting of '`schema`.`table`' does not match.",
        );
        self::assertSame(
            '`schema`.`table`',
            $db->quoteTableName('`schema`.`table`'),
            "Quoting of '`schema`.`table`' does not match.",
        );
        self::assertSame(
            '{{table}}',
            $db->quoteTableName('{{table}}'),
            "Quoting of '{{table}}' does not match.",
        );
        self::assertSame(
            '(table)',
            $db->quoteTableName('(table)'),
            "Quoting of '(table)' does not match.",
        );
        self::assertSame(
            '`table(0)`',
            $db->quoteTableName('table(0)'),
            "Quoting of '`table(0)`' does not match.",
        );
    }

    public function testQuoteColumnName(): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            '`column`',
            $db->quoteColumnName('column'),
            "Quoting of '`column`' does not match.",
        );
        self::assertSame(
            '`column`',
            $db->quoteColumnName('`column`'),
            "Quoting of '`column`' does not match.",
        );
        self::assertSame(
            '[[column]]',
            $db->quoteColumnName('[[column]]'),
            "Quoting of '[[column]]' does not match.",
        );
        self::assertSame(
            '{{column}}',
            $db->quoteColumnName('{{column}}'),
            "Quoting of '{{column}}' does not match.",
        );
        self::assertSame(
            '(column)',
            $db->quoteColumnName('(column)'),
            "Quoting of '(column)' does not match.",
        );
        self::assertSame(
            '`column`',
            $db->quoteSql('[[column]]'),
            "Quoted SQL for '[[column]]' does not match.",
        );
        self::assertSame(
            '`column`',
            $db->quoteSql('{{column}}'),
            "Quoted SQL for '{{column}}' does not match.",
        );
    }

    public function testQuoteFullColumnName(): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            '`table`.`column`',
            $db->quoteColumnName('table.column'),
            "Quoting of '`table`.`column`' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteColumnName('table.`column`'),
            "Quoting of '`table`.`column`' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteColumnName('`table`.column'),
            "Quoting of '`table`.`column`' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteColumnName('`table`.`column`'),
            "Quoting of '`table`.`column`' does not match.",
        );
        self::assertSame(
            '[[table.column]]',
            $db->quoteColumnName('[[table.column]]'),
            "Quoting of '[[table.column]]' does not match.",
        );
        self::assertSame(
            '{{table}}.`column`',
            $db->quoteColumnName('{{table}}.column'),
            "Quoting of '{{table}}.`column`' does not match.",
        );
        self::assertSame(
            '{{table}}.`column`',
            $db->quoteColumnName('{{table}}.`column`'),
            "Quoting of '{{table}}.`column`' does not match.",
        );
        self::assertSame(
            '{{table}}.[[column]]',
            $db->quoteColumnName('{{table}}.[[column]]'),
            "Quoting of '{{table}}.[[column]]' does not match.",
        );
        self::assertSame(
            '{{%table}}.`column`',
            $db->quoteColumnName('{{%table}}.column'),
            "Quoting of '{{%table}}.`column`' does not match.",
        );
        self::assertSame(
            '{{%table}}.`column`',
            $db->quoteColumnName('{{%table}}.`column`'),
            "Quoting of '{{%table}}.`column`' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteSql('[[table.column]]'),
            "Quoted SQL for '[[table.column]]' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteSql('{{table}}.[[column]]'),
            "Quoted SQL for '{{table}}.[[column]]' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteSql('{{table}}.`column`'),
            "Quoted SQL for '{{table}}.`column`' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteSql('{{%table}}.[[column]]'),
            "Quoted SQL for '{{%table}}.[[column]]' does not match.",
        );
        self::assertSame(
            '`table`.`column`',
            $db->quoteSql('{{%table}}.`column`'),
            "Quoted SQL for '{{%table}}.`column`' does not match.",
        );
    }

    public function testTransaction(): void
    {
        $db = $this->getConnection(false);

        self::assertNull(
            $db->transaction,
            'No active transaction before begin.',
        );

        $transaction = $db->beginTransaction();

        self::assertNotNull(
            $db->transaction,
            'Active transaction should be exposed after begin.',
        );
        self::assertTrue(
            $transaction->isActive,
            'Transaction should be active after begin.',
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction'],
        )->execute();

        $transaction->rollBack();

        self::assertFalse(
            $transaction->isActive,
            'Transaction should be inactive after rollback.',
        );
        self::assertNull(
            $db->transaction,
            'No active transaction after rollback.',
        );
        self::assertEquals(
            0,
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'
                SQL
            )->queryScalar(),
            'Rolled back row should not be persisted.',
        );

        $transaction = $db->beginTransaction();
        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction'],
        )->execute();
        $transaction->commit();

        self::assertFalse(
            $transaction->isActive,
            'Transaction should be inactive after commit.',
        );
        self::assertNull(
            $db->transaction,
            'No active transaction after commit.',
        );
        self::assertEquals(
            1,
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'
                SQL
            )->queryScalar(),
            'Committed row should be persisted.',
        );
    }

    public function testCommitTransactionsWithSavepoints(): void
    {
        $db = $this->getConnection(true);

        $transaction = $db->beginTransaction();

        self::assertSame(
            1,
            $transaction->level,
            'Transaction level should be `1` after outer begin.',
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction1'],
        )->execute();
        $transaction->begin();

        self::assertSame(
            2,
            $transaction->level,
            "Transaction level should be '2' after nested begin.",
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction2'],
        )->execute();
        $transaction->commit();

        self::assertSame(
            1,
            $transaction->level,
            'Transaction level should be `1` after nested commit.',
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction3'],
        )->execute();
        $transaction->commit();

        self::assertSame(
            0,
            $transaction->level,
            "Transaction level should be '0' after outer commit.",
        );
        self::assertFalse(
            $transaction->isActive,
            'Transaction should be inactive after outer commit.',
        );
        self::assertNull(
            $db->transaction,
            'No active transaction after outer commit.',
        );
        self::assertEquals(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction1'
                SQL
            )->queryScalar(),
            "Row 'test transaction1' should be persisted.",
        );
        self::assertEquals(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction2'
                SQL
            )->queryScalar(),
            "Row 'test transaction2' should be persisted.",
        );
        self::assertEquals(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction3'
                SQL
            )->queryScalar(),
            "Row 'test transaction3' should be persisted.",
        );
    }

    public function testPartialRollbackTransactionsWithSavepoints(): void
    {
        $db = $this->getConnection(true);

        $transaction = $db->beginTransaction();

        self::assertSame(
            1,
            $transaction->level,
            "Transaction level should be '1' after outer begin.",
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction1'],
        )->execute();
        $transaction->begin();

        self::assertSame(
            2,
            $transaction->level,
            "Transaction level should be '2' after nested begin.",
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction2'],
        )->execute();
        $transaction->rollBack();

        self::assertSame(
            1,
            $transaction->level,
            "Transaction level should be '1' after nested rollback.",
        );
        self::assertTrue(
            $transaction->isActive,
            'Outer transaction should stay active after nested rollback.',
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction3'],
        )->execute();
        $transaction->commit();

        self::assertSame(
            0,
            $transaction->level,
            "Transaction level should be '0' after outer commit.",
        );
        self::assertFalse(
            $transaction->isActive,
            'Transaction should be inactive after outer commit.',
        );
        self::assertNull(
            $db->transaction,
            'No active transaction after outer commit.',
        );
        self::assertEquals(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction1'
                SQL
            )->queryScalar(),
            "Row 'test transaction1' should be persisted.",
        );
        self::assertEquals(
            '0',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction2'
                SQL
            )->queryScalar(),
            "Row 'test transaction2' should be rolled back.",
        );
        self::assertEquals(
            '1',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction3'
                SQL
            )->queryScalar(),
            "Row 'test transaction3' should be persisted.",
        );
    }

    public function testRollbackTransactionsWithSavepoints(): void
    {
        $db = $this->getConnection(true);

        $transaction = $db->beginTransaction();

        self::assertSame(
            1,
            $transaction->level,
            "Transaction level should be '1' after outer begin.",
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction'],
        )->execute();
        $transaction->begin();

        self::assertSame(
            2,
            $transaction->level,
            "Transaction level should be '2' after nested begin.",
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction'],
        )->execute();
        $transaction->rollBack();

        self::assertSame(
            1,
            $transaction->level,
            "Transaction level should be '1' after nested rollback.",
        );
        self::assertTrue(
            $transaction->isActive,
            'Outer transaction should stay active after nested rollback.',
        );

        $db->createCommand()->insert(
            'profile',
            ['description' => 'test transaction'],
        )->execute();
        $transaction->rollBack();

        self::assertSame(
            0,
            $transaction->level,
            "Transaction level should be '0' after outer rollback.",
        );
        self::assertFalse(
            $transaction->isActive,
            'Transaction should be inactive after outer rollback.',
        );
        self::assertNull(
            $db->transaction,
            'No active transaction after outer rollback.',
        );
        self::assertEquals(
            '0',
            $db->createCommand(
                <<<SQL
                SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction'
                SQL
            )->queryScalar(),
            'No rows should be persisted after full rollback.',
        );
    }

    public function testThrowExceptionWhenCommitOnInactiveTransaction(): void
    {
        $db = $this->getConnection();

        $transaction = $db->beginTransaction();
        $db->close();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to commit transaction: transaction was inactive.');

        $transaction->commit();
    }

    public function testTransactionRollbackNotActiveTransaction(): void
    {
        $db = $this->getConnection();

        $transaction = $db->beginTransaction();
        $db->close();

        $level = $transaction->level;

        $transaction->rollBack();

        self::assertSame(
            $level,
            $transaction->level,
            'Transaction level should stay unchanged.',
        );
    }

    public function testThrowExceptionWhenSetIsolationLevelOnInactiveTransaction(): void
    {
        $db = $this->getConnection();

        $transaction = $db->beginTransaction();
        $db->close();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Failed to set isolation level: transaction was inactive.',
        );

        $transaction->setIsolationLevel(Transaction::SERIALIZABLE);
    }

    public function testTransactionIsolation(): void
    {
        $db = $this->getConnection(true);

        $transaction = $db->beginTransaction(Transaction::READ_UNCOMMITTED);
        $transaction->commit();

        $transaction = $db->beginTransaction(Transaction::READ_COMMITTED);
        $transaction->commit();

        $transaction = $db->beginTransaction(Transaction::REPEATABLE_READ);
        $transaction->commit();

        $transaction = $db->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->commit();

        self::assertTrue(
            true,
            'All isolation levels should be accepted without exception.',
        );
    }

    public function testThrowExceptionWhenTransactionShortcutCallbackThrows(): void
    {
        $db = $this->getConnection(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Exception in transaction shortcut',
        );

        $db->transaction(
            static function () use ($db): never {
                $db->createCommand()->insert(
                    'profile',
                    ['description' => 'test transaction shortcut'],
                )->execute();

                throw new Exception(
                    'Exception in transaction shortcut',
                );
            },
        );
    }

    public function testTransactionShortcutCorrect(): void
    {
        $db = $this->getConnection(true);

        $result = $db->transaction(
            static function () use ($db): bool {
                $db->createCommand()->insert(
                    'profile',
                    ['description' => 'test transaction shortcut'],
                )->execute();

                return true;
            },
        );

        self::assertTrue(
            $result,
            'Callback result should be returned.',
        );

        $profilesCount = $db->createCommand(
            <<<SQL
            SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction shortcut'
            SQL
        )->queryScalar();

        self::assertEquals(
            1,
            $profilesCount,
            'Row should be persisted after commit.',
        );
    }

    public function testTransactionShortcutCustom(): void
    {
        $db = $this->getConnection(true);

        $result = $db->transaction(
            static function (Connection $db): bool {
                $db->createCommand()->insert(
                    'profile',
                    ['description' => 'test transaction shortcut'],
                )->execute();

                return true;
            },
            Transaction::READ_UNCOMMITTED,
        );

        self::assertTrue(
            $result,
            'Callback result should be returned.',
        );

        $profilesCount = $db->createCommand(
            <<<SQL
            SELECT COUNT(*) FROM {{profile}} WHERE [[description]] = 'test transaction shortcut'
            SQL
        )->queryScalar();

        self::assertEquals(
            1,
            $profilesCount,
            'Row should be persisted after commit.',
        );
    }

    /**
     * Tests nested transactions with partial rollback.
     *
     * @see https://github.com/yiisoft/yii2/issues/9851
     */
    public function testNestedTransaction(): void
    {
        $db = $this->getConnection(true);

        $db->transaction(
            static function (Connection $db): void {
                self::assertNotNull(
                    $db->transaction,
                    'Outer transaction should be active.',
                );

                $db->transaction(
                    static function (Connection $db): void {
                        self::assertNotNull(
                            $db->transaction,
                            'Nested transaction should be active.',
                        );

                        $db->transaction->rollBack();
                    },
                );

                self::assertNotNull(
                    $db->transaction,
                    'Outer transaction should stay active after nested rollback.',
                );
            },
        );
    }

    public function testThrowNotSupportedExceptionWhenNestedTransactionWithSavepointDisabled(): void
    {
        $db = $this->getConnection();

        $db->enableSavepoint = false;

        $db->transaction(
            function (Connection $db): void {
                self::assertNotNull(
                    $db->transaction,
                    'Outer transaction should be active.',
                );

                $this->expectException(NotSupportedException::class);

                $db->beginTransaction();
            },
        );
    }

    public function testEnableQueryLog(): void
    {
        $db = $this->getConnection();

        foreach (['qlog1', 'qlog2', 'qlog3', 'qlog4'] as $table) {
            if ($db->getTableSchema($table, true) !== null) {
                $db->createCommand()->dropTable($table)->execute();
            }
        }

        // profiling and logging
        $db->enableLogging = true;
        $db->enableProfiling = true;

        Yii::getLogger()->messages = [];

        $db->createCommand()->createTable(
            'qlog1',
            ['id' => 'pk'],
        )->execute();

        self::assertCount(
            3,
            Yii::getLogger()->messages,
            "Profiling and logging should produce '3' messages for create.",
        );
        self::assertNotNull(
            $db->getTableSchema('qlog1', true),
            "Table 'qlog1' should exist.",
        );

        Yii::getLogger()->messages = [];

        $db->createCommand(
            <<<SQL
            SELECT * FROM {{qlog1}}
            SQL
        )->queryAll();

        self::assertCount(
            3,
            Yii::getLogger()->messages,
            "Profiling and logging should produce '3' messages for select.",
        );

        // profiling only
        $db->enableLogging = false;
        $db->enableProfiling = true;

        Yii::getLogger()->messages = [];

        $db->createCommand()->createTable(
            'qlog2',
            ['id' => 'pk'],
        )->execute();

        self::assertCount(
            2,
            Yii::getLogger()->messages,
            "Profiling only should produce '2' messages for create.",
        );
        self::assertNotNull(
            $db->getTableSchema('qlog2', true),
            "Table 'qlog2' should exist.",
        );

        Yii::getLogger()->messages = [];

        $db->createCommand(
            <<<SQL
            SELECT * FROM {{qlog2}}
            SQL
        )->queryAll();

        self::assertCount(
            2,
            Yii::getLogger()->messages,
            "Profiling only should produce '2' messages for select.",
        );

        // logging only
        $db->enableLogging = true;
        $db->enableProfiling = false;

        Yii::getLogger()->messages = [];

        $db->createCommand()->createTable(
            'qlog3',
            ['id' => 'pk'],
        )->execute();

        self::assertCount(
            1,
            Yii::getLogger()->messages,
            "Logging only should produce '1' message for create.",
        );
        self::assertNotNull(
            $db->getTableSchema('qlog3', true),
            "Table 'qlog3' should exist.",
        );

        Yii::getLogger()->messages = [];

        $db->createCommand(
            <<<SQL
            SELECT * FROM {{qlog3}}
            SQL
        )->queryAll();

        self::assertCount(
            1,
            Yii::getLogger()->messages,
            "Logging only should produce '1' message for select.",
        );

        // disabled
        $db->enableLogging = false;
        $db->enableProfiling = false;

        Yii::getLogger()->messages = [];

        $db->createCommand()->createTable(
            'qlog4',
            ['id' => 'pk'],
        )->execute();

        self::assertNotNull(
            $db->getTableSchema('qlog4', true),
            "Table 'qlog4' should exist.",
        );
        self::assertCount(
            0,
            Yii::getLogger()->messages,
            'No messages should be logged for create when disabled.',
        );

        $db->createCommand(
            <<<SQL
            SELECT * FROM {{qlog4}}
            SQL
        )->queryAll();

        self::assertCount(
            0,
            Yii::getLogger()->messages,
            'No messages should be logged for select when disabled.',
        );
    }

    public function testExceptionContainsRawQuery(): void
    {
        $db = $this->getConnection();

        if ($db->getTableSchema('qlog1', true) === null) {
            $db->createCommand()->createTable(
                'qlog1',
                ['id' => 'pk'],
            )->execute();
        }

        $db->emulatePrepare = true;

        // profiling and logging
        $db->enableLogging = true;
        $db->enableProfiling = true;

        $this->runExceptionTest($db);

        // profiling only
        $db->enableLogging = false;
        $db->enableProfiling = true;

        $this->runExceptionTest($db);

        // logging only
        $db->enableLogging = true;
        $db->enableProfiling = false;

        $this->runExceptionTest($db);

        // disabled
        $db->enableLogging = false;
        $db->enableProfiling = false;

        $this->runExceptionTest($db);
    }

    private function runExceptionTest(Connection $db): void
    {
        $thrown = false;
        $sqlAssertLog = 'INSERT INTO qlog1(a) VALUES(1);';

        if ($db->getDriverName() === 'sqlite') {
            // SQLite shows placeholders (`:a`), other drivers show values (`1`) in error messages.
            $sqlAssertLog = <<<SQL
            INSERT INTO qlog1(a) VALUES(:a);
            SQL;
        }

        try {
            $db->createCommand(
                <<<SQL
                INSERT INTO qlog1(a) VALUES(:a);
                SQL,
                [':a' => 1],
            )->execute();
        } catch (Exception $e) {
            self::assertStringContainsString(
                $sqlAssertLog,
                $e->getMessage(),
                'Message should contain the raw SQL.',
            );

            $thrown = true;
        }

        self::assertTrue(
            $thrown,
            'Invalid insert should throw.',
        );

        $thrown = false;
        $sqlAssertLog = <<<SQL
        SELECT * FROM qlog1 WHERE id=1 ORDER BY nonexistingcolumn;
        SQL;

        if ($db->getDriverName() === 'sqlite') {
            // SQLite shows placeholders (`:a`), other drivers show values (`1`) in error messages.
            $sqlAssertLog = <<<SQL
            SELECT * FROM qlog1 WHERE id=:a ORDER BY nonexistingcolumn;
            SQL;
        }

        try {
            $db->createCommand(
                <<<SQL
                SELECT * FROM qlog1 WHERE id=:a ORDER BY nonexistingcolumn;
                SQL,
                [':a' => 1],
            )->queryAll();
        } catch (Exception $e) {
            self::assertStringContainsString(
                $sqlAssertLog,
                $e->getMessage(),
                'Message should contain the raw SQL.',
            );

            $thrown = true;
        }

        self::assertTrue(
            $thrown,
            'Invalid select should throw.',
        );
    }

    /**
     * Ensure database connection is reset on when a connection is cloned.
     * Make sure each connection element has its own PDO instance i.e. own connection to the DB.
     * Also transaction elements should not be shared between two connections.
     */
    public function testClone(): void
    {
        $db = $this->getConnection(true, false);

        self::assertNull(
            $db->transaction,
            'No transaction on a fresh connection.',
        );
        self::assertNull(
            $db->pdo,
            "PDO instance should be 'null' before open.",
        );

        $db->open();

        self::assertNull(
            $db->transaction,
            'Open should not start a transaction.',
        );
        self::assertNotNull(
            $db->pdo,
            'PDO instance should be set after open.',
        );

        $db2 = clone $db;

        self::assertNull(
            $db->transaction,
            'Clone should not affect the source transaction.',
        );
        self::assertNotNull(
            $db->pdo,
            'Clone should not affect the source PDO.',
        );
        self::assertNull(
            $db2->transaction,
            'Clone should not share the transaction.',
        );

        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            self::assertNotNull(
                $db2->pdo,
                'In-memory SQLite clone should keep the PDO.',
            );
        } else {
            self::assertNull(
                $db2->pdo,
                'Clone should reset the PDO.',
            );
        }

        $db->beginTransaction();

        self::assertNotNull(
            $db->transaction,
            'Transaction should be active after begin.',
        );
        self::assertNotNull(
            $db->pdo,
            'PDO instance should be set after begin.',
        );
        self::assertNull(
            $db2->transaction,
            'Clone should not share the transaction.',
        );

        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            self::assertNotNull(
                $db2->pdo,
                'In-memory SQLite clone should keep the PDO.',
            );
        } else {
            self::assertNull(
                $db2->pdo,
                'Clone should reset the PDO.',
            );
        }

        $db3 = clone $db;

        self::assertNotNull(
            $db->transaction,
            'Clone should not affect the source transaction.',
        );
        self::assertNotNull(
            $db->pdo,
            'Clone should not affect the source PDO.',
        );
        self::assertNull(
            $db3->transaction,
            'Clone should not share the active transaction.',
        );

        if ($this->driverName === 'sqlite') {
            // in-memory sqlite should not reset PDO
            self::assertNotNull(
                $db3->pdo,
                'In-memory SQLite clone should keep the PDO.',
            );
        } else {
            self::assertNull(
                $db3->pdo,
                'Clone should reset the PDO.',
            );
        }
    }

    /**
     * Test whether slave connection is recovered when call getSlavePdo(true) after close().
     *
     * @see https://github.com/yiisoft/yii2/issues/14165
     */
    public function testGetPdoAfterClose(): void
    {
        $db = $this->getConnection();

        $db->slaves[] = [
            'dsn' => $db->dsn,
            'username' => $db->username,
            'password' => $db->password,
        ];

        self::assertNotNull(
            $db->getSlavePdo(false),
            'Slave PDO should be available before close.',
        );

        $db->close();

        $masterPdo = $db->getMasterPdo();

        self::assertNotFalse(
            $masterPdo,
            "Master PDO should not be 'false' after close.",
        );
        self::assertNotNull(
            $masterPdo,
            'Master PDO should be recreated after close.',
        );

        $slavePdo = $db->getSlavePdo(false);

        self::assertNotFalse(
            $slavePdo,
            "Slave PDO should not be 'false' after close.",
        );
        self::assertNotNull(
            $slavePdo,
            'Slave PDO should be recreated after close.',
        );
        self::assertNotSame(
            $masterPdo,
            $slavePdo,
            'Slave should use its own PDO instance.',
        );
    }

    public function testServerStatusCacheWorks(): void
    {
        $cache = new ArrayCache();

        Yii::$app->set('cache', $cache);

        $db = $this->getConnection(true, false);

        $db->masters[] = [
            'dsn' => $db->dsn,
            'username' => $db->username,
            'password' => $db->password,
        ];
        $db->shuffleMasters = false;
        $cacheKey = [
            'yii\db\Connection::openFromPoolSequentially',
            $db->dsn,
        ];

        self::assertFalse(
            $cache->exists($cacheKey),
            'Cache should be empty before open.',
        );

        $db->open();

        self::assertFalse(
            $cache->exists($cacheKey),
            'Successful connection should not be cached.',
        );

        $db->close();

        $cacheKey = [
            'yii\db\Connection::openFromPoolSequentially',
            'host:invalid',
        ];
        $db->masters[0]['dsn'] = 'host:invalid';

        try {
            $db->open();
        } catch (InvalidConfigException) {
        }

        self::assertTrue(
            $cache->exists($cacheKey),
            'Failed connection should be cached.',
        );

        $db->close();
    }

    public function testServerStatusCacheCanBeDisabled(): void
    {
        $cache = new ArrayCache();

        Yii::$app->set('cache', $cache);

        $db = $this->getConnection(true, false);
        $db->masters[] = [
            'dsn' => $db->dsn,
            'username' => $db->username,
            'password' => $db->password,
        ];
        $db->shuffleMasters = false;
        $db->serverStatusCache = false;

        $cacheKey = [
            'yii\db\Connection::openFromPoolSequentially',
            $db->dsn,
        ];

        self::assertFalse(
            $cache->exists($cacheKey),
            'Cache should be empty before open.',
        );

        $db->open();

        self::assertFalse(
            $cache->exists($cacheKey),
            'Cache should stay empty when disabled.',
        );

        $db->close();

        $cacheKey = [
            'yii\db\Connection::openFromPoolSequentially',
            'host:invalid',
        ];
        $db->masters[0]['dsn'] = 'host:invalid';

        try {
            $db->open();
        } catch (InvalidConfigException) {
        }

        self::assertFalse(
            $cache->exists($cacheKey),
            'Cache should stay empty for failed connection when disabled.',
        );

        $db->close();
    }
}

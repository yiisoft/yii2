<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\Transaction;
use yiiunit\base\db\BaseConnection;

/**
 * Unit tests for {@see \yii\db\pgsql\Connection} functionality for the PostgreSQL driver.
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('connection')]
class ConnectionTest extends BaseConnection
{
    protected $driverName = 'pgsql';

    public function testGetEffectiveCharsetReturnsConfiguredCharsetOrNull(): void
    {
        $db = new Connection(['dsn' => 'pgsql:host=localhost;dbname=yiitest;port=5432;', 'charset' => 'utf8']);

        self::assertSame(
            'utf8',
            $db->effectiveCharset,
            'Configured charset must be returned.',
        );

        $db = new Connection(['dsn' => 'pgsql:host=localhost;dbname=yiitest;port=5432;']);

        self::assertNull(
            $db->effectiveCharset,
            "No charset source means 'null'.",
        );
    }

    public function testConnection(): void
    {
        $this->assertIsObject($this->getConnection(true));
    }

    public function testQuoteValue(): void
    {
        $connection = $this->getConnection(false);
        $this->assertEquals(123, $connection->quoteValue(123));
        $this->assertEquals("'string'", $connection->quoteValue('string'));
        $this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
    }

    public function testQuoteTableName(): void
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

    public function testQuoteColumnName(): void
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

    public function testQuoteFullColumnName(): void
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

    public function testTransactionIsolation(): void
    {
        $connection = $this->getConnection(false, false);

        $levels = [
            Transaction::READ_UNCOMMITTED => 'read uncommitted',
            Transaction::READ_COMMITTED => 'read committed',
            Transaction::REPEATABLE_READ => 'repeatable read',
            Transaction::SERIALIZABLE => 'serializable',
        ];

        foreach ($levels as $isolationLevel => $expected) {
            $transaction = $connection->beginTransaction($isolationLevel);

            self::assertSame(
                $expected,
                $connection->createCommand(
                    <<<SQL
                    SHOW transaction_isolation
                    SQL,
                )->queryScalar(),
                'Requested level must be active inside the transaction.',
            );

            $transaction->commit();
        }

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE . ' READ ONLY DEFERRABLE');

        self::assertSame(
            'serializable',
            $connection->createCommand('SHOW transaction_isolation')->queryScalar(),
            'Compound level string must keep the level active.',
        );
        self::assertSame(
            'on',
            $connection->createCommand('SHOW transaction_read_only')->queryScalar(),
            'Read-only mode must be active.',
        );
        self::assertSame(
            'on',
            $connection->createCommand('SHOW transaction_deferrable')->queryScalar(),
            'Deferrable mode must be active.',
        );

        $transaction->commit();
    }

    public function testSetTransactionIsolation(): void
    {
        $connection = $this->getConnection(false, false);

        $transaction = $connection->beginTransaction();

        $transaction->setIsolationLevel(Transaction::REPEATABLE_READ);

        self::assertSame(
            'repeatable read',
            $connection->createCommand('SHOW transaction_isolation')->queryScalar(),
            'Level set mid-transaction must be active.',
        );

        $transaction->commit();
    }

    public function testThrowExceptionWhenBeginTransactionIsolationLevelIsInvalid(): void
    {
        $connection = $this->getConnection(false, false);

        try {
            $connection->beginTransaction('INVALID LEVEL');

            self::fail(
                'Unknown isolation level must be rejected.',
            );
        } catch (Exception) {
            // Expected: PostgreSQL rejects the unknown isolation level.
        }

        self::assertFalse(
            $connection->pdo->inTransaction(),
            'Failed begin must leave no open transaction.',
        );

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->commit();
    }
}

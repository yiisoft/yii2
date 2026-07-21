<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Connection;
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
        $connection = $this->getConnection(true);

        $transaction = $connection->beginTransaction();
        $transaction->setIsolationLevel(Transaction::READ_UNCOMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction();
        $transaction->setIsolationLevel(Transaction::READ_COMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction();
        $transaction->setIsolationLevel(Transaction::REPEATABLE_READ);
        $transaction->commit();

        $transaction = $connection->beginTransaction();
        $transaction->setIsolationLevel(Transaction::SERIALIZABLE);
        $transaction->commit();

        $transaction = $connection->beginTransaction();
        $transaction->setIsolationLevel(Transaction::SERIALIZABLE . ' READ ONLY DEFERRABLE');
        $transaction->commit();

        $this->assertTrue(true); // No error occurred – assert passed.
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

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

        $this->assertSame(123, $unserialized->createCommand('SELECT 123 FROM DUAL')->queryScalar());
    }

    public function testQuoteTableName()
    {
        $connection = $this->getConnection(false);
        $this->assertSame('"table"', $connection->quoteTableName('table'));
        $this->assertSame('"table"', $connection->quoteTableName('"table"'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('schema.table'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('schema."table"'));
        $this->assertSame('"schema"."table"', $connection->quoteTableName('"schema"."table"'));
        $this->assertSame('{{table}}', $connection->quoteTableName('{{table}}'));
        $this->assertSame('(table)', $connection->quoteTableName('(table)'));
    }

    public function testQuoteColumnName()
    {
        $connection = $this->getConnection(false);
        $this->assertSame('"column"', $connection->quoteColumnName('column'));
        $this->assertSame('"column"', $connection->quoteColumnName('"column"'));
        $this->assertSame('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertSame('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertSame('(column)', $connection->quoteColumnName('(column)'));

        $this->assertSame('"column"', $connection->quoteSql('[[column]]'));
        $this->assertSame('"column"', $connection->quoteSql('{{column}}'));
    }

    public function testQuoteFullColumnName()
    {
        $connection = $this->getConnection(false, false);
        $this->assertSame('"table"."column"', $connection->quoteColumnName('table.column'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('table."column"'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('"table".column'));
        $this->assertSame('"table"."column"', $connection->quoteColumnName('"table"."column"'));

        $this->assertSame('[[table.column]]', $connection->quoteColumnName('[[table.column]]'));
        $this->assertSame('{{table}}."column"', $connection->quoteColumnName('{{table}}.column'));
        $this->assertSame('{{table}}."column"', $connection->quoteColumnName('{{table}}."column"'));
        $this->assertSame('{{table}}.[[column]]', $connection->quoteColumnName('{{table}}.[[column]]'));
        $this->assertSame('{{%table}}."column"', $connection->quoteColumnName('{{%table}}.column'));
        $this->assertSame('{{%table}}."column"', $connection->quoteColumnName('{{%table}}."column"'));

        $this->assertSame('"table"."column"', $connection->quoteSql('[[table.column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{table}}.[[column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{table}}."column"'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{%table}}.[[column]]'));
        $this->assertSame('"table"."column"', $connection->quoteSql('{{%table}}."column"'));
    }

    public function testTransactionIsolation()
    {
        $connection = $this->getConnection(true);

        $transaction = $connection->beginTransaction(Transaction::READ_COMMITTED);
        $transaction->commit();

        $transaction = $connection->beginTransaction(Transaction::SERIALIZABLE);
        $transaction->commit();
    }
}

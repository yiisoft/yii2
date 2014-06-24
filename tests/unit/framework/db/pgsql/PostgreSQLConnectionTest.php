<?php
namespace yiiunit\framework\db\pgsql;

use yii\db\Transaction;
use yiiunit\framework\db\ConnectionTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLConnectionTest extends ConnectionTest
{
    protected $driverName = 'pgsql';

    public function testConnection()
    {
        $this->getConnection(true);
    }

    public function testQuoteValue()
    {
        $connection = $this->getConnection(false);
        $this->assertEquals(123, $connection->quoteValue(123));
        $this->assertEquals("'string'", $connection->quoteValue('string'));
        $this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
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
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('table.column'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('table."column"'));
        $this->assertEquals('"table"."column"', $connection->quoteColumnName('"table"."column"'));
        $this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
        $this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
        $this->assertEquals('(column)', $connection->quoteColumnName('(column)'));
    }

	public function testTransactionIsolation()
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
		$transaction->setIsolationLevel(Transaction::SERIALIZABLE . ' READ ONLY DEFERABLE');
		$transaction->commit();
	}
}

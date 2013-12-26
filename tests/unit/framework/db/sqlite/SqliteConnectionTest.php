<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\ConnectionTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteConnectionTest extends ConnectionTest
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

	public function testQuoteTableName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals("`table`", $connection->quoteTableName('table'));
		$this->assertEquals("`schema`.`table`", $connection->quoteTableName('schema.table'));
		$this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
		$this->assertEquals('(table)', $connection->quoteTableName('(table)'));
	}

	public function testQuoteColumnName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('`column`', $connection->quoteColumnName('column'));
		$this->assertEquals("`table`.`column`", $connection->quoteColumnName('table.column'));
		$this->assertEquals('[[column]]', $connection->quoteColumnName('[[column]]'));
		$this->assertEquals('{{column}}', $connection->quoteColumnName('{{column}}'));
		$this->assertEquals('(column)', $connection->quoteColumnName('(column)'));
	}
}

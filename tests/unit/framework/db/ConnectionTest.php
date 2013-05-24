<?php

namespace yiiunit\framework\db;

use yii\db\Connection;

class ConnectionTest extends DatabaseTestCase
{
	function testConstruct()
	{
		$connection = $this->getConnection(false);
		$params = $this->database;

		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['username'], $connection->username);
		$this->assertEquals($params['password'], $connection->password);
	}

	function testOpenClose()
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

	function testGetDriverName()
	{
		$connection = $this->getConnection(false, false);
		$this->assertEquals($this->driverName, $connection->driverName);
	}

	function testQuoteValue()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It\\'s interesting'", $connection->quoteValue("It's interesting"));
	}

	function testQuoteTableName()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals('`table`', $connection->quoteTableName('table'));
		$this->assertEquals('`table`', $connection->quoteTableName('`table`'));
		$this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.table'));
		$this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.`table`'));
		$this->assertEquals('{{table}}', $connection->quoteTableName('{{table}}'));
		$this->assertEquals('(table)', $connection->quoteTableName('(table)'));
	}

	function testQuoteColumnName()
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
}

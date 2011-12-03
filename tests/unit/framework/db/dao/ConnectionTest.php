<?php

namespace yiiunit\framework\db\dao;

use yii\db\dao\Connection;

class ConnectionTest extends \yiiunit\TestCase
{
	function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))
			$this->markTestSkipped('pdo and pdo_mysql extensions are required.');
	}

	function testConstruct()
	{
		$connection = $this->createConnection();
		$params = $this->getParam('mysql');

		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['username'], $connection->username);
		$this->assertEquals($params['password'], $connection->password);
	}

	function testOpenClose()
	{
		$connection = $this->createConnection();

		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection->open();
		$this->assertTrue($connection->active);
		$this->assertTrue($connection->pdo instanceof \PDO);

		$connection->close();
		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection = new Connection('unknown::memory:');
		$this->setExpectedException('yii\db\Exception');
		$connection->open();
	}

	function testGetDriverName()
	{
		$connection = $this->createConnection();
		$this->assertEquals('mysql', $connection->driverName);
		$this->assertFalse($connection->active);
	}

	function testQuoteValue()
	{
		$connection = $this->createConnection();
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It\'s interesting'", $connection->quoteValue("It's interesting"));
	}

	function testQuoteTableName()
	{
		$connection = $this->createConnection();
		$this->assertEquals('`table`', $connection->quoteTableName('table'));
		$this->assertEquals('`table`', $connection->quoteTableName('`table`'));
		$this->assertEquals('`schema`.`table`', $connection->quoteTableName('schema.table'));
	}

	function testQuoteColumnName()
	{
		$connection = $this->createConnection();
		$this->assertEquals('`column`', $connection->quoteColumnName('column'));
		$this->assertEquals('`column`', $connection->quoteColumnName('`column`'));
		$this->assertEquals('`table`.`column`', $connection->quoteColumnName('table.column'));
	}

	function testGetPdoType()
	{
		$connection = $this->createConnection();
		$this->assertEquals(\PDO::PARAM_BOOL, $connection->getPdoType('boolean'));
		$this->assertEquals(\PDO::PARAM_INT, $connection->getPdoType('integer'));
		$this->assertEquals(\PDO::PARAM_STR, $connection->getPdoType('string'));
		$this->assertEquals(\PDO::PARAM_NULL, $connection->getPdoType('NULL'));
	}

	function testAttribute()
	{

	}

	function createConnection()
	{
		$params = $this->getParam('mysql');
		return new Connection($params['dsn'], $params['username'], $params['password']);
	}
}

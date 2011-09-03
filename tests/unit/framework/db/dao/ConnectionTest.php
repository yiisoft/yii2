<?php

use yii\db\dao\Connection;

class ConnectionTest extends TestCase
{
	function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))
			$this->markTestSkipped('PDO and MySQL extensions are required.');
	}

	function testConstruct()
	{
		$params = $this->getParam('mysql');
		$connection = new Connection($params['dsn'], $params['username'], $params['password']);
		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['username'], $connection->username);
		$this->assertEquals($params['password'], $connection->password);
	}

	function testOpenClose()
	{
		$params = $this->getParam('mysql');
		$connection = new Connection($params['dsn'], $params['username'], $params['password']);
		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection->open();
		$this->assertTrue($connection->active);
		$this->assertTrue($connection->pdo instanceof PDO);

		$connection->close();
		$this->assertFalse($connection->active);
		$this->assertEquals(null, $connection->pdo);

		$connection = new Connection('unknown::memory:');
		$this->setExpectedException('yii\db\Exception');
		$connection->open();
	}

	/*
	function testCreateCommand()
	{
		$sql='SELECT * FROM posts';
		$this->connection->active=true;
		$this->connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
		$command=$this->connection->createCommand($sql);
		$this->assertTrue($command instanceof CDbCommand);
	}

	function testLastInsertID()
	{
	    $this->connection->active=true;
	    $this->connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
		$sql='INSERT INTO posts(title,create_time,author_id) VALUES(\'test post\',11000,1)';
		$this->connection->createCommand($sql)->execute();
		$this->assertEquals($this->connection->lastInsertID,6);
	}

	function testQuoteValue()
	{
	    $this->connection->active=true;
	    $this->connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
		$str="this is 'my' name";
		$expectedStr="'this is ''my'' name'";
		$this->assertEquals($expectedStr,$this->connection->quoteValue($str));
	}

	function testColumnNameCase()
	{
	    $this->connection->active=true;
	    $this->connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
		$this->assertEquals(PDO::CASE_NATURAL,$this->connection->ColumnCase);
		$this->connection->columnCase=PDO::CASE_LOWER;
		$this->assertEquals(PDO::CASE_LOWER,$this->connection->ColumnCase);
	}

	function testNullConversion()
	{
	    $this->connection->active=true;
	    $this->connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
		$this->assertEquals(PDO::NULL_NATURAL,$this->connection->NullConversion);
		$this->connection->nullConversion=PDO::NULL_EMPTY_STRING;
		$this->assertEquals(PDO::NULL_EMPTY_STRING,$this->connection->NullConversion);
	}
	*/
}

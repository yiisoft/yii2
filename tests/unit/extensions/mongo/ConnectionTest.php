<?php

namespace yiiunit\extensions\mongo;


use yii\mongo\Connection;

class ConnectionTest extends MongoTestCase
{
	public function testConstruct()
	{
		$connection = $this->getConnection(false);
		$params = $this->mongoConfig;

		$connection->open();

		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['dbName'], $connection->dbName);
		$this->assertEquals($params['options'], $connection->options);
	}

	public function testOpenClose()
	{
		$connection = $this->getConnection(false, false);

		$this->assertFalse($connection->isActive);
		$this->assertEquals(null, $connection->client);

		$connection->open();
		$this->assertTrue($connection->isActive);
		$this->assertTrue(is_object($connection->client));

		$connection->close();
		$this->assertFalse($connection->isActive);
		$this->assertEquals(null, $connection->client);

		$connection = new Connection;
		$connection->dsn = 'unknown::memory:';
		$this->setExpectedException('yii\db\Exception');
		$connection->open();
	}
}
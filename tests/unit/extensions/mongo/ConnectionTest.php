<?php

namespace yiiunit\extensions\mongo;


class ConnectionTest extends MongoTestCase
{
	public function testConstruct()
	{
		$connection = $this->getConnection(false);
		$params = $this->mongoConfig;

		$connection->open();

		$this->assertEquals($params['dsn'], $connection->dsn);
		//$this->assertEquals($params['username'], $connection->username);
		//$this->assertEquals($params['password'], $connection->password);
	}
}
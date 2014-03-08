<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\Connection;

/**
 * @group sphinx
 */
class ConnectionTest extends SphinxTestCase
{
	public function testConstruct()
	{
		$connection = $this->getConnection(false);
		$params = $this->sphinxConfig;

		$this->assertEquals($params['dsn'], $connection->dsn);
		$this->assertEquals($params['username'], $connection->username);
		$this->assertEquals($params['password'], $connection->password);
	}

	public function testOpenClose()
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
}

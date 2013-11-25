<?php

namespace yiiunit\extensions\redis;

use Yii;
use yii\redis\Connection;
use yiiunit\TestCase;

Yii::setAlias('@yii/redis', __DIR__ . '/../../../../extensions/redis');

/**
 * RedisTestCase is the base class for all redis related test cases
 */
abstract class RedisTestCase extends TestCase
{
	protected function setUp()
	{
		$databases = $this->getParam('databases');
		$params = isset($databases['redis']) ? $databases['redis'] : null;
		if ($params === null) {
			$this->markTestSkipped('No redis server connection configured.');
		}
		$connection = new Connection($params);
		if(!@stream_socket_client($connection->hostname . ':' . $connection->port, $errorNumber, $errorDescription, 0.5)) {
			$this->markTestSkipped('No redis server running at ' . $connection->hostname . ':' . $connection->port . ' : ' . $errorNumber . ' - ' . $errorDescription);
		}

		$this->mockApplication(['components' => ['redis' => $connection]]);

		parent::setUp();
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @return Connection
	 */
	public function getConnection($reset = true)
	{
		$databases = $this->getParam('databases');
		$params = isset($databases['redis']) ? $databases['redis'] : [];
		$db = new Connection($params);
		if ($reset) {
			$db->open();
			$db->flushdb();
		}
		return $db;
	}
}
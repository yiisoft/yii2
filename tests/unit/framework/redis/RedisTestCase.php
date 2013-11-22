<?php

namespace yiiunit\framework\redis;

use yii\redis\Connection;
use yiiunit\TestCase;

/**
 * RedisTestCase is the base class for all redis related test cases
 */
abstract class RedisTestCase extends TestCase
{
	protected function setUp()
	{
		$this->mockApplication();

		$databases = $this->getParam('databases');
		$params = isset($databases['redis']) ? $databases['redis'] : null;
		if ($params === null || !isset($params['dsn'])) {
			$this->markTestSkipped('No redis server connection configured.');
		}
		$dsn = explode('/', $params['dsn']);
		$host = $dsn[2];
		if (strpos($host, ':')===false) {
			$host .= ':6379';
		}
		if(!@stream_socket_client($host, $errorNumber, $errorDescription, 0.5)) {
			$this->markTestSkipped('No redis server running at ' . $params['dsn'] . ' : ' . $errorNumber . ' - ' . $errorDescription);
		}

		parent::setUp();
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @return Connection
	 */
	public function getConnection($reset = true)
	{
		$databases = $this->getParam('databases');
		$params = isset($databases['redis']) ? $databases['redis'] : array();
		$db = new Connection;
		$db->dsn = $params['dsn'];
		$db->password = $params['password'];
		if ($reset) {
			$db->open();
			$db->flushall();
		}
		return $db;
	}
}
<?php

namespace yiiunit\framework\db\redis;

use yii\db\redis\Connection;
use yiiunit\TestCase;

/**
 * RedisTestCase is the base class for all redis related test cases
 */
class RedisTestCase extends TestCase
{
	protected function setUp()
	{
		$params = $this->getParam('redis');
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
		$params = $this->getParam('redis');
		$db = new \yii\db\redis\Connection;
		$db->dsn = $params['dsn'];
		$db->password = $params['password'];
		if ($reset) {
			$db->open();
			$db->flushall();
/*			$lines = explode(';', file_get_contents($params['fixture']));
			foreach ($lines as $line) {
				if (trim($line) !== '') {
					$db->pdo->exec($line);
				}
			}*/
		}
		return $db;
	}
}
<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\db\redis;


use yii\db\redis\Connection;
use yiiunit\TestCase;

class RedisTestCase extends TestCase
{
	function __construct()
	{
		// TODO check if a redis server is running
		//$this->markTestSkipped('No redis server running at port ...');
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @return Connection
	 */
	function getConnection($reset = true)
	{
		$params = $this->getParam('redis');
		$db = new \yii\db\redis\Connection;
		$db->dsn = $params['dsn'];
		$db->username = $params['username'];
		$db->password = $params['password'];
		if ($reset) {
			// TODO implement
/*			$db->open();
			$lines = explode(';', file_get_contents($params['fixture']));
			foreach ($lines as $line) {
				if (trim($line) !== '') {
					$db->pdo->exec($line);
				}
			}*/
		}
		return $db;
	}
}
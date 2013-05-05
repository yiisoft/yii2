<?php

namespace yiiunit\framework\db;

use yiiunit\TestCase;

class DatabaseTestCase extends TestCase
{
	protected $driver;

	protected function setUp()
	{
		$this->driver = isset($_ENV['db_driver']) ? $_ENV['db_driver'] : 'mysql';

		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
			$this->markTestSkipped('pdo and pdo_mysql extensions are required.');
		}
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @return \yii\db\Connection
	 */
	public function getConnection($reset = true)
	{
		$params = $this->getParam($this->driver);
		$db = new \yii\db\Connection;
		$db->dsn = $params['dsn'];
		$db->username = $params['username'];
		$db->password = $params['password'];
		if ($reset) {
			$db->open();
			$lines = explode(';', file_get_contents($params['fixture']));
			foreach ($lines as $line) {
				if (trim($line) !== '') {
					$db->pdo->exec($line);
				}
			}
		}
		return $db;
	}
}
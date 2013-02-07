<?php
namespace yiiunit\framework\caching;
use yii\caching\DbCache;
use yiiunit\TestCase;

/**
 * Class for testing file cache backend
 */
class DbCacheTest extends CacheTest
{
	private $_cacheInstance;
	private $_connection;

	function __construct()
	{
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
			$this->markTestSkipped('pdo and pdo_mysql extensions are required.');
		}

		$this->getConnection()->createCommand("
			CREATE TABLE IF NOT EXISTS tbl_cache (
				id char(128) NOT NULL,
				expire int(11) DEFAULT NULL,
				data LONGBLOB,
				PRIMARY KEY (id),
				KEY expire (expire)
			);
		")->execute();
	}

	/**
	 * @param bool $reset whether to clean up the test database
	 * @return \yii\db\Connection
	 */
	function getConnection($reset = true)
	{
		if($this->_connection === null) {
			$params = $this->getParam('mysql');
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
			$this->_connection = $db;
		}
		return $this->_connection;
	}


	/**
	 * @return DbCache
	 */
	protected function getCacheInstance()
	{
		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new DbCache(array(
				'db' => $this->getConnection(),
			));
		}
		return $this->_cacheInstance;
	}
}
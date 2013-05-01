<?php
namespace yiiunit\framework\caching;
use yii\caching\MemCache;
use yii\caching\RedisCache;
use yiiunit\TestCase;

/**
 * Class for testing redis cache backend
 */
class RedisCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return MemCache
	 */
	protected function getCacheInstance()
	{
		$config = array(
			'hostname' => 'localhost',
			'port' => 6379,
			'database' => 0,
		);
		$dsn = $config['hostname'] . ':' .$config['port'];
		if(!@stream_socket_client($dsn, $errorNumber, $errorDescription, 0.5)) {
			$this->markTestSkipped('No redis server running at ' . $dsn .' : ' . $errorNumber . ' - ' . $errorDescription);
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new RedisCache($config);
		}
		return $this->_cacheInstance;
	}
}
<?php
namespace yiiunit\framework\caching;
use yii\caching\MemCache;
use yii\caching\RedisCache;
use yiiunit\TestCase;

/**
 * Class for testing redis cache backend
 * @group redis
 * @group caching
 */
class RedisCacheTest extends CacheTestCase
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
			'dataTimeout' => 0.1,
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

	public function testExpireMilliseconds()
	{
		$cache = $this->getCacheInstance();

		$this->assertTrue($cache->set('expire_test_ms', 'expire_test_ms', 0.2));
		usleep(100000);
		$this->assertEquals('expire_test_ms', $cache->get('expire_test_ms'));
		usleep(300000);
		$this->assertFalse($cache->get('expire_test_ms'));
	}

	/**
	 * Store a value that is 2 times buffer size big
	 * https://github.com/yiisoft/yii2/issues/743
	 */
	public function testLargeData()
	{
		$cache = $this->getCacheInstance();

		$data=str_repeat('XX',8192); // http://www.php.net/manual/en/function.fread.php
		$key='bigdata1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);

		// try with multibyte string
		$data=str_repeat('ЖЫ',8192); // http://www.php.net/manual/en/function.fread.php
		$key='bigdata2';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

	public function testMultiByteGetAndSet()
	{
		$cache = $this->getCacheInstance();

		$data=array('abc'=>'ежик',2=>'def');
		$key='data1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

}
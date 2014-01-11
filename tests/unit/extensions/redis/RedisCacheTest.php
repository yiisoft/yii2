<?php
namespace yiiunit\extensions\redis;

use Yii;
use yii\redis\Cache;
use yii\redis\Connection;
use yiiunit\framework\caching\CacheTestCase;

Yii::setAlias('@yii/redis', __DIR__ . '/../../../../extensions/redis');

/**
 * Class for testing redis cache backend
 * @group redis
 * @group caching
 */
class RedisCacheTest extends CacheTestCase
{
	private $_cacheInstance = null;

	/**
	 * @return Cache
	 */
	protected function getCacheInstance()
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

		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new Cache();
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

	public function testExpireAddMilliseconds()
	{
		$cache = $this->getCacheInstance();

		$this->assertTrue($cache->add('expire_testa_ms', 'expire_testa_ms', 0.2));
		usleep(100000);
		$this->assertEquals('expire_testa_ms', $cache->get('expire_testa_ms'));
		usleep(300000);
		$this->assertFalse($cache->get('expire_testa_ms'));
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

		$data=['abc'=>'ежик',2=>'def'];
		$key='data1';

		$this->assertFalse($cache->get($key));
		$cache->set($key,$data);
		$this->assertTrue($cache->get($key)===$data);
	}

}

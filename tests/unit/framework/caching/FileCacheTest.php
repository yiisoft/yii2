<?php
namespace yiiunit\framework\caching;
use yii\caching\FileCache;
use yiiunit\TestCase;

/**
 * Class for testing file cache backend
 */
class FileCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return FileCache
	 */
	protected function getCacheInstance()
	{
		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new FileCache(array(
				'cachePath' => '@yiiunit/runtime/cache',
			));
		}
		return $this->_cacheInstance;
	}

	public function testExpire()
	{
		$cache = $this->getCacheInstance();

		$this->assertTrue($cache->set('expire_test', 'expire_test', 2));
		static::$time = time() + 1;
		$this->assertEquals('expire_test', $cache->get('expire_test'));
		static::$time = time() + 2;
		$this->assertFalse($cache->get('expire_test'));
	}
}

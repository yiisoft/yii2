<?php
namespace yiiunit\framework\caching;

use yii\caching\MemCache;

/**
 * Class for testing memcache cache backend
 * @group memcache
 * @group caching
 */
class MemCacheTest extends CacheTestCase
{
	private $_cacheInstance = null;

	/**
	 * @return MemCache
	 */
	protected function getCacheInstance()
	{
		if (!extension_loaded("memcache")) {
			$this->markTestSkipped("memcache not installed. Skipping.");
		}

		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new MemCache();
		}
		return $this->_cacheInstance;
	}

	public function testExpire()
	{
		if (isset($_ENV['TRAVIS']) && $_ENV['TRAVIS']) {
			$this->markTestSkipped('Can not reliably test memcache expiry on travis-ci.');
		}
		parent::testExpire();
	}
}

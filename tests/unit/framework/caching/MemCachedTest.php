<?php
namespace yiiunit\framework\caching;

use yii\caching\MemCache;

/**
 * Class for testing memcached cache backend
 * @group memcached
 * @group caching
 */
class MemCachedTest extends CacheTestCase
{
	private $_cacheInstance = null;

	/**
	 * @return MemCache
	 */
	protected function getCacheInstance()
	{
		if (!extension_loaded("memcached")) {
			$this->markTestSkipped("memcached not installed. Skipping.");
		}

		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new MemCache(['useMemcached' => true]);
		}
		return $this->_cacheInstance;
	}

	public function testExpire()
	{
		if (isset($_ENV['TRAVIS']) && $_ENV['TRAVIS']) {
			$this->markTestSkipped('Can not reliably test memcached expiry on travis-ci.');
		}
		parent::testExpire();
	}
}

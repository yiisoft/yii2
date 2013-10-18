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
			$this->_cacheInstance = new MemCache(array(
				'useMemcached' => true,
			));
		}
		return $this->_cacheInstance;
	}
}

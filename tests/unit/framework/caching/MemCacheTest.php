<?php
namespace yiiunit\framework\caching;
use yii\caching\MemCache;
use yiiunit\TestCase;

/**
 * Class for testing memcache cache backend
 */
class MemCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return MemCache
	 */
	protected function getCacheInstance()
	{
		if(!extension_loaded("memcache")) {
			$this->markTestSkipped("memcache not installed. Skipping.");
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new MemCache();
		}
		return $this->_cacheInstance;
	}
}

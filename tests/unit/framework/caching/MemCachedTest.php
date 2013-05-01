<?php
namespace yiiunit\framework\caching;
use yii\caching\MemCache;
use yiiunit\TestCase;

/**
 * Class for testing memcached cache backend
 */
class MemCachedTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return MemCache
	 */
	protected function getCacheInstance()
	{
		if(!extension_loaded("memcached")) {
			$this->markTestSkipped("memcached not installed. Skipping.");
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new MemCache(array(
				'useMemcached' => true,
			));
		}
		return $this->_cacheInstance;
	}
}
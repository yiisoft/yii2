<?php
namespace yiiunit\framework\caching;
use yii\caching\ApcCache;
use yiiunit\TestCase;

/**
 * Class for testing APC cache backend
 */
class ApcCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return ApcCache
	 */
	protected function getCacheInstance()
	{
		if(!extension_loaded("apc")) {
			$this->markTestSkipped("APC not installed. Skipping.");
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new ApcCache();
		}
		return $this->_cacheInstance;
	}
}
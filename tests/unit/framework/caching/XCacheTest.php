<?php
namespace yiiunit\framework\caching;
use yii\caching\XCache;
use yiiunit\TestCase;

/**
 * Class for testing xcache backend
 */
class XCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return XCache
	 */
	protected function getCacheInstance()
	{
		if(!function_exists("xcache_isset")) {
			$this->markTestSkipped("XCache not installed. Skipping.");
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new XCache();
		}
		return $this->_cacheInstance;
	}
}
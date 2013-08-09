<?php
namespace yiiunit\framework\caching;
use yii\caching\ApcCache;

/**
 * Class for testing APC cache backend
 */
class ApcCacheTest extends CacheTestCase
{
	private $_cacheInstance = null;

	/**
	 * @return ApcCache
	 */
	protected function getCacheInstance()
	{
		if (!extension_loaded("apc")) {
			$this->markTestSkipped("APC not installed. Skipping.");
		} elseif ('cli' === PHP_SAPI && !ini_get('apc.enable_cli')) {
			$this->markTestSkipped("APC cli is not enabled. Skipping.");
		}

		if(!ini_get("apc.enabled") || !ini_get("apc.enable_cli")) {
			$this->markTestSkipped("APC is installed but not enabled. Skipping.");
		}

		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new ApcCache();
		}
		return $this->_cacheInstance;
	}

	public function testExpire()
	{
		$this->markTestSkipped("APC keys are expiring only on the next request.");
	}
}

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
		} else if ('cli' === PHP_SAPI && !ini_get('apc.enable_cli')) {
			$this->markTestSkipped("APC cli is not enabled. Skipping.");
		}

		if(!ini_get("apc.enabled") || !ini_get("apc.enable_cli")) {
			$this->markTestSkipped("APC is installed but not enabled. Skipping.");
		}

		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new ApcCache();
		}
		return $this->_cacheInstance;
	}

	// TODO there seems to be a problem with APC returning cached value even if it is expired.
	// TODO makes test fail on PHP 5.3.10-1ubuntu3.6 with Suhosin-Patch (cli) -- cebe
	// TODO http://drupal.org/node/1278292
}

<?php
namespace yiiunit\framework\caching;

use yii\caching\ZendDataCache;

/**
 * Class for testing Zend cache backend
 * @group zenddata
 * @group caching
 */
class ZendDataCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return ZendDataCache
     */
    protected function getCacheInstance()
    {
        if (!function_exists("zend_shm_cache_store")) {
            $this->markTestSkipped("Zend Data cache not installed. Skipping.");
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new ZendDataCache();
        }

        return $this->_cacheInstance;
    }
}

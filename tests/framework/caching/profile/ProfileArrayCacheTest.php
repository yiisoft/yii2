<?php

namespace yiiunit\framework\caching\profile;

use yii\caching\ArrayCache;
use yiiunit\framework\caching\ArrayCacheTest;

/**
 * Class for testing file cache backend
 * @group caching
 */
class ProfileArrayCacheTest extends ArrayCacheTest
{
    /**
     * @return ArrayCache
     */
    protected function getCacheInstance()
    {
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new ArrayCache();
            $this->_cacheInstance->enableProfiling = true;
        }
        return $this->_cacheInstance;
    }    
}

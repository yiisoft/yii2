<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\Cache;
use yii\caching\WinCache;

/**
 * Class for testing wincache backend.
 * @group wincache
 * @group caching
 */
class WinCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return Cache
     */
    protected function getCacheInstance()
    {
        if (!extension_loaded('wincache')) {
            $this->markTestSkipped('Wincache not installed. Skipping.');
        }

        if (!ini_get('wincache.ucenabled')) {
            $this->markTestSkipped('Wincache user cache disabled. Skipping.');
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new Cache([
                'handler' => new WinCache(),
            ]);
        }

        return $this->_cacheInstance;
    }
}

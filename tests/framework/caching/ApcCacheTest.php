<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\ApcCache;
use yii\caching\Cache;

/**
 * Class for testing APC cache backend.
 * @group apc
 * @group caching
 */
class ApcCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return Cache
     */
    protected function getCacheInstance()
    {
        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APCu not installed. Skipping.');
        }

        if (!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('APC is installed but not enabled. Skipping.');
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new Cache([
                'handler' => new ApcCache()
            ]);
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        $this->markTestSkipped('APC keys are expiring only on the next request.');
    }

    public function testExpireAdd()
    {
        $this->markTestSkipped('APC keys are expiring only on the next request.');
    }
}

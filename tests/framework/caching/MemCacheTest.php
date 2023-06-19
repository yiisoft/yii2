<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\MemCache;

/**
 * Class for testing memcache cache backend.
 * @group memcache
 * @group caching
 */
class MemCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return MemCache
     */
    protected function getCacheInstance()
    {
        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('memcache not installed. Skipping.');
        }

        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client('127.0.0.1:11211', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No memcached server running at ' . '127.0.0.1:11211' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new MemCache();
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        if (getenv('GITHUB_ACTIONS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcache expiry on GitHub actions.');
        }
        parent::testExpire();
    }

    public function testExpireAdd()
    {
        if (getenv('GITHUB_ACTIONS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcache expiry on GitHub actions.');
        }
        parent::testExpireAdd();
    }
}

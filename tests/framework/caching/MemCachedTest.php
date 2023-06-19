<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\MemCache;

/**
 * Class for testing memcached cache backend.
 * @group memcached
 * @group caching
 */
class MemCachedTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return MemCache
     */
    protected function getCacheInstance()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('memcached not installed. Skipping.');
        }

        if (
            PHP_VERSION_ID >= 80100 && version_compare(phpversion('memcached'), '3.1.5', '<=')
        ) {
            $php_version = phpversion();
            $memcached_version = phpversion('memcached');
            $this->markTestSkipped("memcached version $memcached_version is not ready for PHP $php_version. Skipping.");
        }

        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client('127.0.0.1:11211', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No memcached server running at ' . '127.0.0.1:11211' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new MemCache(['useMemcached' => true]);
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        if (getenv('GITHUB_ACTIONS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcached expiry on GitHub actions.');
        }
        parent::testExpire();
    }

    public function testExpireAdd()
    {
        if (getenv('GITHUB_ACTIONS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcached expiry on GitHub actions.');
        }
        parent::testExpireAdd();
    }
}

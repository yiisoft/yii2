<?php
namespace yiiunit\framework\caching;

use yii\caching\MemCache;

/**
 * Class for testing memcached cache backend
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
        if (!extension_loaded("memcached")) {
            $this->markTestSkipped("memcached not installed. Skipping.");
        }

        // check whether memcached is running and skip tests if not.
        if (!@stream_socket_client('127.0.0.1:11211', $errorNumber, $errorDescription, 0.5)) {
            $this->markTestSkipped('No redis server running at ' . '127.0.0.1:11211' . ' : ' . $errorNumber . ' - ' . $errorDescription);
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new MemCache(['useMemcached' => true]);
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcached expiry on travis-ci.');
        }
        parent::testExpire();
    }

    public function testExpireAdd()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Can not reliably test memcached expiry on travis-ci.');
        }
        parent::testExpireAdd();
    }
}

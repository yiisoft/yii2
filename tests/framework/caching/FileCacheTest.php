<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use yii\caching\FileCache;

/**
 * Class for testing file cache backend
 * @group caching
 */
class FileCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return FileCache
     */
    protected function getCacheInstance()
    {
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);
        }

        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        static::$time++;
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        static::$time++;
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testExpireAdd()
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->add('expire_testa', 'expire_testa', 2));
        static::$time++;
        $this->assertEquals('expire_testa', $cache->get('expire_testa'));
        static::$time++;
        $this->assertFalse($cache->get('expire_testa'));
    }
}

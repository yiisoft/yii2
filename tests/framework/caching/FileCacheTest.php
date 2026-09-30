<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\caching;

use ReflectionClass;
use yii\caching\FileCache;

/**
 * Class for testing file cache backend.
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

    public function testExpire(): void
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        static::$time++;
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        static::$time++;
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testExpireAdd(): void
    {
        $cache = $this->getCacheInstance();

        static::$time = \time();
        $this->assertTrue($cache->add('expire_testa', 'expire_testa', 2));
        static::$time++;
        $this->assertEquals('expire_testa', $cache->get('expire_testa'));
        static::$time++;
        $this->assertFalse($cache->get('expire_testa'));
    }

    public function testKeyPrefix(): void
    {
        $keyPrefix = 'foobar';
        $key = uniqid('uid-cache_');
        $cache = $this->getCacheInstance();
        $cache->flush();

        $cache->directoryLevel = 1;
        $cache->keyPrefix = $keyPrefix;
        $normalizeKey = $cache->buildKey($key);
        $expectedDirectoryName = substr($normalizeKey, 6, 2);

        $value = \time();

        $refClass = new ReflectionClass($cache);

        $refMethodGetCacheFile = $refClass->getMethod('getCacheFile');

        // @link https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_reflectionsetaccessible
        // @link https://wiki.php.net/rfc/make-reflection-setaccessible-no-op
        if (PHP_VERSION_ID < 80100) {
            $refMethodGetCacheFile->setAccessible(true);
        }

        $refMethodGet = $refClass->getMethod('get');
        $refMethodSet = $refClass->getMethod('set');

        $cacheFile = $refMethodGetCacheFile->invoke($cache, $normalizeKey);

        $this->assertTrue($refMethodSet->invoke($cache, $key, $value));
        $this->assertStringContainsString($keyPrefix, basename($cacheFile));
        $this->assertEquals($expectedDirectoryName, basename(dirname($cacheFile)), $cacheFile);
        $this->assertTrue(is_dir(dirname($cacheFile)), 'File not found ' . $cacheFile);
        $this->assertEquals($value, $refMethodGet->invoke($cache, $key));
    }

    public function testStatCache(): void
    {
        $cache = $this->getCacheInstance();
        $cache->set(__FUNCTION__, 'cache1', 2);

        $normalizeKey = $cache->buildKey(__FUNCTION__);
        $refClass = new ReflectionClass($cache);
        $refMethodGetCacheFile = $refClass->getMethod('getCacheFile');

        // @link https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_reflectionsetaccessible
        // @link https://wiki.php.net/rfc/make-reflection-setaccessible-no-op
        if (PHP_VERSION_ID < 80100) {
            $refMethodGetCacheFile->setAccessible(true);
        }

        $cacheFile = $refMethodGetCacheFile->invoke($cache, $normalizeKey);

        // simulate cache expire 10 seconds ago
        touch($cacheFile, time() - 10);
        clearstatcache();

        $this->assertFalse($cache->get(__FUNCTION__));
        $this->assertTrue($cache->set(__FUNCTION__, 'cache2', 2));
        $this->assertSame('cache2', $cache->get(__FUNCTION__));
    }

    /**
     * @dataProvider \yiiunit\framework\caching\providers\FileCacheProvider::missingCacheFile
     *
     * @see https://github.com/yiisoft/yii2/pull/20260
     *
     * @param string $method Cache method invoked for a key whose cache file does not exist.
     * @param array $arguments Arguments passed to the method.
     * @param mixed $expected Expected return value.
     */
    public function testMissingCacheFileDoesNotTriggerStatWarning(string $method, array $arguments, $expected): void
    {
        $cache = $this->getCacheInstance();
        $cache->flush();

        $errors = [];

        set_error_handler(
            static function ($errno, $errstr) use (&$errors) {
                if (strpos($errstr, 'filemtime(): stat failed') !== false) {
                    $errors[] = $errstr;

                    return true;
                }

                return false;
            }
        );

        try {
            $result = $cache->$method(...$arguments);
        } finally {
            restore_error_handler();
        }

        $this->assertSame(
            [],
            $errors,
            "No 'filemtime' stat warnings expected.",
        );
        $this->assertSame(
            $expected,
            $result,
            "Unexpected result from cache method.",
        );
    }
}

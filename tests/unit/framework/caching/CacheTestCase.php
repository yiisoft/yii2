<?php

namespace yii\caching;

/**
 * Mock for the time() function for caching classes
 * @return int
 */
function time()
{
    return \yiiunit\framework\caching\CacheTestCase::$time ?: \time();
}

namespace yiiunit\framework\caching;

use yiiunit\TestCase;

/**
 * Base class for testing cache backends
 */
abstract class CacheTestCase extends TestCase
{
    /**
     * @var integer virtual time to be returned by mocked time() function.
     * Null means normal time() behavior.
     */
    public static $time;

    /**
     * @return Cache
     */
    abstract protected function getCacheInstance();

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function tearDown()
    {
        static::$time = null;
    }

    /**
     * @return Cache
     */
    public function prepare()
    {
        $cache = $this->getCacheInstance();

        $cache->flush();
        $cache->set('string_test', 'string_test');
        $cache->set('number_test', 42);
        $cache->set('array_test', ['array_test' => 'array_test']);
        $cache['arrayaccess_test'] = new \stdClass();

        return $cache;
    }

    public function testSet()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('string_test', 'string_test'));
        $this->assertTrue($cache->set('number_test', 42));
        $this->assertTrue($cache->set('array_test', ['array_test' => 'array_test']));
    }

    public function testGet()
    {
        $cache = $this->prepare();

        $this->assertEquals('string_test', $cache->get('string_test'));

        $this->assertEquals(42, $cache->get('number_test'));

        $array = $cache->get('array_test');
        $this->assertArrayHasKey('array_test', $array);
        $this->assertEquals('array_test', $array['array_test']);
    }

    /**
     * @return array testing mset with and without expiry
     */
    public function msetExpiry()
    {
        return [[0], [2]];
    }

    /**
     * @dataProvider msetExpiry
     */
    public function testMset($expiry)
    {
        $cache = $this->getCacheInstance();
        $cache->flush();

        $cache->mset([
            'string_test' => 'string_test',
            'number_test' => 42,
            'array_test' => ['array_test' => 'array_test'],
        ], $expiry);

        $this->assertEquals('string_test', $cache->get('string_test'));

        $this->assertEquals(42, $cache->get('number_test'));

        $array = $cache->get('array_test');
        $this->assertArrayHasKey('array_test', $array);
        $this->assertEquals('array_test', $array['array_test']);
    }

    public function testExists()
    {
        $cache = $this->prepare();

        $this->assertTrue($cache->exists('string_test'));
        // check whether exists affects the value
        $this->assertEquals('string_test', $cache->get('string_test'));

        $this->assertTrue($cache->exists('number_test'));
        $this->assertFalse($cache->exists('not_exists'));
    }

    public function testArrayAccess()
    {
        $cache = $this->getCacheInstance();

        $cache['arrayaccess_test'] = new \stdClass();
        $this->assertInstanceOf('stdClass', $cache['arrayaccess_test']);
    }

    public function testGetNonExistent()
    {
        $cache = $this->getCacheInstance();

        $this->assertFalse($cache->get('non_existent_key'));
    }

    public function testStoreSpecialValues()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('null_value', null));
        $this->assertNull($cache->get('null_value'));

        $this->assertTrue($cache->set('bool_value', true));
        $this->assertTrue($cache->get('bool_value'));
    }

    public function testMget()
    {
        $cache = $this->prepare();

        $this->assertEquals(['string_test' => 'string_test', 'number_test' => 42], $cache->mget(['string_test', 'number_test']));
        // ensure that order does not matter
        $this->assertEquals(['number_test' => 42, 'string_test' => 'string_test'], $cache->mget(['number_test', 'string_test']));
        $this->assertEquals(['number_test' => 42, 'non_existent_key' => null], $cache->mget(['number_test', 'non_existent_key']));
    }

    public function testExpire()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        usleep(500000);
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        usleep(2500000);
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testExpireAdd()
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->add('expire_testa', 'expire_testa', 2));
        usleep(500000);
        $this->assertEquals('expire_testa', $cache->get('expire_testa'));
        usleep(2500000);
        $this->assertFalse($cache->get('expire_testa'));
    }

    public function testAdd()
    {
        $cache = $this->prepare();

        // should not change existing keys
        $this->assertFalse($cache->add('number_test', 13));
        $this->assertEquals(42, $cache->get('number_test'));

        // should store data if it's not there yet
        $this->assertFalse($cache->get('add_test'));
        $this->assertTrue($cache->add('add_test', 13));
        $this->assertEquals(13, $cache->get('add_test'));
    }

    public function testMadd()
    {
        $cache = $this->prepare();

        $this->assertFalse($cache->get('add_test'));

        $cache->madd([
            'number_test' => 13,
            'add_test' => 13,
        ]);

        $this->assertEquals(42, $cache->get('number_test'));
        $this->assertEquals(13, $cache->get('add_test'));
    }

    public function testDelete()
    {
        $cache = $this->prepare();

        $this->assertNotNull($cache->get('number_test'));
        $this->assertTrue($cache->delete('number_test'));
        $this->assertFalse($cache->get('number_test'));
    }

    public function testFlush()
    {
        $cache = $this->prepare();
        $this->assertTrue($cache->flush());
        $this->assertFalse($cache->get('number_test'));
    }
}

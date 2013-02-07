<?php
namespace yiiunit\framework\caching;
use yiiunit\TestCase;
use yii\caching\Cache;

/**
 * Base class for testing cache backends
 */
abstract class CacheTest extends TestCase
{
	/**
	 * @return Cache
	 */
	abstract protected function getCacheInstance();

	public function testSet()
	{
		$cache = $this->getCacheInstance();
		$cache->set('string_test', 'string_test');
		$cache->set('number_test', 42);
		$cache->set('array_test', array('array_test' => 'array_test'));
		$cache['arrayaccess_test'] = new \stdClass();
	}

	public function testGet()
	{
		$cache = $this->getCacheInstance();
		$this->assertEquals('string_test', $cache->get('string_test'));

		$this->assertEquals(42, $cache->get('number_test'));

		$array = $cache->get('array_test');
		$this->assertArrayHasKey('array_test', $array);
		$this->assertEquals('array_test', $array['array_test']);

		$this->assertInstanceOf('stdClass', $cache['arrayaccess_test']);
	}

	public function testMget()
	{
		$cache = $this->getCacheInstance();
		$this->assertEquals(array('string_test' => 'string_test', 'number_test' => 42), $cache->mget(array('string_test', 'number_test')));
	}

	public function testExpire()
	{
		$cache = $this->getCacheInstance();
		$cache->set('expire_test', 'expire_test', 2);
		sleep(1);
		$this->assertEquals('expire_test', $cache->get('expire_test'));
		sleep(2);
		$this->assertEquals(false, $cache->get('expire_test'));
	}

	public function testAdd()
	{
		$cache = $this->getCacheInstance();

		// should not change existing keys
		$cache->add('number_test', 13);
		$this->assertEquals(42, $cache->get('number_test'));

		// should store data is it's not there yet
		$cache->add('add_test', 13);
		$this->assertEquals(13, $cache->get('add_test'));
	}

	public function testDelete()
	{
		$cache = $this->getCacheInstance();

		$cache->delete('number_test');
		$this->assertEquals(null, $cache->get('number_test'));
	}

	public function testFlush()
	{
		$cache = $this->getCacheInstance();
		$cache->flush();
		$this->assertEquals(null, $cache->get('add_test'));
	}
}

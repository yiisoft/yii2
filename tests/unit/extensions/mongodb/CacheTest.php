<?php

namespace yiiunit\extensions\mongodb;

use Yii;
use yii\mongodb\Cache;

class CacheTest extends MongoDbTestCase
{
	/**
	 * @var string test cache collection name.
	 */
	protected static $cacheCollection = '_test_cache';

	protected function tearDown()
	{
		$this->dropCollection(static::$cacheCollection);
		parent::tearDown();
	}

	/**
	 * Creates test cache instance.
	 * @return Cache cache instance.
	 */
	protected function createCache()
	{
		return Yii::createObject([
			'class' => Cache::className(),
			'db' => $this->getConnection(),
			'cacheCollection' => static::$cacheCollection,
			'gcProbability' => 0,
		]);
	}

	// Tests:

	public function testSet()
	{
		$cache = $this->createCache();

		$key = 'test_key';
		$value = 'test_value';
		$this->assertTrue($cache->set($key, $value), 'Unable to set value!');
		$this->assertEquals($value, $cache->get($key), 'Unable to set value correctly!');

		$newValue = 'test_new_value';
		$this->assertTrue($cache->set($key, $newValue), 'Unable to update value!');
		$this->assertEquals($newValue, $cache->get($key), 'Unable to update value correctly!');
	}

	public function testAdd()
	{
		$cache = $this->createCache();

		$key = 'test_key';
		$value = 'test_value';
		$this->assertTrue($cache->add($key, $value), 'Unable to add value!');
		$this->assertEquals($value, $cache->get($key), 'Unable to add value correctly!');

		$newValue = 'test_new_value';
		$this->assertTrue($cache->add($key, $newValue), 'Unable to re-add value!');
		$this->assertEquals($value, $cache->get($key), 'Original value is lost!');
	}

	/**
	 * @depends testSet
	 */
	public function testDelete()
	{
		$cache = $this->createCache();

		$key = 'test_key';
		$value = 'test_value';
		$cache->set($key, $value);

		$this->assertTrue($cache->delete($key), 'Unable to delete key!');
		$this->assertEquals(false, $cache->get($key), 'Value is not deleted!');
	}

	/**
	 * @depends testSet
	 */
	public function testFlush()
	{
		$cache = $this->createCache();

		$cache->set('key1', 'value1');
		$cache->set('key2', 'value2');

		$this->assertTrue($cache->flush(), 'Unable to flush cache!');

		$collection = $cache->db->getCollection($cache->cacheCollection);
		$rows = $this->findAll($collection);
		$this->assertCount(0, $rows, 'Unable to flush records!');
	}

	/**
	 * @depends testSet
	 */
	public function testGc()
	{
		$cache = $this->createCache();

		$cache->set('key1', 'value1');
		$cache->set('key2', 'value2');

		$collection = $cache->db->getCollection($cache->cacheCollection);

		list($row) = $this->findAll($collection);
		$collection->update(['_id' => $row['_id']], ['expire' => time() - 10]);

		$cache->gc(true);

		$rows = $this->findAll($collection);
		$this->assertCount(1, $rows, 'Unable to collect garbage!');
	}

	/**
	 * @depends testSet
	 */
	public function testGetExpired()
	{
		$cache = $this->createCache();

		$key = 'test_key';
		$value = 'test_value';
		$cache->set($key, $value);

		$collection = $cache->db->getCollection($cache->cacheCollection);
		list($row) = $this->findAll($collection);
		$collection->update(['_id' => $row['_id']], ['expire' => time() - 10]);

		$this->assertEquals(false, $cache->get($key), 'Expired key value returned!');
	}
}
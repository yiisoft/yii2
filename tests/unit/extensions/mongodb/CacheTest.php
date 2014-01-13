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
		]);
	}

	// Tests:

	public function testSet()
	{
		$cache = $this->createCache();

		$key = 'test_key';
		$value = 'test_value';
		$this->assertTrue($cache->set($key, $value), 'Unable to set value!');
	}
}
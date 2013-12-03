<?php

namespace yiiunit\extensions\mongo;

use yii\helpers\FileHelper;
use yii\mongo\Connection;
use Yii;
use yii\mongo\Exception;
use yiiunit\TestCase;

class MongoTestCase extends TestCase
{
	/**
	 * @var array Mongo connection configuration.
	 */
	protected $mongoConfig = [
		'dsn' => 'mongodb://localhost:27017',
		'defaultDatabaseName' => 'yii2test',
		'options' => [],
	];
	/**
	 * @var Connection Mongo connection instance.
	 */
	protected $mongo;

	public static function setUpBeforeClass()
	{
		static::loadClassMap();
	}

	protected function setUp()
	{
		parent::setUp();
		if (!extension_loaded('mongo')) {
			$this->markTestSkipped('mongo extension required.');
		}
		$config = $this->getParam('mongo');
		if (!empty($config)) {
			$this->mongoConfig = $config;
		}
		$this->mockApplication();
		static::loadClassMap();
	}

	protected function tearDown()
	{
		if ($this->mongo) {
			$this->mongo->close();
		}
		$this->destroyApplication();
	}

	/**
	 * Adds sphinx extension files to [[Yii::$classPath]],
	 * avoiding the necessity of usage Composer autoloader.
	 */
	protected static function loadClassMap()
	{
		$baseNameSpace = 'yii/mongo';
		$basePath = realpath(__DIR__. '/../../../../extensions/mongo');
		$files = FileHelper::findFiles($basePath);
		foreach ($files as $file) {
			$classRelativePath = str_replace($basePath, '', $file);
			$classFullName = str_replace(['/', '.php'], ['\\', ''], $baseNameSpace . $classRelativePath);
			Yii::$classMap[$classFullName] = $file;
		}
	}

	/**
	 * @param boolean $reset whether to clean up the test database
	 * @param boolean $open whether to open test database
	 * @return \yii\mongo\Connection
	 */
	public function getConnection($reset = false, $open = true)
	{
		if (!$reset && $this->mongo) {
			return $this->mongo;
		}
		$db = new Connection;
		$db->dsn = $this->mongoConfig['dsn'];
		$db->defaultDatabaseName = $this->mongoConfig['defaultDatabaseName'];
		if (isset($this->mongoConfig['options'])) {
			$db->options = $this->mongoConfig['options'];
		}
		if ($open) {
			$db->open();
		}
		$this->mongo = $db;
		return $db;
	}

	/**
	 * Drops the specified collection.
	 * @param string $name collection name.
	 */
	protected function dropCollection($name)
	{
		if ($this->mongo) {
			try {
				$this->mongo->getCollection($name)->drop();
			} catch (Exception $e) {
				// shut down exception
			}
		}
	}

	/**
	 * Finds all records in collection.
	 * @param \yii\mongo\Collection $collection
	 * @param array $condition
	 * @param array $fields
	 * @return array rows
	 */
	protected function findAll($collection, $condition = [], $fields = [])
	{
		$cursor = $collection->find($condition, $fields);
		$result = [];
		foreach ($cursor as $data) {
			$result[] = $data;
		}
		return $result;
	}
}
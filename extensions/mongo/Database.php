<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Object;
use Yii;

/**
 * Database represents the Mongo database information.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Database extends Object
{
	/**
	 * @var \MongoDB Mongo database instance.
	 */
	public $mongoDb;
	/**
	 * @var Collection[] list of collections.
	 */
	private $_collections = [];

	/**
	 * Returns the Mongo collection with the given name.
	 * @param string $name collection name
	 * @param boolean $refresh whether to reload the table schema even if it is found in the cache.
	 * @return Collection mongo collection instance.
	 */
	public function getCollection($name, $refresh = false)
	{
		if ($refresh || !array_key_exists($name, $this->_collections)) {
			$this->_collections[$name] = $this->selectCollection($name);
		}
		return $this->_collections[$name];
	}

	/**
	 * Selects collection with given name.
	 * @param string $name collection name.
	 * @return Collection collection instance.
	 */
	protected function selectCollection($name)
	{
		return Yii::createObject([
			'class' => 'yii\mongo\Collection',
			'mongoCollection' => $this->mongoDb->selectCollection($name)
		]);
	}

	/**
	 * Creates new collection.
	 * Note: Mongo creates new collections automatically on the first demand,
	 * this method makes sense only for the migration script or for the case
	 * you need to create collection with the specific options.
	 * @param string $name name of the collection
	 * @param array $options collection options in format: "name" => "value"
	 * @return \MongoCollection new mongo collection instance.
	 */
	public function createCollection($name, $options = [])
	{
		return $this->mongoDb->createCollection($name, $options);
	}

	/**
	 * Executes Mongo command.
	 * @param array $command command specification.
	 * @param array $options options in format: "name" => "value"
	 * @return array database response.
	 */
	public function execute($command, $options = [])
	{
		return $this->mongoDb->command($command, $options);
	}
}
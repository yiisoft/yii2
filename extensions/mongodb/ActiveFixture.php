<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use Yii;
use yii\base\InvalidConfigException;
use yii\test\BaseActiveFixture;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|MongoDB ActiveRecord class]] or a [[collectionName|MongoDB collection]].
 *
 * Either [[modelClass]] or [[collectionName]] must be set. You should also provide fixture data in the file
 * specified by [[dataFile]] or overriding [[getData()]] if you want to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[resetCollection()]] to remove any existing data in the collection.
 * It will then populate the table with the data returned by [[getData()]].
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property. If you set [[modelClass]],
 * you will also be able to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends BaseActiveFixture
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 */
	public $db = 'mongodb';
	/**
	 * @var string|array the collection name that this fixture is about. If this property is not set,
	 * the table name will be determined via [[modelClass]].
	 * @see [[yii\mongodb\Connection::getCollection()]]
	 */
	public $collectionName;

	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->modelClass) && !isset($this->collectionName)) {
			throw new InvalidConfigException('Either "modelClass" or "collectionName" must be set.');
		}
	}

	/**
	 * Loads the fixture data.
	 * The default implementation will first reset the DB table and then populate it with the data
	 * returned by [[getData()]].
	 */
	public function load()
	{
		$this->resetCollection();
		$data = $this->getData();
		$this->getCollection()->batchInsert($data);
		foreach ($data as $alias => $row) {
			$this->data[$alias] = $row;
		}
	}

	protected function getCollection()
	{
		return $this->db->getCollection($this->getCollectionName());
	}

	protected function getCollectionName()
	{
		if ($this->collectionName) {
			return $this->collectionName;
		} else {
			/** @var ActiveRecord $modelClass */
			$modelClass = $this->modelClass;
			return $modelClass::collectionName();
		}
	}

	/**
	 * Returns the fixture data.
	 *
	 * This method is called by [[loadData()]] to get the needed fixture data.
	 *
	 * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
	 * The file should return an array of data rows (column name => column value), each corresponding to a row in the table.
	 *
	 * If the data file does not exist, an empty array will be returned.
	 *
	 * @return array the data rows to be inserted into the collection.
	 */
	protected function getData()
	{
		if ($this->dataFile === null) {
			$class = new \ReflectionClass($this);
			$dataFile = dirname($class->getFileName()) . '/data/' . $this->getCollectionName() . '.php';
			return is_file($dataFile) ? require($dataFile) : [];
		} else {
			return parent::getData();
		}
	}

	/**
	 * Removes all existing data from the specified collection and resets sequence number if any.
	 * This method is called before populating fixture data into the collection associated with this fixture.
	 */
	protected function resetCollection()
	{
		$this->getCollection()->remove();
	}
}

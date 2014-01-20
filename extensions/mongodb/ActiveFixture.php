<?php

namespace yii\mongodb;

use Yii;
use yii\base\InvalidConfigException;

class ActiveFixture extends \yii\test\BaseActiveFixture
{

	public $db = 'mongodb';
	/**
	 * @var string the name of the collection that this fixture is about. If this property is not set,
	 * the table name will be determined via [[modelClass]].
	 * @see modelClass
	 */
	public $collectionName;

	/**
	 * @var string the file path or path alias of the data file that contains the fixture data
	 * and will be loaded by [[loadData()]]. If this is not set, it will default to `FixturePath/data/TableName.php`,
	 * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
	 * name of the table associated with this fixture.
	 */
	public $dataFile;

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
		$this->getCollection()->batchInsert($this->getData());
		foreach ($this->getData() as $alias => $row) {
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
		if ($this->dataFile === false) {
			return [];
		}
		if ($this->dataFile !== null) {
			$dataFile = Yii::getAlias($this->dataFile);
		} else {
			$class = new \ReflectionClass($this);
			$dataFile = dirname($class->getFileName()) . '/data/' . $this->getCollectionName() . '.php';
		}
		return is_file($dataFile) ? require($dataFile) : [];
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
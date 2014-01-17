<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\TableSchema;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|ActiveRecord class]] or a [[tableName|database table]].
 *
 * Either [[modelClass]] or [[tableName]] must be set. When loading an ActiveFixture, the corresponding
 * database table will be [[resetTable()|reset]] first. It will then be populated with the data loaded by [[loadData()]].
 *
 * You can access the loaded data via the [[rows]] property. If you set [[modelClass]], you will also be able
 * to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @inheritdoc
	 */
	public $depends = ['yii\test\DbFixture'];
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the ActiveFixture object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var string the AR model class associated with this fixture.
	 * @see tableName
	 */
	public $modelClass;
	/**
	 * @var string the name of the database table that this fixture is about. If this property is not set,
	 * the table name will be determined via [[modelClass]].
	 * @see modelClass
	 */
	public $tableName;
	/**
	 * @var string|boolean the file path or path alias of the data file that contains the fixture data
	 * and will be loaded by [[loadData()]]. If this is not set, it will default to `FixturePath/data/TableName.php`,
	 * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
	 * name of the table associated with this fixture. You may set this property to be false to disable loading data.
	 */
	public $dataFile;
	/**
	 * @var array the data rows. Each array element represents one row of data (column name => column value).
	 */
	public $rows;
	/**
	 * @var TableSchema the table schema for the table associated with this fixture
	 */
	private $_table;
	/**
	 * @var \yii\db\ActiveRecord[] the loaded AR models
	 */
	private $_models;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->modelClass) && !isset($this->tableName)) {
			throw new InvalidConfigException('Either "modelClass" or "tableName" must be set.');
		}
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("The 'db' property must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function load()
	{
		$this->initSchema();

 		$table = $this->getTableSchema();
		$this->resetTable();
		$this->rows = [];
		foreach ($this->loadData() as $alias => $row) {
			$this->db->createCommand()->insert($table->fullName, $row)->execute();
			if ($table->sequenceName !== null) {
				foreach ($table->primaryKey as $pk) {
					if (!isset($row[$pk])) {
						$row[$pk] = $this->db->getLastInsertID($table->sequenceName);
						break;
					}
				}
			}
			$this->rows[$alias] = $row;
		}
	}

	/**
	 * Returns the AR model by the specified model name.
	 * A model name is the key of the corresponding data row returned by [[loadData()]].
	 * @param string $name the model name.
	 * @return null|\yii\db\ActiveRecord the AR model, or null if the model cannot be found in the database
	 * @throws \yii\base\InvalidConfigException if [[modelClass]] is not set.
	 */
	public function getModel($name)
	{
		if (!isset($this->rows[$name])) {
			return null;
		}
		if (array_key_exists($name, $this->_models)) {
			return $this->_models[$name];
		}

		if ($this->modelClass === null) {
			throw new InvalidConfigException('The "modelClass" property must be set.');
		}
		$row = $this->rows[$name];
		/** @var \yii\db\ActiveRecord $modelClass */
		$modelClass = $this->modelClass;
		/** @var \yii\db\ActiveRecord $model */
		$model = new $modelClass;
		$keys = [];
		foreach ($model->primaryKey() as $key) {
			$keys[$key] = isset($row[$key]) ? $row[$key] : null;
		}
		return $this->_models[$name] = $modelClass::find($keys);
	}

	/**
	 * @return TableSchema the schema information of the database table associated with this fixture.
	 * @throws \yii\base\InvalidConfigException if the table does not exist
	 */
	public function getTableSchema()
	{
		if ($this->_table !== null) {
			return $this->_table;
		}

		$db = $this->db;
		$tableName = $this->tableName;
		if ($tableName === null) {
			/** @var \yii\db\ActiveRecord $modelClass */
			$modelClass = $this->modelClass;
			$tableName = $modelClass::tableName();
		}

		$this->_table = $db->getSchema()->getTableSchema($tableName);
		if ($this->_table === null) {
			throw new InvalidConfigException("Table does not exist: {$tableName}");
		}

		return $this->_table;
	}

	/**
	 * Initializes the database schema.
	 * This method is called by [[load()]] before loading data.
	 * You may override this method to prepare necessary database schema changes.
	 */
	protected function initSchema()
	{
	}

	/**
	 * Loads fixture data.
	 *
	 * The default implementation will try to load data by including the external file specified by [[dataFile]].
	 * The file should return an array of data rows (column name => column value), each corresponding to a row in the table.
	 *
	 * If the data file does not exist, an empty array will be returned.
	 *
	 * @return array the data rows to be inserted into the database table.
	 */
	protected function loadData()
	{
		if ($this->dataFile === false) {
			return [];
		}
		if ($this->dataFile !== null) {
			$dataFile = Yii::getAlias($this->dataFile);
		} else {
			$class = new \ReflectionClass($this);
			$dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableSchema()->fullName . '.php';
		}
		return is_file($dataFile) ? require($dataFile) : [];
	}

	/**
	 * Removes all existing data from the specified table and resets sequence number if any.
	 * This method is called before populating fixture data into the table associated with this fixture.
	 */
	protected function resetTable()
	{
		$table = $this->getTableSchema();
		$this->db->createCommand()->delete($table->fullName)->execute();
		if ($table->sequenceName !== null) {
			$this->db->createCommand()->resetSequence($table->fullName, 1)->execute();
		}
	}

	/**
	 * Returns an iterator for traversing the cookies in the collection.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the collection.
	 * @return \ArrayIterator an iterator for traversing the cookies in the collection.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->rows);
	}

	/**
	 * Returns the number of items in the session.
	 * This method is required by Countable interface.
	 * @return integer number of items in the session.
	 */
	public function count()
	{
		return count($this->rows);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->rows[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return isset($this->rows[$offset]) ? $this->rows[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->rows[$offset] = $item;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->rows[$offset]);
	}
}

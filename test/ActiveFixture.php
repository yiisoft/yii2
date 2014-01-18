<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;
use yii\db\TableSchema;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|ActiveRecord class]] or a [[tableName|database table]].
 *
 * Either [[modelClass]] or [[tableName]] must be set. And you should normally override [[loadSchema()]]
 * to set up the necessary database schema (e.g. creating the table, view, trigger, etc.)
 * You should also provide fixture data in the file specified by [[dataFile]] or overriding [[loadData()]] if you want
 * to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[loadSchema()]] to initialize the database schema.
 * It will then call [[loadData()]] to populate the table with the fixture data.
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property. If you set [[modelClass]],
 * you will also be able to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends BaseActiveFixture
{
	/**
	 * @var string the name of the database table that this fixture is about. If this property is not set,
	 * the table name will be determined via [[modelClass]].
	 * @see modelClass
	 */
	public $tableName;
	/**
	 * @var string the file path or path alias of the data file that contains the fixture data
	 * and will be loaded by [[loadData()]]. If this is not set, it will default to `FixturePath/data/TableName.php`,
	 * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
	 * name of the table associated with this fixture.
	 */
	public $dataFile;
	/**
	 * @var boolean whether to reset the table associated with this fixture.
	 * By setting this property to be true, when [[loadData()]] is called, all existing data in the table
	 * will be removed and the sequence number (if any) will be reset.
	 *
	 * Note that you normally do not need to reset the table if you implement [[loadSchema()]] because
	 * there will be no existing data.
	 */
	public $resetTable = false;
	/**
	 * @var TableSchema the table schema for the table associated with this fixture
	 */
	private $_table;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		if (!isset($this->modelClass) && !isset($this->tableName)) {
			throw new InvalidConfigException('Either "modelClass" or "tableName" must be set.');
		}
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
	 * Loads the fixture data.
	 * The default implementation will first reset the DB table and then populate it with the data
	 * returned by [[getData()]].
	 */
	protected function loadData()
	{
		$table = $this->getTableSchema();
		if ($this->resetTable) {
			$this->resetTable();
		}
		foreach ($this->getData() as $alias => $row) {
			$this->db->createCommand()->insert($table->fullName, $row)->execute();
			if ($table->sequenceName !== null) {
				foreach ($table->primaryKey as $pk) {
					if (!isset($row[$pk])) {
						$row[$pk] = $this->db->getLastInsertID($table->sequenceName);
						break;
					}
				}
			}
			$this->data[$alias] = $row;
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
	 * @return array the data rows to be inserted into the database table.
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
}

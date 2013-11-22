<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * DbFixtureManager manages database fixtures during tests.
 *
 * A fixture represents a list of rows for a specific table. For a test method,
 * using a fixture means that at the beginning of the method, the table has and only
 * has the rows that are given in the fixture. Therefore, the table's state is
 * predictable.
 *
 * A fixture is represented as a PHP script whose name (without suffix) is the
 * same as the table name (if schema name is needed, it should be prefixed to
 * the table name). The PHP script returns an array representing a list of table
 * rows. Each row is an associative array of column values indexed by column names.
 *
 * Fixtures must be stored under the [[basePath]] directory. The directory
 * may contain a file named `init.php` which will be executed before any fixture is loaded.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbFixtureManager extends Component
{
	/**
	 * @var string the init script file that should be executed before running each test.
	 * This should be a path relative to [[basePath]].
	 */
	public $initScript = 'init.php';
	/**
	 * @var string the base path containing all fixtures. This can be either a directory path or path alias.
	 */
	public $basePath = '@app/tests/fixtures';
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbFixtureManager object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var array list of database schemas that the test tables may reside in. Defaults to
	 * array(''), meaning using the default schema (an empty string refers to the
	 * default schema). This property is mainly used when turning on and off integrity checks
	 * so that fixture data can be populated into the database without causing problem.
	 */
	public $schemas = [''];

	private $_rows; // fixture name, row alias => row
	private $_models; // fixture name, row alias => record (or class name)
	private $_modelClasses;


	/**
	 * Loads the specified fixtures.
	 *
	 * This method does the following things to load the fixtures:
	 *
	 * - Run [[initScript]] if any.
	 * - Clean up data and models loaded in memory previously.
	 * - Load each specified fixture by calling [[loadFixture()]].
	 *
	 * @param array $fixtures a list of fixtures (fixture name => table name or AR class name) to be loaded.
	 * Each array element can be either a table name (with schema prefix if needed), or a fully-qualified
	 * ActiveRecord class name (e.g. `app\models\Post`). An element can be associated with a key
	 * which will be treated as the fixture name.
	 * @return array the loaded fixture data (fixture name => table rows)
	 * @throws InvalidConfigException if a model class specifying a fixture is not an ActiveRecord class.
	 */
	public function load(array $fixtures = [])
	{
		$this->basePath = Yii::getAlias($this->basePath);

		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("The 'db' property must be either a DB connection instance or the application component ID of a DB connection.");
		}

		foreach ($fixtures as $name => $fixture) {
			if (strpos($fixture, '\\') !== false) {
				$model = new $fixture;
				if ($model instanceof ActiveRecord) {
					$this->_modelClasses[$name] = $fixture;
					$fixtures[$name] = $model->getTableSchema()->name;
				} else {
					throw new InvalidConfigException("Fixture '$fixture' must be an ActiveRecord class.");
				}
			}
		}

		$this->_modelClasses = $this->_rows = $this->_models = [];

		$this->checkIntegrity(false);

		if (!empty($this->initScript)) {
			$initFile = $this->basePath . '/' . $this->initScript;
			if (is_file($initFile)) {
				require($initFile);
			}
		}

		foreach ($fixtures as $name => $tableName) {
			$rows = $this->loadFixture($tableName);
			if (is_array($rows)) {
				$this->_rows[$name] = $rows;
			}
		}
		$this->checkIntegrity(true);
		return $this->_rows;
	}

	/**
	 * Loads the fixture for the specified table.
	 *
	 * This method does the following tasks to load the fixture for a table:
	 *
	 * - Remove existing rows in the table.
	 * - If there is any auto-incremental column, the corresponding sequence will be reset to 0.
	 * - If a fixture file is found, it will be executed, and its return value will be treated
	 *   as rows which will then be inserted into the table.
	 *
	 * @param string $tableName table name
	 * @return array|boolean the loaded fixture rows indexed by row aliases (if any).
	 * False is returned if the table does not have a fixture.
	 * @throws InvalidConfigException if the specified table does not exist
	 */
	public function loadFixture($tableName)
	{
		$table = $this->db->getSchema()->getTableSchema($tableName);
		if ($table === null) {
			throw new InvalidConfigException("Table does not exist: $tableName");
		}

		$this->db->createCommand()->truncateTable($tableName);

		$fileName = $this->basePath . '/' . $tableName . '.php';
		if (!is_file($fileName)) {
			return false;
		}

		$rows = [];
		foreach (require($fileName) as $alias => $row) {
			$this->db->createCommand()->insert($tableName, $row)->execute();
			if ($table->sequenceName !== null) {
				foreach ($table->primaryKey as $pk) {
					if (!isset($row[$pk])) {
						$row[$pk] = $this->db->getLastInsertID($table->sequenceName);
						break;
					}
				}
			}
			$rows[$alias] = $row;
		}

		return $rows;
	}

	/**
	 * Returns the fixture data rows.
	 * The rows will have updated primary key values if the primary key is auto-incremental.
	 * @param string $fixtureName the fixture name
	 * @return array the fixture data rows. False is returned if there is no such fixture data.
	 */
	public function getRows($fixtureName)
	{
		return isset($this->_rows[$fixtureName]) ? $this->_rows[$fixtureName] : false;
	}

	/**
	 * Returns the specified ActiveRecord instance in the fixture data.
	 * @param string $fixtureName the fixture name
	 * @param string $modelName the alias for the fixture data row
	 * @return \yii\db\ActiveRecord the ActiveRecord instance. Null is returned if there is no such fixture row.
	 */
	public function getModel($fixtureName, $modelName)
	{
		if (!isset($this->_modelClasses[$fixtureName]) || !isset($this->_rows[$fixtureName][$modelName])) {
			return null;
		}
		if (isset($this->_models[$fixtureName][$modelName])) {
			return $this->_models[$fixtureName][$modelName];
		}
		$row = $this->_rows[$fixtureName][$modelName];
		/** @var \yii\db\ActiveRecord $modelClass */
		$modelClass = $this->_models[$fixtureName];
		/** @var \yii\db\ActiveRecord $model */
		$model = new $modelClass;
		$keys = [];
		foreach ($model->primaryKey() as $key) {
			$keys[$key] = isset($row[$key]) ? $row[$key] : null;
		}
		return $this->_models[$fixtureName][$modelName] = $modelClass::find($keys);
	}

	/**
	 * Enables or disables database integrity check.
	 * This method may be used to temporarily turn off foreign constraints check.
	 * @param boolean $check whether to enable database integrity check
	 */
	public function checkIntegrity($check)
	{
		foreach ($this->schemas as $schema) {
			$this->db->createCommand()->checkIntegrity($check, $schema);
		}
	}
}

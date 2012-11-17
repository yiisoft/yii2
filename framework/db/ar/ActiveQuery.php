<?php
/**
 * ActiveFinder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\db\dao\BaseQuery;
use yii\base\VectorIterator;
use yii\db\dao\Expression;
use yii\db\Exception;

class ActiveQuery extends BaseQuery
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var array list of relations that this query should be performed with
	 */
	public $with = array();
	/**
	 * @var string the name of the column that the result should be indexed by.
	 * This is only useful when the query result is returned as an array.
	 */
	public $index;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var array list of scopes that should be applied to this query
	 */
	public $scopes = array();
	/**
	 * @var string the SQL statement to be executed for retrieving AR records.
	 * This is set by [[ActiveRecord::findBySql()]].
	 */
	public $sql;

	public function __call($name, $params)
	{
		if (method_exists($this->modelClass, $name)) {
			$this->scopes[$name] = $params;
			return $this;
		} else {
			return parent::__call($name, $params);
		}
	}

	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		return $this->find();
	}

	/**
	 * Executes query and returns a single row of result.
	 * @return null|array|ActiveRecord the single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one()
	{
		$records = $this->find();
		return isset($records[0]) ? $records[0] : null;
	}

	/**
	 * Returns a scalar value for this query.
	 * The value returned will be the first column in the first row of the query results.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if there is no value.
	 */
	public function value()
	{
		return $this->createFinder()->find($this, true);
	}

	/**
	 * Executes query and returns if matching row exists in the table.
	 * @return bool if row exists in the table.
	 */
	public function exists()
	{
		return $this->select(array(new Expression('1')))->value() !== false;
	}

	/**
	 * Returns the database connection used by this query.
	 * This method returns the connection used by the [[modelClass]].
	 * @return \yii\db\dao\Connection the database connection used by this query
	 */
	public function getDbConnection()
	{
		$class = $this->modelClass;
		return $class::getDbConnection();
	}

	/**
	 * Sets the parameters about query caching.
	 * This is a shortcut method to {@link CDbConnection::cache()}.
	 * It changes the query caching parameter of the {@link dbConnection} instance.
	 * @param integer $duration the number of seconds that query results may remain valid in cache.
	 * If this is 0, the caching will be disabled.
	 * @param \yii\caching\Dependency $dependency the dependency that will be used when saving the query results into cache.
	 * @param integer $queryCount number of SQL queries that need to be cached after calling this method. Defaults to 1,
	 * meaning that the next SQL query will be cached.
	 * @return ActiveRecord the active record instance itself.
	 */
	public function cache($duration, $dependency = null, $queryCount = 1)
	{
		$this->getDbConnection()->cache($duration, $dependency, $queryCount);
		return $this;
	}

	protected function find()
	{
		$modelClass = $this->modelClass;
		/**
		 * @var ActiveRecord $model
		 */
		$model = $modelClass::model();
		/**
		 * @var \yii\db\dao\Connection $db
		 */
		$db = $model->getDbConnection();
		if ($this->sql === null) {
			if ($this->from === null) {
				$tableName = $model->getTableSchema()->name;
				$this->from = array($tableName);
			}
			foreach ($this->scopes as $name => $config) {
				if (is_integer($name)) {
					$model->$config($this);
				} else {
					array_unshift($config, $this);
					call_user_func_array(array($model, $name), $config);
				}
			}
			$this->sql = $db->getQueryBuilder()->build($this);
		}
		$command = $db->createCommand($this->sql, $this->params);
		$rows = $command->queryAll();
		$records = $this->createRecords($rows);

		foreach ($this->with as $name => $config) {
			$relation = $model->$name();
			foreach ($config as $p => $v) {
				$relation->$p = $v;
			}
			$relation->findWith($records);
		}

		return $records;
	}

	protected function createRecords($rows)
	{
		$records = array();
		if ($this->asArray) {
			if ($this->index === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$records[$row[$this->index]] = $row;
			}
		} else {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			if ($this->index === null) {
				foreach ($rows as $row) {
					$records[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$records[$row[$this->index]] = $class::create($row);
				}
			}
		}
		return $records;
	}
}

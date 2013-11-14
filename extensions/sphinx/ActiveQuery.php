<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

/**
 * Class ActiveQuery
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends Query
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var array list of relations that this query should be performed with
	 */
	public $with;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var string the SQL statement to be executed for retrieving AR records.
	 * This is set by [[ActiveRecord::findBySql()]].
	 */
	public $sql;

	/**
	 * PHP magic method.
	 * This method allows calling static method defined in [[modelClass]] via this query object.
	 * It is mainly implemented for supporting the feature of scope.
	 * @param string $name the method name to be called
	 * @param array $params the parameters passed to the method
	 * @return mixed the method return result
	 */
	public function __call($name, $params)
	{
		if (method_exists($this->modelClass, $name)) {
			array_unshift($params, $this);
			call_user_func_array([$this->modelClass, $name], $params);
			return $this;
		} else {
			return parent::__call($name, $params);
		}
	}

	/**
	 * Executes query and returns all results as an array.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$command = $this->createCommand($db);
		$rows = $command->queryAll();
		if (!empty($rows)) {
			$models = $this->createModels($rows);
			// TODO relations
			/*if (!empty($this->with)) {
				$this->populateRelations($models, $this->with);
			}*/
			return $models;
		} else {
			return [];
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one($db = null)
	{
		$command = $this->createCommand($db);
		$row = $command->queryOne();
		if ($row !== false) {
			if ($this->asArray) {
				$model = $row;
			} else {
				/** @var $class ActiveRecord */
				$class = $this->modelClass;
				$model = $class::create($row);
			}
			// TODO relations
			/*if (!empty($this->with)) {
				$models = [$model];
				$this->populateRelations($models, $this->with);
				$model = $models[0];
			}*/
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the DB connection used to create the DB command.
	 * If null, the DB connection returned by [[modelClass]] will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		/** @var $modelClass ActiveRecord */
		$modelClass = $this->modelClass;
		if ($db === null) {
			$db = $modelClass::getDb();
		}

		$params = $this->params;
		if ($this->sql === null) {
			if ($this->from === null) {
				$tableName = $modelClass::indexName();
				if ($this->select === null && !empty($this->join)) {
					$this->select = ["$tableName.*"];
				}
				$this->from = [$tableName];
			}
			list ($this->sql, $params) = $db->getQueryBuilder()->build($this);
		}
		return $db->createCommand($this->sql, $params);
	}

	/**
	 * Sets the [[asArray]] property.
	 * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
	 * @return static the query object itself
	 */
	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	/**
	 * Sets the [[indexBy]] property.
	 * @param string|callable $column the name of the column by which the query results should be indexed by.
	 * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
	 * row or model data. The signature of the callable should be:
	 *
	 * ~~~
	 * // $model is an AR instance when `asArray` is false,
	 * // or an array of column values when `asArray` is true.
	 * function ($model)
	 * {
	 *     // return the index value corresponding to $model
	 * }
	 * ~~~
	 *
	 * @return static the query object itself
	 */
	public function indexBy($column)
	{
		return parent::indexBy($column);
	}

	private function createModels($rows)
	{
		$models = [];
		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				if (is_string($this->indexBy)) {
					$key = $row[$this->indexBy];
				} else {
					$key = call_user_func($this->indexBy, $row);
				}
				$models[$key] = $row;
			}
		} else {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			if ($this->indexBy === null) {
				foreach ($rows as $row) {
					$models[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$model = $class::create($row);
					if (is_string($this->indexBy)) {
						$key = $model->{$this->indexBy};
					} else {
						$key = call_user_func($this->indexBy, $model);
					}
					$models[$key] = $model;
				}
			}
		}
		return $models;
	}
}
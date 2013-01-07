<?php
/**
 * ActiveFinder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\db\Connection;
use yii\db\Command;
use yii\db\QueryBuilder;
use yii\base\VectorIterator;
use yii\db\Expression;
use yii\db\Exception;

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
	 * @var string the name of the column by which the query result should be indexed.
	 * This is only used when the query result is returned as an array when calling [[all()]].
	 */
	public $indexBy;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var array list of scopes that should be applied to this query
	 */
	public $scopes;
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
		$command = $this->createCommand();
		$rows = $command->queryAll();
		if ($rows !== array()) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->fetchRelatedModels($models, $this->with);
			}
			return $models;
		} else {
			return array();
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one()
	{
		$command = $this->createCommand();
		$row = $command->queryRow();
		if ($row !== false && !$this->asArray) {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			$model = $class::create($row);
			if (!empty($this->with)) {
				$models = array($model);
				$this->fetchRelatedModels($models, $this->with);
				$model = $models[0];
			}
			return $model;
		} else {
			return $row === false ? null : $row;
		}
	}

	/**
	 * Returns a scalar value for this query.
	 * The value returned will be the first column in the first row of the query results.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if there is no value.
	 */
	public function value()
	{
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Executes query and returns if matching row exists in the table.
	 * @return bool if row exists in the table.
	 */
	public function exists()
	{
		$this->select = array(new Expression('1'));
		return $this->value() !== false;
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
			$db = $modelClass::getDbConnection();
		}
		if ($this->sql === null) {
			if ($this->from === null) {
				$tableName = $modelClass::tableName();
				$this->from = array($tableName);
			}
			if (!empty($this->scopes)) {
				foreach ($this->scopes as $name => $config) {
					if (is_integer($name)) {
						$modelClass::$config($this);
					} else {
						array_unshift($config, $this);
						call_user_func_array(array($modelClass, $name), $config);
					}
				}
			}
			/** @var $qb QueryBuilder */
			$qb = $db->getQueryBuilder();
			$this->sql = $qb->build($this);
		}
		return $db->createCommand($this->sql, $this->params);
	}

	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	public function with()
	{
		$this->with = func_get_args();
		if (isset($this->with[0]) && is_array($this->with[0])) {
			// the parameter is given as an array
			$this->with = $this->with[0];
		}
		return $this;
	}

	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
	}

	public function scopes($names)
	{
		$this->scopes = $names;
		return $this;
	}

	protected function createModels($rows)
	{
		$models = array();
		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$models[$row[$this->indexBy]] = $row;
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
					$models[$model->{$this->indexBy}] = $model;
				}
			}
		}
		return $models;
	}

	protected function fetchRelatedModels(&$models, $with)
	{
		$primaryModel = new $this->modelClass;
		$relations = $this->normalizeRelations($primaryModel, $with);
		foreach ($relations as $name => $relation) {
			if ($relation->asArray === null) {
				// inherit asArray from primary query
				$relation->asArray = $this->asArray;
			}
			$relation->findWith($name, $models);
		}
	}

	/**
	 * @param ActiveRecord $model
	 * @param array $with
	 * @return ActiveRelation[]
	 * @throws \yii\db\Exception
	 */
	protected function normalizeRelations($model, $with)
	{
		$relations = array();
		foreach ($with as $name => $options) {
			if (is_integer($name)) {
				$name = $options;
				$options = array();
			}
			if (($pos = strpos($name, '.')) !== false) {
				// with sub-relations
				$childName = substr($name, $pos + 1);
				$name = substr($name, 0, $pos);
			} else {
				$childName = null;
			}

			if (!isset($relations[$name])) {
				if (!method_exists($model, $name)) {
					throw new Exception("Unknown relation: $name");
				}
				/** @var $relation ActiveRelation */
				$relation = $model->$name();
				$relation->primaryModel = null;
				$relations[$name] = $relation;
			} else {
				$relation = $relations[$name];
			}

			if (isset($childName)) {
				if (isset($relation->with[$childName])) {
					$relation->with[$childName] = array_merge($relation->with, $options);
				} else {
					$relation->with[$childName] = $options;
				}
			} else {
				foreach ($options as $p => $v) {
					$relation->$p = $v;
				}
			}
		}
		return $relations;
	}
}

<?php
/**
 * ActiveRecord class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\redis;

/**
 * ActiveQuery represents a DB query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[yii\db\redis\ActiveRecord::find()]]
 * and [[yii\db\redis\ActiveRecord::count()]].
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[sum()]]: returns the sum over the specified column.
 * - [[average()]]: returns the average over the specified column.
 * - [[min()]]: returns the min over the specified column.
 * - [[max()]]: returns the max over the specified column.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * You can use query methods, such as [[limit()]], [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ~~~
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends \yii\base\Component
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
	 * @var string the name of the column by which query results should be indexed by.
	 * This is only used when the query result is returned as an array when calling [[all()]].
	 */
	public $indexBy;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var integer maximum number of records to be returned. If not set or less than 0, it means no limit.
	 */
	public $limit;
	/**
	 * @var integer zero-based offset from where the records are to be returned. If not set or
	 * less than 0, it means starting from the beginning.
	 */
	public $offset;
	/**
	 * @var string|array how to sort the query results. This refers to the ORDER BY clause in a SQL statement.
	 * It can be either a string (e.g. `'id ASC, name DESC'`) or an array (e.g. `array('id ASC', 'name DESC')`).
	 */
	public $orderBy;

	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		// TODO implement
		$command = $this->createCommand();
		$rows = $command->queryAll();
		if ($rows !== array()) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->populateRelations($models, $this->with);
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
		// TODO implement
		$command = $this->createCommand();
		$row = $command->queryRow();
		if ($row !== false && !$this->asArray) {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			$model = $class::create($row);
			if (!empty($this->with)) {
				$models = array($model);
				$this->populateRelations($models, $this->with);
				$model = $models[0];
			}
			return $model;
		} else {
			return $row === false ? null : $row;
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names.
	 * @return integer number of records
	 */
	public function count($q = '*')
	{
		// TODO implement
		$this->select = array("COUNT($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the sum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the sum of the specified column values
	 */
	public function sum($q)
	{
		// TODO implement
		$this->select = array("SUM($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the average of the specified column values.
	 */
	public function average($q)
	{
		// TODO implement
		$this->select = array("AVG($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($q)
	{
		// TODO implement
		$this->select = array("MIN($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($q)
	{
		// TODO implement
		$this->select = array("MAX($q)");
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar()
	{
		// TODO implement
		return $this->createCommand()->queryScalar();
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists()
	{
		// TODO implement
		$this->select = array(new Expression('1'));
		return $this->scalar() !== false;
	}


	/**
	 * Sets the [[asArray]] property.
	 * TODO: refactor, it is duplicated from yii/db/ActiveQuery
	 * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
	 * @return ActiveQuery the query object itself
	 */
	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * TODO: refactor, it is duplicated from yii/db/Query
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
	 * @see addOrder()
	 */
	public function orderBy($columns)
	{
		$this->orderBy = $columns;
		return $this;
	}

	/**
	 * Adds additional ORDER BY columns to the query.
	 * TODO: refactor, it is duplicated from yii/db/Query
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
	 * @see order()
	 */
	public function addOrderBy($columns)
	{
		if (empty($this->orderBy)) {
			$this->orderBy = $columns;
		} else {
			if (!is_array($this->orderBy)) {
				$this->orderBy = preg_split('/\s*,\s*/', trim($this->orderBy), -1, PREG_SPLIT_NO_EMPTY);
			}
			if (!is_array($columns)) {
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}
			$this->orderBy = array_merge($this->orderBy, $columns);
		}
		return $this;
	}

	/**
	 * Sets the LIMIT part of the query.
	 * TODO: refactor, it is duplicated from yii/db/Query
	 * @param integer $limit the limit
	 * @return Query the query object itself
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * TODO: refactor, it is duplicated from yii/db/Query
	 * @param integer $offset the offset
	 * @return Query the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Specifies the relations with which this query should be performed.
	 *
	 * The parameters to this method can be either one or multiple strings, or a single array
	 * of relation names and the optional callbacks to customize the relations.
	 *
	 * The followings are some usage examples:
	 *
	 * ~~~
	 * // find customers together with their orders and country
	 * Customer::find()->with('orders', 'country')->all();
	 * // find customers together with their country and orders of status 1
	 * Customer::find()->with(array(
	 *     'orders' => function($query) {
	 *         $query->andWhere('status = 1');
	 *     },
	 *     'country',
	 * ))->all();
	 * ~~~
	 *
	 * TODO: refactor, it is duplicated from yii/db/ActiveQuery
	 * @return ActiveQuery the query object itself
	 */
	public function with()
	{
		$this->with = func_get_args();
		if (isset($this->with[0]) && is_array($this->with[0])) {
			// the parameter is given as an array
			$this->with = $this->with[0];
		}
		return $this;
	}

	/**
	 * Sets the [[indexBy]] property.
	 * TODO: refactor, it is duplicated from yii/db/ActiveQuery
	 * @param string $column the name of the column by which the query results should be indexed by.
	 * @return ActiveQuery the query object itself
	 */
	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
	}

	// TODO: refactor, it is duplicated from yii/db/ActiveQuery
	private function createModels($rows)
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

	// TODO: refactor, it is duplicated from yii/db/ActiveQuery
	private function populateRelations(&$models, $with)
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
	 * TODO: refactor, it is duplicated from yii/db/ActiveQuery
	 * @param ActiveRecord $model
	 * @param array $with
	 * @return ActiveRelation[]
	 */
	private function normalizeRelations($model, $with)
	{
		$relations = array();
		foreach ($with as $name => $callback) {
			if (is_integer($name)) {
				$name = $callback;
				$callback = null;
			}
			if (($pos = strpos($name, '.')) !== false) {
				// with sub-relations
				$childName = substr($name, $pos + 1);
				$name = substr($name, 0, $pos);
			} else {
				$childName = null;
			}

			$t = strtolower($name);
			if (!isset($relations[$t])) {
				$relation = $model->getRelation($name);
				$relation->primaryModel = null;
				$relations[$t] = $relation;
			} else {
				$relation = $relations[$t];
			}

			if (isset($childName)) {
				$relation->with[$childName] = $callback;
			} elseif ($callback !== null) {
				call_user_func($callback, $relation);
			}
		}
		return $relations;
	}
}

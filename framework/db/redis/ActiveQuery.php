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
	 * @var integer zero-based offset from where the records are to be returned.
	 * If not set, it means starting from the beginning.
	 * If less than zero it means starting n elements from the end.
	 */
	public $offset;
	/**
	 * @var array array of primary keys of the records to find.
	 */
	public $primaryKeys;

	/**
	 * List of multiple pks must be zero based
	 *
	 * @param $primaryKeys
	 * @return ActiveQuery
	 */
	public function primaryKeys($primaryKeys) {
		if (is_array($primaryKeys) && isset($primaryKeys[0])) {
			$this->primaryKeys = $primaryKeys;
		} else {
			$this->primaryKeys = array($primaryKeys);
		}

		return $this;
	}

	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		if (($primaryKeys = $this->primaryKeys) === null) {
			$start = $this->offset === null ? 0 : $this->offset;
			$end = $this->limit === null ? -1 : $start + $this->limit;
			$primaryKeys = $db->executeCommand('LRANGE', array($modelClass::tableName(), $start, $end));
		}
		$rows = array();
		foreach($primaryKeys as $pk) {
			$key = $modelClass::tableName() . ':a:' . (is_array($pk) ? implode('-', $pk) : $pk); // TODO escape PK glue
			// get attributes
			$data = $db->executeCommand('HGETALL', array($key));
			$row = array();
			for($i=0;$i<count($data);) {
				$row[$data[$i++]] = $data[$i++];
			}
			$rows[] = $row;
		}
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
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		if (($primaryKeys = $this->primaryKeys) === null) {
			$start = $this->offset === null ? 0 : $this->offset;
			$primaryKeys = $db->executeCommand('LRANGE', array($modelClass::tableName(), $start, $start + 1));
		}
		$pk = reset($primaryKeys);
		$key = $modelClass::tableName() . ':a:' . (is_array($pk) ? implode('-', $pk) : $pk); // TODO escape PK glue
		// get attributes
		$data = $db->executeCommand('HGETALL', array($key));
		if ($data === array()) {
			return null;
		}
		$row = array();
		for($i=0;$i<count($data);) {
			$row[$data[$i++]] = $data[$i++];
		}
		if (!$this->asArray) {
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
			return $row;
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names.
	 * @return integer number of records
	 */
	public function count()
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		return $db->executeCommand('LLEN', array($modelClass::tableName()));
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar($column)
	{
		$record = $this->one();
		return $record->$column;
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists()
	{
		return $this->one() !== null;
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

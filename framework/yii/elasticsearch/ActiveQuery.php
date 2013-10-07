<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\MultiTransferException;
use yii\base\NotSupportedException;
use yii\db\Exception;
use yii\helpers\Json;

/**
 * ActiveQuery represents a query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[ActiveRecord::find()]]
 * and [[ActiveRecord::count()]].
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
 * You can use query methods, such as [[where()]], [[limit()]] and [[orderBy()]] to customize the query options.
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

		$index = $modelClass::indexName();
		$type = $modelClass::indexType();
		if (is_array($this->where) && Activerecord::isPrimaryKey(array_keys($this->where))) {
			// TODO what about mixed queries?
			$query = array();
			foreach((array) reset($this->where) as $pk) {
				 $doc = array(
					'_id' => $pk,
				);
				$db->getQueryBuilder()->buildSelect($doc, $this->select);
				$query['docs'][] = $doc;
			}
			$command = $db->createCommand($query, $index, $type);
			$command->api = '_mget';
			return $command;
		} else {
			$query = $db->getQueryBuilder()->build($this);
			return $db->createCommand($query, $index, $type);
		}
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @param string $column name of the column to select
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
	 * Executes a script created by [[LuaScriptBuilder]]
	 * @param string $type
	 * @param null $column
	 * @return array|bool|null|string
	 */
	protected function executeScript($type, $columnName=null)
	{
		if (($data = $this->findByPk($type)) === false) {
			$modelClass = $this->modelClass;
			$http = $modelClass::getDb()->http();

			$url = '/' . $modelClass::indexName() . '/' . $modelClass::indexType() . '/_search';
			$query = $modelClass::getDb()->getQueryBuilder()->build($this);
			$response = $http->post($url, null, Json::encode($query))->send();
			$data = Json::decode($response->getBody(true));
			return $data['hits']['hits'];
		}
		return $data;
	}

	/**
	 * Fetch by pk if possible as this is much faster
	 */
	private function findByPk($type, $columnName = null)
	{
		$modelClass = $this->modelClass;
		if (is_array($this->where) && !isset($this->where[0]) && $modelClass::isPrimaryKey(array_keys($this->where))) {
			/** @var Client $http */
			$http = $modelClass::getDb()->http();

			$pks = (array) reset($this->where);

			$query = array('docs' => array());
			foreach($pks as $pk) {
				$doc = array('_id' => $pk);
				if (!empty($this->select)) {
					$doc['fields'] = $this->select;
				}
				$query['docs'][] = $doc;
			}
			$url = '/' . $modelClass::indexName() . '/' . $modelClass::indexType() . '/_mget';
			$response = $http->post($url, null, Json::encode($query))->send();
			$data = Json::decode($response->getBody(true));

			$start = $this->offset === null ? 0 : $this->offset;
			$data = array_slice($data['docs'], $start, $this->limit);

			// TODO support orderBy

			switch($type) {
				case 'All':
					return $data;
				case 'One':
					return empty($data) ? null : reset($data);
				case 'Column':
					$column = array();
					foreach($data as $row) {
						$row['_source']['id'] = $row['_id'];
						if ($this->indexBy === null) {
							$column[] = $row['_source'][$columnName];
						} else {
							if (is_string($this->indexBy)) {
								$key = $row['_source'][$this->indexBy];
							} else {
								$key = call_user_func($this->indexBy, $row['_source']);
							}
							$models[$key] = $row;
						}
					}
					return $column;
				case 'Count':
					return count($data);
				case 'Sum':
					$sum = 0;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName) {
								$sum += $dataRow[$i];
								break;
							}
						}
					}
					return $sum;
				case 'Average':
					$sum = 0;
					$count = 0;
					foreach($data as $dataRow) {
						$count++;
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName) {
								$sum += $dataRow[$i];
								break;
							}
						}
					}
					return $sum / $count;
				case 'Min':
					$min = null;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName && ($min == null || $dataRow[$i] < $min)) {
								$min = $dataRow[$i];
								break;
							}
						}
					}
					return $min;
				case 'Max':
					$max = null;
					foreach($data as $dataRow) {
						$c = count($dataRow);
						for($i = 0; $i < $c; ) {
							if ($dataRow[$i++] == $columnName && ($max == null || $dataRow[$i] > $max)) {
								$max = $dataRow[$i];
								break;
							}
						}
					}
					return $max;
			}
		}
		return false;
	}

	// TODO: refactor. code below here is all duplicated from yii/db/ActiveQuery and yii/db/Query

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
			call_user_func_array(array($this->modelClass, $name), $params);
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
			if (!empty($this->with)) {
				$models = array($model);
				$this->populateRelations($models, $this->with);
				$model = $models[0];
			}
			return $model;
		} else {
			return null;
		}
	}

	/**
	 * Sets the [[asArray]] property.
	 * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
	 * @return ActiveQuery the query object itself
	 */
	public function asArray($value = true)
	{
		$this->asArray = $value;
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

	private function createModels($rows)
	{
		$models = array();
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

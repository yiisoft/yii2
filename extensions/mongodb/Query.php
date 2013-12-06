<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;
use yii\helpers\Json;
use Yii;

/**
 * Query represents Mongo "find" operation.
 *
 * Query provides a set of methods to facilitate the specification of "find" command.
 * These methods can be chained together.
 *
 * For example,
 *
 * ~~~
 * $query = new Query;
 * // compose the query
 * $query->select(['name', 'status'])
 *     ->from('customer')
 *     ->limit(10);
 * // execute the query
 * $rows = $query->all();
 * ~~~
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
	use QueryTrait;

	/**
	 * @var array the fields of the results to return. For example, `['name', 'group_id']`.
	 * The "_id" field is always returned. If not set, if means selecting all columns.
	 * @see select()
	 */
	public $select = [];
	/**
	 * @var string|array the collection to be selected from. If string considered as  the name of the collection
	 * inside the default database. If array - first element considered as the name of the database,
	 * second - as name of collection inside that database
	 * @see from()
	 */
	public $from;

	/**
	 * Returns the Mongo collection for this query.
	 * @param Connection $db Mongo connection.
	 * @return Collection collection instance.
	 */
	public function getCollection($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getComponent('mongodb');
		}
		return $db->getCollection($this->from);
	}

	/**
	 * Sets the list of fields of the results to return.
	 * @param array $fields fields of the results to return.
	 * @return static the query object itself.
	 */
	public function select(array $fields)
	{
		$this->select = $fields;
		return $this;
	}

	/**
	 * Sets the collection to be selected from.
	 * @param string|array the collection to be selected from. If string considered as  the name of the collection
	 * inside the default database. If array - first element considered as the name of the database,
	 * second - as name of collection inside that database
	 * @return static the query object itself.
	 */
	public function from($collection)
	{
		$this->from = $collection;
		return $this;
	}

	/**
	 * Builds the Mongo cursor for this query.
	 * @param Connection $db the database connection used to execute the query.
	 * @return \MongoCursor mongo cursor instance.
	 */
	protected function buildCursor($db = null)
	{
		if ($this->where === null) {
			$where = [];
		} else {
			$where = $this->where;
		}
		$selectFields = [];
		if (!empty($this->select)) {
			foreach ($this->select as $fieldName) {
				$selectFields[$fieldName] = true;
			}
		}
		$cursor = $this->getCollection($db)->find($where, $selectFields);
		if (!empty($this->orderBy)) {
			$sort = [];
			foreach ($this->orderBy as $fieldName => $sortOrder) {
				$sort[$fieldName] = $sortOrder === SORT_DESC ? \MongoCollection::DESCENDING : \MongoCollection::ASCENDING;
			}
			$cursor->sort($sort);
		}
		$cursor->limit($this->limit);
		$cursor->skip($this->offset);
		return $cursor;
	}

	/**
	 * Fetches rows from the given Mongo cursor.
	 * @param \MongoCursor $cursor Mongo cursor instance to fetch data from.
	 * @param boolean $all whether to fetch all rows or only first one.
	 * @param string|callable $indexBy the column name or PHP callback,
	 * by which the query results should be indexed by.
	 * @throws Exception on failure.
	 * @return array|boolean result.
	 */
	protected function fetchRows($cursor, $all = true, $indexBy = null)
	{
		$token = 'find(' . Json::encode($cursor->info()) . ')';
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$result = $this->fetchRowsInternal($cursor, $all, $indexBy);
			Yii::endProfile($token, __METHOD__);
			return $result;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * @param \MongoCursor $cursor Mongo cursor instance to fetch data from.
	 * @param boolean $all whether to fetch all rows or only first one.
	 * @param string|callable $indexBy value to index by.
	 * @return array|boolean result.
	 * @see Query::fetchRows()
	 */
	protected function fetchRowsInternal($cursor, $all, $indexBy)
	{
		$result = [];
		if ($all) {
			foreach ($cursor as $row) {
				if ($indexBy !== null) {
					if (is_string($indexBy)) {
						$key = $row[$indexBy];
					} else {
						$key = call_user_func($indexBy, $row);
					}
					$result[$key] = $row;
				} else {
					$result[] = $row;
				}
			}
		} else {
			if ($cursor->hasNext()) {
				$result = $cursor->getNext();
			} else {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Executes the query and returns all results as an array.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$cursor = $this->buildCursor($db);
		return $this->fetchRows($cursor, true, $this->indexBy);
	}

	/**
	 * Executes the query and returns a single row of result.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 */
	public function one($db = null)
	{
		$cursor = $this->buildCursor($db);
		return $this->fetchRows($cursor, false);
	}

	/**
	 * Returns the number of records.
	 * @param string $q kept to match [[QueryInterface]], its value is ignored.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return integer number of records
	 * @throws Exception on failure.
	 */
	public function count($q = '*', $db = null)
	{
		$cursor = $this->buildCursor($db);
		$token = 'find.count(' . Json::encode($cursor->info()) . ')';
		Yii::info($token, __METHOD__);
		try {
			Yii::beginProfile($token, __METHOD__);
			$result = $cursor->count();
			Yii::endProfile($token, __METHOD__);
			return $result;
		} catch (\Exception $e) {
			Yii::endProfile($token, __METHOD__);
			throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
		}
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists($db = null)
	{
		return $this->one($db) !== null;
	}

	/**
	 * Returns the sum of the specified column values.
	 * @param string $q the column name.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return integer the sum of the specified column values
	 */
	public function sum($q, $db = null)
	{
		return $this->aggregate($q, 'sum', $db);
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $q the column name.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return integer the average of the specified column values.
	 */
	public function average($q, $db = null)
	{
		return $this->aggregate($q, 'avg', $db);
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $q the column name.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($q, $db = null)
	{
		return $this->aggregate($q, 'min', $db);
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $q the column name.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($q, $db = null)
	{
		return $this->aggregate($q, 'max', $db);
	}

	/**
	 * Performs the aggregation for the given column.
	 * @param string $column column name.
	 * @param string $operator aggregation operator.
	 * @param Connection $db the database connection used to execute the query.
	 * @return integer aggregation result.
	 */
	protected function aggregate($column, $operator, $db)
	{
		$collection = $this->getCollection($db);
		$pipelines = [];
		if ($this->where !== null) {
			$pipelines[] = ['$match' => $collection->buildCondition($this->where)];
		}
		$pipelines[] = [
			'$group' => [
				'_id' => '1',
				'total' => [
					'$' . $operator => '$' . $column
				],
			]
		];
		$result = $collection->aggregate($pipelines);
		if (array_key_exists(0, $result)) {
			return $result[0]['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Returns a list of distinct values for the given column across a collection.
	 * @param string $q column to use.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongodb` application component will be used.
	 * @return array array of distinct values
	 */
	public function distinct($q, $db = null)
	{
		$collection = $this->getCollection($db);
		if ($this->where !== null) {
			$condition = $this->where;
		} else {
			$condition = [];
		}
		$result = $collection->distinct($q, $condition);
		if ($result === false) {
			return [];
		} else {
			return $result;
		}
	}
}
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongo;

use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;
use Yii;

/**
 * Class Query
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
			$db = Yii::$app->getComponent('mongo');
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
	 * Executes the query and returns all results as an array.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$cursor = $this->buildCursor($db);
		$result = [];
		foreach ($cursor as $row) {
			if ($this->indexBy !== null) {
				if (is_string($this->indexBy)) {
					$key = $row[$this->indexBy];
				} else {
					$key = call_user_func($this->indexBy, $row);
				}
				$result[$key] = $row;
			} else {
				$result[] = $row;
			}
		}
		return $result;
	}

	/**
	 * Executes the query and returns a single row of result.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 */
	public function one($db = null)
	{
		$cursor = $this->buildCursor($db);
		if ($cursor->hasNext()) {
			return $cursor->getNext();
		} else {
			return false;
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return integer number of records
	 */
	public function count($q = '*', $db = null)
	{
		$cursor = $this->buildCursor($db);
		return $cursor->count();
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists($db = null)
	{
		return $this->one($db) !== null;
	}

	/**
	 * Returns the sum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return integer the sum of the specified column values
	 */
	public function sum($q, $db = null)
	{
		return $this->aggregate($q, 'sum', $db);
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
	 * @return integer the average of the specified column values.
	 */
	public function average($q, $db = null)
	{
		return $this->aggregate($q, 'avg', $db);
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $q the column name or expression.
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
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
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
		if (!empty($result['ok'])) {
			return $result['result'][0]['total'];
		} else {
			return 0;
		}
	}

	/**
	 * Returns a list of distinct values for the given column across a collection.
	 * @param string $q column to use.
	 * @param Connection $db the Mongo connection used to execute the query.
	 * If this parameter is not given, the `mongo` application component will be used.
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
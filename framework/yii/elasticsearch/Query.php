<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;

/**
 * Class Query
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
	use QueryTrait;

	/**
	 * @var array the fields being retrieved from the documents. For example, `['id', 'name']`.
	 * If not set, it means retrieving all fields. An empty array will result in no fields being
	 * retrieved. This means that only the primaryKey of a record will be available in the result.
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html#search-request-fields
	 * @see fields()
	 */
	public $fields;
	/**
	 * @var string|array The index to retrieve data from. This can be a string representing a single index
	 * or a an array of multiple indexes. If this is not set, indexes are being queried.
	 * @see from()
	 */
	public $index;
	/**
	 * @var string|array The type to retrieve data from. This can be a string representing a single type
	 * or a an array of multiple types. If this is not set, all types are being queried.
	 * @see from()
	 */
	public $type;
	/**
	 * @var integer A search timeout, bounding the search request to be executed within the specified time value
	 * and bail with the hits accumulated up to that point when expired. Defaults to no timeout.
	 * @see timeout()
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-body.html#_parameters_3
	 */
	public $timeout;

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getComponent('elasticsearch');
		}

		$query = $db->getQueryBuilder()->build($this);
		return $db->createCommand($query, $this->index, $this->type);
	}

	/**
	 * Executes the query and returns all results as an array.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$rows = $this->createCommand($db)->queryAll()['hits'];
		if ($this->indexBy === null && $this->fields === null) {
			return $rows;
		}
		$result = [];
		foreach ($rows as $key => $row) {
			if ($this->fields !== null) {
				$row['_source'] = isset($row['fields']) ? $row['fields'] : [];
				unset($row['fields']);
			}
			if ($this->indexBy !== null) {
				if (is_string($this->indexBy)) {
					$key = $row['_source'][$this->indexBy];
				} else {
					$key = call_user_func($this->indexBy, $row);
				}
			}
			$result[$key] = $row;
		}
		return $result;
	}

	/**
	 * Executes the query and returns a single row of result.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 */
	public function one($db = null)
	{
		$options['size'] = 1;
		$result = $this->createCommand($db)->queryAll($options);
		if (empty($result['hits'])) {
			return false;
		}
		$record = reset($result['hits']);
		if ($this->fields !== null) {
			$record['_source'] = isset($record['fields']) ? $record['fields'] : [];
			unset($record['fields']);
		}
		return $record;
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the specified field in the first document of the query results.
	 * @param string $field name of the attribute to select
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return string the value of the specified attribute in the first record of the query result.
	 * Null is returned if the query result is empty or the field does not exist.
	 */
	public function scalar($field, $db = null)
	{
		$record = self::one($db);
		if ($record !== false && isset($record['_source'][$field])) {
			return $record['_source'][$field];
		} else {
			return null;
		}
	}

	/**
	 * Executes the query and returns the first column of the result.
	 * @param string $field the field to query over
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return array the first column of the query result. An empty array is returned if the query results in nothing.
	 */
	public function column($field, $db = null)
	{
		$query = clone $this;
		$rows = $query->fields([$field])->createCommand($db)->queryAll()['hits'];
		$result = [];
		foreach ($rows as $row) {
			$result[] = $row['fields'][$field];
		}
		return $result;
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. This parameter is ignored by this implementation.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return integer number of records
	 */
	public function count($q = '*', $db = null)
	{
		$count = $this->createCommand($db)->queryCount()['total'];
		if ($this->limit === null && $this->offset === null) {
			return $count;
		} elseif ($this->offset !== null) {
			$count = $this->offset < $count ? $count - $this->offset : 0;
		}
		return $this->limit === null ? $count : ($this->limit > $count ? $count : $this->limit);
	}


	/**
	 * Returns the sum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return integer the sum of the specified column values
	 */
	public function sum($q, $db = null)
	{
		$this->select = ["SUM($q)"];
		return $this->createCommand($db)->queryScalar();
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return integer the average of the specified column values.
	 */
	public function average($q, $db = null)
	{
		$this->select = ["AVG($q)"];
		return $this->createCommand($db)->queryScalar();
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($q, $db = null)
	{
		$this->select = ["MIN($q)"];
		return $this->createCommand($db)->queryScalar();
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $q the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($q, $db = null)
	{
		$this->select = ["MAX($q)"];
		return $this->createCommand($db)->queryScalar();
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists($db = null)
	{
		// TODO check for exists
		return $this->one($db) !== null;
	}

	/**
	 * Executes the query and returns all results as an array.
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `elasticsearch` application component will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function delete($db = null)
	{
		// TODO http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
	}

	/**
	 * Sets the index and type to retrieve documents from.
	 * @param string|array $index The index to retrieve data from. This can be a string representing a single index
	 * or a an array of multiple indexes. If this is `null` it means that all indexes are being queried.
	 * @param string|array $type The type to retrieve data from. This can be a string representing a single type
	 * or a an array of multiple types. If this is `null` it means that all types are being queried.
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-search.html#search-multi-index-type
	 */
	public function from($index, $type = null)
	{
		$this->index = $index;
		$this->type = $type;
	}

	/**
	 * Sets the fields to retrieve from the documents.
	 * @param array $fields the fields to be selected.
	 * @return static the query object itself
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-fields.html
	 */
	public function fields($fields)
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * Sets the search timeout.
	 * @param integer $timeout A search timeout, bounding the search request to be executed within the specified time value
	 * and bail with the hits accumulated up to that point when expired. Defaults to no timeout.
	 * @return static the query object itself
	 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-request-body.html#_parameters_3
	 */
	public function timeout($timeout)
	{
		$this->timeout = $timeout;
		return $this;
	}

}
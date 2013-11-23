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
	 * @var array the columns being selected. For example, `array('id', 'name')`.
	 * This is used to construct the SELECT clause in a SQL statement. If not set, if means selecting all columns.
	 * @see select()
	 */
	public $select; // TODO fields

	public $index;

	public $type;

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the database connection used to generate the SQL statement.
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all($db = null)
	{
		$rows = $this->createCommand($db)->queryAll();
		if ($this->indexBy === null) {
			return $rows;
		}
		$result = [];
		foreach ($rows as $row) {
			if (is_string($this->indexBy)) {
				$key = $row[$this->indexBy];
			} else {
				$key = call_user_func($this->indexBy, $row);
			}
			$result[$key] = $row;
		}
		return $result;
	}

	/**
	 * Executes the query and returns a single row of result.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 */
	public function one($db = null)
	{
		return $this->createCommand($db)->queryOne();
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the specified attribute in the first record of the query results.
	 * @param string $attribute name of the attribute to select
	 * @param Connection $db the database connection used to execute the query.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return string the value of the specified attribute in the first record of the query result.
	 * Null is returned if the query result is empty.
	 */
	public function scalar($attribute, $db = null)
	{
		$record = $this->one($db);
		if ($record !== null) {
			return $record->$attribute;
		} else {
			return null;
		}
	}

	/**
	 * Executes the query and returns the first column of the result.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return array the first column of the query result. An empty array is returned if the query results in nothing.
	 */
	public function column($db = null)
	{
		return $this->createCommand($db)->queryColumn();
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. This parameter is ignored by this implementation.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given (or null), the `db` application component will be used.
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
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
	 * If this parameter is not given, the `db` application component will be used.
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

	public function from($index, $type = null)
	{
		$this->index = $index;
		$this->type = $type;
	}
}
<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use Yii;
use yii\base\Component;
use yii\db\Expression;

/**
 * Class Query
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends Component
{
	/**
	 * Sort ascending
	 * @see orderBy
	 */
	const SORT_ASC = false;
	/**
	 * Sort descending
	 * @see orderBy
	 */
	const SORT_DESC = true;

	public $select;
	/**
	 * @var string additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 */
	public $selectOption;
	/**
	 * @var boolean whether to select distinct rows of data only. If this is set true,
	 * the SELECT clause would be changed to SELECT DISTINCT.
	 */
	public $distinct;
	public $from;
	public $where;
	public $limit;
	public $offset;
	public $orderBy;
	public $groupBy;
	/**
	 * @var string WITHIN GROUP ORDER BY clause. This is a Sphinx specific extension
	 * that lets you control how the best row within a group will to be selected.
	 */
	public $within;
	/**
	 * @var array per-query options in format: optionName => optionValue
	 * They will compose OPTION clause. This is a Sphinx specific extension
	 * that lets you control a number of per-query options.
	 */
	public $options;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `[':name' => 'Dan', ':age' => 31]`.
	 */
	public $params;
	/**
	 * @var string|callable $column the name of the column by which the query results should be indexed by.
	 * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
	 * row data. For more details, see [[indexBy()]]. This property is only used by [[all()]].
	 */
	public $indexBy;

	/**
	 * Creates a Sphinx command that can be used to execute this query.
	 * @param Connection $sphinxConnection the Sphinx connection used to generate the SQL statement.
	 * If this parameter is not given, the `sphinx` application component will be used.
	 * @return Command the created Sphinx command instance.
	 */
	public function createCommand($sphinxConnection = null)
	{
		if ($sphinxConnection === null) {
			$sphinxConnection = Yii::$app->getComponent('sphinx');
		}
		list ($sql, $params) = $sphinxConnection->getQueryBuilder()->build($this);
		return $sphinxConnection->createCommand($sql, $params);
	}

	/**
	 * Sets the [[indexBy]] property.
	 * @param string|callable $column the name of the column by which the query results should be indexed by.
	 * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
	 * row data. The signature of the callable should be:
	 *
	 * ~~~
	 * function ($row)
	 * {
	 *     // return the index value corresponding to $row
	 * }
	 * ~~~
	 *
	 * @return static the query object itself
	 */
	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
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
	 * The value returned will be the first column in the first row of the query results.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar($db = null)
	{
		return $this->createCommand($db)->queryScalar();
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
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return integer number of records
	 */
	public function count($q = '*', $db = null)
	{
		$this->select = ["COUNT($q)"];
		return $this->createCommand($db)->queryScalar();
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
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists($db = null)
	{
		$this->select = [new Expression('1')];
		return $this->scalar($db) !== false;
	}

	/**
	 * Sets the SELECT part of the query.
	 * @param string|array $columns the columns to be selected.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. ['id', 'name']).
	 * Columns can contain table prefixes (e.g. "tbl_user.id") and/or column aliases (e.g. "tbl_user.id AS user_id").
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @param string $option additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 * @return static the query object itself
	 */
	public function select($columns, $option = null)
	{
		if (!is_array($columns)) {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}
		$this->select = $columns;
		$this->selectOption = $option;
		return $this;
	}

	/**
	 * Sets the value indicating whether to SELECT DISTINCT or not.
	 * @param bool $value whether to SELECT DISTINCT or not.
	 * @return static the query object itself
	 */
	public function distinct($value = true)
	{
		$this->distinct = $value;
		return $this;
	}

	/**
	 * Sets the FROM part of the query.
	 * @param string|array $tables the table(s) to be selected from. This can be either a string (e.g. `'tbl_user'`)
	 * or an array (e.g. `['tbl_user', 'tbl_profile']`) specifying one or several table names.
	 * Table names can contain schema prefixes (e.g. `'public.tbl_user'`) and/or table aliases (e.g. `'tbl_user u'`).
	 * The method will automatically quote the table names unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return static the query object itself
	 */
	public function from($tables)
	{
		if (!is_array($tables)) {
			$tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
		}
		$this->from = $tables;
		return $this;
	}

	public function where($condition, $params = [])
	{
		$this->where = $condition;
		$this->addParams($params);
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'AND' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see where()
	 * @see orWhere()
	 */
	public function andWhere($condition, $params = [])
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = ['and', $this->where, $condition];
		}
		$this->addParams($params);
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see where()
	 * @see andWhere()
	 */
	public function orWhere($condition, $params = [])
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = ['or', $this->where, $condition];
		}
		$this->addParams($params);
		return $this;
	}

	/**
	 * Sets the GROUP BY part of the query.
	 * @param string|array $columns the columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. ['id', 'name']).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see addGroupBy()
	 */
	public function groupBy($columns)
	{
		if (!is_array($columns)) {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}
		$this->groupBy = $columns;
		return $this;
	}

	/**
	 * Adds additional group-by columns to the existing ones.
	 * @param string|array $columns additional columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. ['id', 'name']).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see groupBy()
	 */
	public function addGroupBy($columns)
	{
		if (!is_array($columns)) {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}
		if ($this->groupBy === null) {
			$this->groupBy = $columns;
		} else {
			$this->groupBy = array_merge($this->groupBy, $columns);
		}
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see addOrderBy()
	 */
	public function orderBy($columns)
	{
		$this->orderBy = $this->normalizeOrderBy($columns);
		return $this;
	}

	/**
	 * Adds additional ORDER BY columns to the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see orderBy()
	 */
	public function addOrderBy($columns)
	{
		$columns = $this->normalizeOrderBy($columns);
		if ($this->orderBy === null) {
			$this->orderBy = $columns;
		} else {
			$this->orderBy = array_merge($this->orderBy, $columns);
		}
		return $this;
	}

	protected function normalizeOrderBy($columns)
	{
		if (is_array($columns)) {
			return $columns;
		} else {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			$result = [];
			foreach ($columns as $column) {
				if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
					$result[$matches[1]] = strcasecmp($matches[2], 'desc') ? self::SORT_ASC : self::SORT_DESC;
				} else {
					$result[$column] = self::SORT_ASC;
				}
			}
			return $result;
		}
	}

	/**
	 * Sets the LIMIT part of the query.
	 * @param integer $limit the limit. Use null or negative value to disable limit.
	 * @return static the query object itself
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset. Use null or negative value to disable offset.
	 * @return static the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Sets the parameters to be bound to the query.
	 * @param array $params list of query parameter values indexed by parameter placeholders.
	 * For example, `[':name' => 'Dan', ':age' => 31]`.
	 * @return static the query object itself
	 * @see addParams()
	 */
	public function params($params)
	{
		$this->params = $params;
		return $this;
	}

	/**
	 * Adds additional parameters to be bound to the query.
	 * @param array $params list of query parameter values indexed by parameter placeholders.
	 * For example, `[':name' => 'Dan', ':age' => 31]`.
	 * @return static the query object itself
	 * @see params()
	 */
	public function addParams($params)
	{
		if (!empty($params)) {
			if ($this->params === null) {
				$this->params = $params;
			} else {
				foreach ($params as $name => $value) {
					if (is_integer($name)) {
						$this->params[] = $value;
					} else {
						$this->params[$name] = $value;
					}
				}
			}
		}
		return $this;
	}

	public function options(array $options)
	{
		$this->options = $options;
		return $this;
	}

	public function addOptions(array $options)
	{
		if (is_array($this->options)) {
			$this->options = array_merge($this->options, $options);
		} else {
			$this->options = $options;
		}
		return $this;
	}

	public function within($columns)
	{
		$this->within = $this->normalizeOrderBy($columns);
		return $this;
	}

	public function addWithin($columns)
	{
		$columns = $this->normalizeOrderBy($columns);
		if ($this->within === null) {
			$this->within = $columns;
		} else {
			$this->within = array_merge($this->within, $columns);
		}
		return $this;
	}
}
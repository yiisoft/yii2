<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

use Yii;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\db\Expression;
use yii\db\QueryInterface;
use yii\db\QueryTrait;

/**
 * Class Query
 *
 *
 * Note: implicit LIMIT 0,20 is present by default.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
	use QueryTrait;

	/**
	 * @var array the columns being selected. For example, `['id', 'group_id']`.
	 * This is used to construct the SELECT clause in a SQL statement. If not set, if means selecting all columns.
	 * @see select()
	 */
	public $select;
	/**
	 * @var string additional option that should be appended to the 'SELECT' keyword.
	 */
	public $selectOption;
	/**
	 * @var boolean whether to select distinct rows of data only. If this is set true,
	 * the SELECT clause would be changed to SELECT DISTINCT.
	 */
	public $distinct;
	/**
	 * @var array the index(es) to be selected from. For example, `['idx_user', 'idx_post']`.
	 * This is used to construct the FROM clause in a SQL statement.
	 * @see from()
	 */
	public $from;
	/**
	 * @var string text, which should be searched in fulltext mode.
	 * This value will be composed into MATCH operator inside the WHERE clause.
	 */
	public $match;
	/**
	 * @var array how to group the query results. For example, `['company', 'department']`.
	 * This is used to construct the GROUP BY clause in a SQL statement.
	 */
	public $groupBy;
	/**
	 * @var string WITHIN GROUP ORDER BY clause. This is a Sphinx specific extension
	 * that lets you control how the best row within a group will to be selected.
	 * The possible value matches the [[orderBy]] one.
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
	 * @var callback PHP callback, which should be used to fetch source data for the snippets.
	 * Such callback will receive array of query result rows as an argument and must return the
	 * array of snippet source strings in the order, which match one of incoming rows.
	 * For example:
	 * ```php
	 * $query = new Query;
	 * $query->from('idx_item')
	 *     ->match('pencil')
	 *     ->snippetCallback(function ($rows) {
	 *         $result = [];
	 *         foreach ($rows as $row) {
	 *             $result[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
	 *         }
	 *         return $result;
	 *     })
	 *     ->all();
	 * ```
	 */
	public $snippetCallback;
	/**
	 * @var array query options for the call snippet.
	 */
	public $snippetOptions;
	/**
	 * @var Connection the Sphinx connection used to generate the SQL statements.
	 */
	private $_connection;

	/**
	 * @param Connection $connection Sphinx connection instance
	 * @return static the query object itself
	 */
	public function setConnection($connection)
	{
		$this->_connection = $connection;
		return $this;
	}

	/**
	 * @return Connection Sphinx connection instance
	 */
	public function getConnection()
	{
		if ($this->_connection === null) {
			$this->_connection = $this->defaultConnection();
		}
		return $this->_connection;
	}

	/**
	 * @return Connection default connection value.
	 */
	protected function defaultConnection()
	{
		return Yii::$app->getComponent('sphinx');
	}

	/**
	 * Creates a Sphinx command that can be used to execute this query.
	 * @param Connection $connection the Sphinx connection used to generate the SQL statement.
	 * If this parameter is not given, the `sphinx` application component will be used.
	 * @return Command the created Sphinx command instance.
	 */
	public function createCommand($connection = null)
	{
		$this->setConnection($connection);
		$connection = $this->getConnection();
		list ($sql, $params) = $connection->getQueryBuilder()->build($this);
		return $connection->createCommand($sql, $params);
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
		$rows = $this->fillUpSnippets($rows);
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
		$row = $this->createCommand($db)->queryOne();
		if ($row !== false) {
			list ($row) = $this->fillUpSnippets([$row]);
		}
		return $row;
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

	/**
	 * Sets the fulltext query text. This text will be composed into
	 * MATCH operator inside the WHERE clause.
	 * @param string $query fulltext query text.
	 * @return static the query object itself
	 */
	public function match($query)
	{
		$this->match = $query;
		return $this;
	}

	/**
	 * Sets the WHERE part of the query.
	 *
	 * The method requires a $condition parameter, and optionally a $params parameter
	 * specifying the values to be bound to the query.
	 *
	 * The $condition parameter should be either a string (e.g. 'id=1') or an array.
	 * If the latter, it must be in one of the following two formats:
	 *
	 * - hash format: `['column1' => value1, 'column2' => value2, ...]`
	 * - operator format: `[operator, operand1, operand2, ...]`
	 *
	 * A condition in hash format represents the following SQL expression in general:
	 * `column1=value1 AND column2=value2 AND ...`. In case when a value is an array,
	 * an `IN` expression will be generated. And if a value is null, `IS NULL` will be used
	 * in the generated expression. Below are some examples:
	 *
	 * - `['type' => 1, 'status' => 2]` generates `(type = 1) AND (status = 2)`.
	 * - `['id' => [1, 2, 3], 'status' => 2]` generates `(id IN (1, 2, 3)) AND (status = 2)`.
	 * - `['status' => null] generates `status IS NULL`.
	 *
	 * A condition in operator format generates the SQL expression according to the specified operator, which
	 * can be one of the followings:
	 *
	 * - `and`: the operands should be concatenated together using `AND`. For example,
	 * `['and', 'id=1', 'id=2']` will generate `id=1 AND id=2`. If an operand is an array,
	 * it will be converted into a string using the rules described here. For example,
	 * `['and', 'type=1', ['or', 'id=1', 'id=2']]` will generate `type=1 AND (id=1 OR id=2)`.
	 * The method will NOT do any quoting or escaping.
	 *
	 * - `or`: similar to the `and` operator except that the operands are concatenated using `OR`.
	 *
	 * - `between`: operand 1 should be the column name, and operand 2 and 3 should be the
	 * starting and ending values of the range that the column is in.
	 * For example, `['between', 'id', 1, 10]` will generate `id BETWEEN 1 AND 10`.
	 *
	 * - `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
	 * in the generated condition.
	 *
	 * - `in`: operand 1 should be a column or DB expression, and operand 2 be an array representing
	 * the range of the values that the column or DB expression should be in. For example,
	 * `['in', 'id', [1, 2, 3]]` will generate `id IN (1, 2, 3)`.
	 * The method will properly quote the column name and escape values in the range.
	 *
	 * - `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.
	 *
	 * - `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
	 * the values that the column or DB expression should be like.
	 * For example, `['like', 'name', '%tester%']` will generate `name LIKE '%tester%'`.
	 * When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
	 * using `AND`. For example, `['like', 'name', ['%test%', '%sample%']]` will generate
	 * `name LIKE '%test%' AND name LIKE '%sample%'`.
	 * The method will properly quote the column name and escape values in the range.
	 *
	 * - `or like`: similar to the `like` operator except that `OR` is used to concatenate the `LIKE`
	 * predicates when operand 2 is an array.
	 *
	 * - `not like`: similar to the `like` operator except that `LIKE` is replaced with `NOT LIKE`
	 * in the generated condition.
	 *
	 * - `or not like`: similar to the `not like` operator except that `OR` is used to concatenate
	 * the `NOT LIKE` predicates.
	 *
	 * @param string|array $condition the conditions that should be put in the WHERE part.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see andWhere()
	 * @see orWhere()
	 */
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

	/**
	 * Sets the query options.
	 * @param array $options query options in format: optionName => optionValue
	 * @return static the query object itself
	 * @see addOptions()
	 */
	public function options($options)
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * Adds additional query options.
	 * @param array $options query options in format: optionName => optionValue
	 * @return static the query object itself
	 * @see options()
	 */
	public function addOptions($options)
	{
		if (is_array($this->options)) {
			$this->options = array_merge($this->options, $options);
		} else {
			$this->options = $options;
		}
		return $this;
	}

	/**
	 * Sets the WITHIN GROUP ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to find best row within a group.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see addWithin()
	 */
	public function within($columns)
	{
		$this->within = $this->normalizeOrderBy($columns);
		return $this;
	}

	/**
	 * Adds additional WITHIN GROUP ORDER BY columns to the query.
	 * @param string|array $columns the columns (and the directions) to find best row within a group.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see within()
	 */
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

	/**
	 * @param callback $callback
	 * @return static the query object itself
	 */
	public function snippetCallback($callback)
	{
		$this->snippetCallback = $callback;
		return $this;
	}

	/**
	 * @param array $options
	 * @return static the query object itself
	 */
	public function snippetOptions($options)
	{
		$this->snippetOptions = $options;
		return $this;
	}

	/**
	 * Fills the query result rows with the snippets built from source determined by
	 * [[snippetCallback]] result.
	 * @param array $rows raw query result rows.
	 * @return array query result rows with filled up snippets.
	 */
	protected function fillUpSnippets($rows)
	{
		if ($this->snippetCallback === null) {
			return $rows;
		}
		$snippetSources = call_user_func($this->snippetCallback, $rows);
		$snippets = $this->callSnippets($snippetSources);
		$snippetKey = 0;
		foreach ($rows as $key => $row) {
			$rows[$key]['snippet'] = $snippets[$snippetKey];
			$snippetKey++;
		}
		return $rows;
	}

	/**
	 * Builds a snippets from provided source data.
	 * @param array $source the source data to extract a snippet from.
	 * @return array snippets list
	 */
	protected function callSnippets(array $source)
	{
		$connection = $this->getConnection();
		$match = $this->match;
		if ($match === null) {
			throw new InvalidCallException('Unable to call snippets: "' . $this->className() . '::match" should be specified.');
		}
		return $connection->createCommand()
			->callSnippets($this->from[0], $source, $match, $this->snippetOptions)
			->queryColumn();
	}
}
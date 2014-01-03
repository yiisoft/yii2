<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Component;

/**
 * Query represents a SELECT SQL statement in a way that is independent of DBMS.
 *
 * Query provides a set of methods to facilitate the specification of different clauses
 * in a SELECT statement. These methods can be chained together.
 *
 * By calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 *
 * For example,
 *
 * ~~~
 * $query = new Query;
 * // compose the query
 * $query->select('id, name')
 *     ->from('tbl_user')
 *     ->limit(10);
 * // build and execute the query
 * $rows = $query->all();
 * // alternatively, you can create DB command and execute it
 * $command = $query->createCommand();
 * // $command->sql returns the actual SQL
 * $rows = $command->queryAll();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Query extends Component implements QueryInterface
{
	use QueryTrait;

	/**
	 * @var array the columns being selected. For example, `['id', 'name']`.
	 * This is used to construct the SELECT clause in a SQL statement. If not set, it means selecting all columns.
	 * @see select()
	 */
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
	/**
	 * @var array the table(s) to be selected from. For example, `['tbl_user', 'tbl_post']`.
	 * This is used to construct the FROM clause in a SQL statement.
	 * @see from()
	 */
	public $from;
	/**
	 * @var array how to group the query results. For example, `['company', 'department']`.
	 * This is used to construct the GROUP BY clause in a SQL statement.
	 */
	public $groupBy;
	/**
	 * @var array how to join with other tables. Each array element represents the specification
	 * of one join which has the following structure:
	 *
	 * ~~~
	 * [$joinType, $tableName, $joinCondition]
	 * ~~~
	 *
	 * For example,
	 *
	 * ~~~
	 * [
	 *     ['INNER JOIN', 'tbl_user', 'tbl_user.id = author_id'],
	 *     ['LEFT JOIN', 'tbl_team', 'tbl_team.id = team_id'],
	 * ]
	 * ~~~
	 */
	public $join;
	/**
	 * @var string|array the condition to be applied in the GROUP BY clause.
	 * It can be either a string or an array. Please refer to [[where()]] on how to specify the condition.
	 */
	public $having;
	/**
	 * @var array this is used to construct the UNION clause(s) in a SQL statement.
	 * Each array element can be either a string or a [[Query]] object representing a sub-query.
	 */
	public $union;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `[':name' => 'Dan', ':age' => 31]`.
	 */
	public $params;


	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($db = null)
	{
		if ($db === null) {
			$db = Yii::$app->getDb();
		}
		list ($sql, $params) = $db->getQueryBuilder()->build($this);
		return $db->createCommand($sql, $params);
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
	 * If this parameter is not given (or null), the `db` application component will be used.
	 * @return integer number of records
	 */
	public function count($q = '*', $db = null)
	{
		return $this->queryScalar("COUNT($q)", $db);
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
		return $this->queryScalar("SUM($q)", $db);
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
		return $this->queryScalar("AVG($q)", $db);
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
		return $this->queryScalar("MIN($q)", $db);
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
		return $this->queryScalar("MAX($q)", $db);
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists($db = null)
	{
		$select = $this->select;
		$this->select = [new Expression('1')];
		$command = $this->createCommand($db);
		$this->select = $select;
		return $command->queryScalar() !== false;
	}

	/**
	 * Queries a scalar value by setting [[select]] first.
	 * Restores the value of select to make this query reusable.
	 * @param string|Expression $selectExpression
	 * @param Connection $db
	 * @return bool|string
	 */
	private function queryScalar($selectExpression, $db)
	{
		$select = $this->select;
		$limit = $this->limit;
		$offset = $this->offset;

		$this->select = [$selectExpression];
		$this->limit = null;
		$this->offset = null;
		$command = $this->createCommand($db);

		$this->select = $select;
		$this->limit = $limit;
		$this->offset = $offset;

		return $command->queryScalar();
	}

	/**
	 * Sets the SELECT part of the query.
	 * @param string|array $columns the columns to be selected.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. ['id', 'name']).
	 * Columns can contain table prefixes (e.g. "tbl_user.id") and/or column aliases (e.g. "tbl_user.id AS user_id").
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 *
	 * Note that if you are selecting an expression like `CONCAT(first_name, ' ', last_name)`, you should
	 * use an array to specify the columns. Otherwise, the expression may be incorrectly split into several parts.
	 *
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
	 * For example, `['like', 'name', 'tester']` will generate `name LIKE '%tester%'`.
	 * When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
	 * using `AND`. For example, `['like', 'name', ['test', 'sample']]` will generate
	 * `name LIKE '%test%' AND name LIKE '%sample%'`.
	 * The method will properly quote the column name and escape special characters in the values.
	 * Sometimes, you may want to add the percentage characters to the matching value by yourself, you may supply
	 * a third operand `false` to do so. For example, `['like', 'name', '%tester', false]` will generate `name LIKE '%tester'`.
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
	 * Appends a JOIN part to the query.
	 * The first parameter specifies what type of join it is.
	 * @param string $type the type of join, such as INNER JOIN, LEFT JOIN.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $on the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return Query the query object itself
	 */
	public function join($type, $table, $on = '', $params = [])
	{
		$this->join[] = [$type, $table, $on];
		return $this->addParams($params);
	}

	/**
	 * Appends an INNER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $on the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return Query the query object itself
	 */
	public function innerJoin($table, $on = '', $params = [])
	{
		$this->join[] = ['INNER JOIN', $table, $on];
		return $this->addParams($params);
	}

	/**
	 * Appends a LEFT OUTER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $on the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query
	 * @return Query the query object itself
	 */
	public function leftJoin($table, $on = '', $params = [])
	{
		$this->join[] = ['LEFT JOIN', $table, $on];
		return $this->addParams($params);
	}

	/**
	 * Appends a RIGHT OUTER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $on the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query
	 * @return Query the query object itself
	 */
	public function rightJoin($table, $on = '', $params = [])
	{
		$this->join[] = ['RIGHT JOIN', $table, $on];
		return $this->addParams($params);
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
	 * Sets the HAVING part of the query.
	 * @param string|array $condition the conditions to be put after HAVING.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see andHaving()
	 * @see orHaving()
	 */
	public function having($condition, $params = [])
	{
		$this->having = $condition;
		$this->addParams($params);
		return $this;
	}

	/**
	 * Adds an additional HAVING condition to the existing one.
	 * The new condition and the existing one will be joined using the 'AND' operator.
	 * @param string|array $condition the new HAVING condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see having()
	 * @see orHaving()
	 */
	public function andHaving($condition, $params = [])
	{
		if ($this->having === null) {
			$this->having = $condition;
		} else {
			$this->having = ['and', $this->having, $condition];
		}
		$this->addParams($params);
		return $this;
	}

	/**
	 * Adds an additional HAVING condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new HAVING condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see having()
	 * @see andHaving()
	 */
	public function orHaving($condition, $params = [])
	{
		if ($this->having === null) {
			$this->having = $condition;
		} else {
			$this->having = ['or', $this->having, $condition];
		}
		$this->addParams($params);
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string|Query $sql the SQL statement to be appended using UNION
	 * @return static the query object itself
	 */
	public function union($sql)
	{
		$this->union[] = $sql;
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
}

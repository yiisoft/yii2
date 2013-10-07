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
 * $query->select('id, name')
 *     ->from('tbl_user')
 *     ->limit(10);
 * // build and execute the query
 * $command = $query->createCommand();
 * // $command->sql returns the actual SQL
 * $rows = $command->queryAll();
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Query extends Component
{
	use BaseQuery;

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

	/**
	 * @var array the columns being selected. For example, `array('id', 'name')`.
	 * This is used to construct the SELECT clause in a SQL statement. If not set, if means selecting all columns.
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
	 * @var array the table(s) to be selected from. For example, `array('tbl_user', 'tbl_post')`.
	 * This is used to construct the FROM clause in a SQL statement.
	 * @see from()
	 */
	public $from;
	/**
	 * @var array how to group the query results. For example, `array('company', 'department')`.
	 * This is used to construct the GROUP BY clause in a SQL statement.
	 */
	public $groupBy;
	/**
	 * @var array how to join with other tables. Each array element represents the specification
	 * of one join which has the following structure:
	 *
	 * ~~~
	 * array($joinType, $tableName, $joinCondition)
	 * ~~~
	 *
	 * For example,
	 *
	 * ~~~
	 * array(
	 *     array('INNER JOIN', 'tbl_user', 'tbl_user.id = author_id'),
	 *     array('LEFT JOIN', 'tbl_team', 'tbl_team.id = team_id'),
	 * )
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
	 * For example, `array(':name' => 'Dan', ':age' => 31)`.
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
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names in the expression.
	 * @param Connection $db the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return integer number of records
	 */
	public function count($q = '*', $db = null)
	{
		$this->select = array("COUNT($q)");
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
		$this->select = array("SUM($q)");
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
		$this->select = array("AVG($q)");
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
		$this->select = array("MIN($q)");
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
		$this->select = array("MAX($q)");
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
		$this->select = array(new Expression('1'));
		return $this->scalar($db) !== false;
	}

	/**
	 * Sets the SELECT part of the query.
	 * @param string|array $columns the columns to be selected.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * Columns can contain table prefixes (e.g. "tbl_user.id") and/or column aliases (e.g. "tbl_user.id AS user_id").
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @param string $option additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 * @return Query the query object itself
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
	 * @return Query the query object itself
	 */
	public function distinct($value = true)
	{
		$this->distinct = $value;
		return $this;
	}

	/**
	 * Sets the FROM part of the query.
	 * @param string|array $tables the table(s) to be selected from. This can be either a string (e.g. `'tbl_user'`)
	 * or an array (e.g. `array('tbl_user', 'tbl_profile')`) specifying one or several table names.
	 * Table names can contain schema prefixes (e.g. `'public.tbl_user'`) and/or table aliases (e.g. `'tbl_user u'`).
	 * The method will automatically quote the table names unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return Query the query object itself
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
	 * @inheritDocs
	 * @param string|array $condition the conditions that should be put in the WHERE part.
	 * @param array $params the parameters (name => value) to be bound to the query.
	 * @return static the query object itself
	 * @see andWhere()
	 * @see orWhere()
	 */
	public function where($condition, $params = array())
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
	public function andWhere($condition, $params = array())
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = array('and', $this->where, $condition);
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
	public function orWhere($condition, $params = array())
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = array('or', $this->where, $condition);
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
	public function join($type, $table, $on = '', $params = array())
	{
		$this->join[] = array($type, $table, $on);
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
	public function innerJoin($table, $on = '', $params = array())
	{
		$this->join[] = array('INNER JOIN', $table, $on);
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
	public function leftJoin($table, $on = '', $params = array())
	{
		$this->join[] = array('LEFT JOIN', $table, $on);
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
	public function rightJoin($table, $on = '', $params = array())
	{
		$this->join[] = array('RIGHT JOIN', $table, $on);
		return $this->addParams($params);
	}

	/**
	 * Sets the GROUP BY part of the query.
	 * @param string|array $columns the columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
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
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
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
	 * @return Query the query object itself
	 * @see andHaving()
	 * @see orHaving()
	 */
	public function having($condition, $params = array())
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
	 * @return Query the query object itself
	 * @see having()
	 * @see orHaving()
	 */
	public function andHaving($condition, $params = array())
	{
		if ($this->having === null) {
			$this->having = $condition;
		} else {
			$this->having = array('and', $this->having, $condition);
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
	 * @return Query the query object itself
	 * @see having()
	 * @see andHaving()
	 */
	public function orHaving($condition, $params = array())
	{
		if ($this->having === null) {
			$this->having = $condition;
		} else {
			$this->having = array('or', $this->having, $condition);
		}
		$this->addParams($params);
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string|Query $sql the SQL statement to be appended using UNION
	 * @return Query the query object itself
	 */
	public function union($sql)
	{
		$this->union[] = $sql;
		return $this;
	}

	/**
	 * Sets the parameters to be bound to the query.
	 * @param array $params list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name' => 'Dan', ':age' => 31)`.
	 * @return Query the query object itself
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
	 * For example, `array(':name' => 'Dan', ':age' => 31)`.
	 * @return Query the query object itself
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

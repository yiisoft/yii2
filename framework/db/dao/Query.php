<?php
/**
 * Query class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

/**
 * Query represents a SQL statement in a way that is independent of DBMS.
 *
 * Query not only can represent a SELECT statement, it can also represent INSERT, UPDATE, DELETE,
 * and other commonly used DDL statements, such as CREATE TABLE, CREATE INDEX, etc.
 *
 * Query provides a set of methods to facilitate the specification of different clauses.
 * These methods can be chained together. For example,
 *
 * ~~~
 * $query = new Query;
 * $query->select('id, name')
 *     ->from('tbl_user')
 *     ->limit(10);
 * // get the actual SQL statement
 * echo $query->getSql();
 * // or execute the query
 * $users = $query->createCommand()->queryAll();
 * ~~~
 *
 * By calling [[getSql()]], we can obtain the actual SQL statement from a Query object.
 * And by calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 *
 * @property string $sql the SQL statement represented by this query object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Query extends \yii\base\Object
{
	/**
	 * @var string|array the columns being selected. This refers to the SELECT clause in a SQL
	 * statement. It can be either a string (e.g. `'id, name'`) or an array (e.g. `array('id', 'name')`).
	 * If not set, if means all columns.
	 * @see select()
	 */
	public $select;
	/**
	 * @var string additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 */
	public $selectOption;
	/**
	 * @var string|array the table(s) to be selected from. This refers to the FROM clause in a SQL statement.
	 * It can be either a string (e.g. `'tbl_user, tbl_post'`) or an array (e.g. `array('tbl_user', 'tbl_post')`).
	 * @see from()
	 */
	public $from;
	/**
	 * @var boolean whether to select distinct rows of data only. If this is set true,
	 * the SELECT clause would be changed to SELECT DISTINCT.
	 */
	public $distinct;
	/**
	 * @var string|array query condition. This refers to the WHERE clause in a SQL statement.
	 * For example, `age > 31 AND team = 1`.
	 * @see where()
	 */
	public $where;
	/**
	 * @var integer maximum number of records to be returned. If not set or less than 0, it means no limit.
	 */
	public $limit;
	/**
	 * @var integer zero-based offset from where the records are to be returned. If not set or
	 * less than 0, it means starting from the beginning.
	 */
	public $offset;
	/**
	 * @var string|array how to sort the query results. This refers to the ORDER BY clause in a SQL statement.
	 * It can be either a string (e.g. `'id ASC, name DESC'`) or an array (e.g. `array('id ASC', 'name DESC')`).
	 */
	public $orderBy;
	/**
	 * @var string|array how to group the query results. This refers to the GROUP BY clause in a SQL statement.
	 * It can be either a string (e.g. `'company, department'`) or an array (e.g. `array('company', 'department')`).
	 */
	public $groupBy;
	/**
	 * @var string|array how to join with other tables. This refers to the JOIN clause in a SQL statement.
	 * It can either a string (e.g. `'LEFT JOIN tbl_user ON tbl_user.id=author_id'`) or an array (e.g.
	 * `array('LEFT JOIN tbl_user ON tbl_user.id=author_id', 'LEFT JOIN tbl_team ON tbl_team.id=team_id')`).
	 * @see join()
	 */
	public $join;
	/**
	 * @var string|array the condition to be applied in the GROUP BY clause.
	 * It can be either a string or an array. Please refer to [[where()]] on how to specify the condition.
	 */
	public $having;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 */
	public $params;
	/**
	 * @var string|array the UNION clause(s) in a SQL statement. This can be either a string
	 * representing a single UNION clause or an array representing multiple UNION clauses.
	 * Each union clause can be a string or a `Query` object which refers to the SQL statement.
	 */
	public $union;
	/**
	 * @var array the operation that this query represents. This refers to the method call as well as
	 * the corresponding parameters for constructing a non-query SQL statement (e.g. INSERT, CREATE TABLE).
	 * This property is mainly maintained by methods such as [[insert()]], [[update()]], [[createTable()]].
	 * If this property is not set, it means this query represents a SELECT statement.
	 */
	public $operation;

	/**
	/**
	 * Sets the SELECT part of the query.
	 * @param mixed $columns the columns to be selected. Defaults to '*', meaning all columns.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * Columns can contain table prefixes (e.g. "tbl_user.id") and/or column aliases (e.g. "tbl_user.id AS user_id").
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @param boolean $distinct whether to use 'SELECT DISTINCT'.
	 * @param string $option additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 * @return Query the query object itself
	 */
	public function select($columns = '*', $distinct = false, $option = '')
	{
		$this->select = $columns;
		$this->distinct = $distinct;
		$this->selectOption = $option;
		return $this;
	}

	/**
	 * Sets the FROM part of the query.
	 * @param mixed $tables the table(s) to be selected from. This can be either a string (e.g. 'tbl_user')
	 * or an array (e.g. array('tbl_user', 'tbl_profile')) specifying one or several table names.
	 * Table names can contain schema prefixes (e.g. 'public.tbl_user') and/or table aliases (e.g. 'tbl_user u').
	 * The method will automatically quote the table names unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return Query the query object itself
	 */
	public function from($tables)
	{
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
	 * If the latter, it must be in the format of `array(operator, operand1, operand2, ...)`,
	 * where the operator can be one of the followings, and the possible operands depend on the corresponding
	 * operator:
	 *
	 * - `and`: the operands should be concatenated together using `AND`. For example,
	 * `array('and', 'id=1', 'id=2')` will generate `id=1 AND id=2`. If an operand is an array,
	 * it will be converted into a string using the rules described here. For example,
	 * `array('and', 'type=1', array('or', 'id=1', 'id=2'))` will generate `type=1 AND (id=1 OR id=2)`.
	 * The method will NOT do any quoting or escaping.
	 *
	 * - `or`: similar to the `and` operator except that the operands are concatenated using `OR`.
	 *
	 * - `between`: operand 1 should be the column name, and operand 2 and 3 should be the
	 * starting and ending values of the range that the column is in.
	 * For example, `array('between', 'id', 1, 10)` will generate `id BETWEEN 1 AND 10`.
	 *
	 * - `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
	 * in the generated condition.
	 *
	 * - `in`: operand 1 should be a column or DB expression, and operand 2 be an array representing
	 * the range of the values that the column or DB expression should be in. For example,
	 * `array('in', 'id', array(1,2,3))` will generate `id IN (1,2,3)`.
	 * The method will properly quote the column name and escape values in the range.
	 *
	 * - `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.
	 *
	 * - `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
	 * the values that the column or DB expression should be like.
	 * For example, `array('like', 'name', '%tester%')` will generate `name LIKE '%tester%'`.
	 * When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
	 * using `AND`. For example, `array('like', 'name', array('%test%', '%sample%'))` will generate
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
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
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
	 * Appends an INNER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $condition the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
	 */
	public function join($table, $condition, $params = array())
	{
		$this->join[] = array('JOIN', $table, $condition);
		return $this->addParams($params);
	}

	/**
	 * Appends a LEFT OUTER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $condition the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
	 */
	public function leftJoin($table, $condition, $params = array())
	{
		$this->join[] = array('LEFT JOIN', $table, $condition);
		return $this->addParams($params);
	}

	/**
	 * Appends a RIGHT OUTER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param string|array $condition the join condition that should appear in the ON part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Query the query object itself
	 */
	public function rightJoin($table, $condition, $params = array())
	{
		$this->join[] = array('RIGHT JOIN', $table, $condition);
		return $this->addParams($params);
	}

	/**
	 * Appends a CROSS JOIN part to the query.
	 * Note that not all DBMS support CROSS JOIN.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return Query the query object itself
	 */
	public function crossJoin($table)
	{
		$this->join[] = array('CROSS JOIN', $table);
		return $this;
	}

	/**
	 * Appends a NATURAL JOIN part to the query.
	 * Note that not all DBMS support NATURAL JOIN.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return Query the query object itself
	 */
	public function naturalJoin($table)
	{
		$this->join[] = array('NATURAL JOIN', $table);
		return $this;
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
		if (empty($this->groupBy)) {
			$this->groupBy = $columns;
		} else {
			if (!is_array($this->groupBy)) {
				$this->groupBy = preg_split('/\s*,\s*/', trim($this->groupBy), -1, PREG_SPLIT_NO_EMPTY);
			}
			if (!is_array($columns)) {
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}
			$this->groupBy = array_merge($this->groupBy, $columns);
		}
		return $this;
	}

	/**
	 * Sets the HAVING part of the query.
	 * @param string|array $condition the conditions to be put after HAVING.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query
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
	 * @param array $params the parameters (name=>value) to be bound to the query
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
	 * @param array $params the parameters (name=>value) to be bound to the query
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
	 * Sets the ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
	 * @see addOrderBy()
	 */
	public function orderBy($columns)
	{
		$this->orderBy = $columns;
		return $this;
	}

	/**
	 * Adds additional ORDER BY columns to the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
	 * @see orderBy()
	 */
	public function addOrderBy($columns)
	{
		if (empty($this->orderBy)) {
			$this->orderBy = $columns;
		} else {
			if (!is_array($this->orderBy)) {
				$this->orderBy = preg_split('/\s*,\s*/', trim($this->orderBy), -1, PREG_SPLIT_NO_EMPTY);
			}
			if (!is_array($columns)) {
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			}
			$this->orderBy = array_merge($this->orderBy, $columns);
		}
		return $this;
	}

	/**
	 * Sets the LIMIT part of the query.
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
	 * @param integer $offset the offset
	 * @return Query the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string $sql the SQL statement to be appended using UNION
	 * @return Query the query object itself
	 */
	public function union($sql)
	{
		$this->union[] = $sql;
		return $this;
	}

	/**
	 * Sets the parameters to be bound to the query.
	 * @param array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
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
	 * @param array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 * @return Query the query object itself
	 * @see params()
	 */
	public function addParams($params)
	{
		foreach ($params as $name => $value) {
			if (is_integer($name)) {
				$this->params[] = $value;
			} else {
				$this->params[$name] = $value;
			}
		}
		return $this;
	}

	/**
	 * Creates and executes an INSERT SQL statement.
	 * The method will properly escape the column names, and bind the values to be inserted.
	 * @param string $table the table that new rows will be inserted into.
	 * @param array $columns the column data (name=>value) to be inserted into the table.
	 * @return Query the query object itself
	 */
	public function insert($table, $columns)
	{
		$this->operation = array(__FUNCTION__, $table, $columns, array());
		return $this;
	}

	/**
	 * Creates and executes an UPDATE SQL statement.
	 * The method will properly escape the column names and bind the values to be updated.
	 * @param string $table the table to be updated.
	 * @param array $columns the column data (name=>value) to be updated.
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters to be bound to the query.
	 * @return Query the query object itself
	 */
	public function update($table, $columns, $condition = '', $params = array())
	{
		$this->addParams($params);
		$this->operation = array(__FUNCTION__, $table, $columns, $condition, array());
		return $this;
	}

	/**
	 * Creates and executes a DELETE SQL statement.
	 * @param string $table the table where the data will be deleted from.
	 * @param string|array $condition the conditions that will be put in the WHERE part.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters to be bound to the query.
	 * @return Query the query object itself
	 */
	public function delete($table, $condition = '', $params = array())
	{
		$this->operation = array(__FUNCTION__, $table, $condition);
		return $this->addParams($params);
	}

	/**
	 * Builds and executes a SQL statement for creating a new DB table.
	 *
	 * The columns in the new  table should be specified as name-definition pairs (e.g. 'name'=>'string'),
	 * where name stands for a column name which will be properly quoted by the method, and definition
	 * stands for the column type which can contain an abstract DB type.
	 * The method [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the abstract column types to physical ones. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 *
	 * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
	 * inserted into the generated SQL.
	 *
	 * @param string $table the name of the table to be created. The name will be properly quoted by the method.
	 * @param array $columns the columns (name=>definition) in the new table.
	 * @param string $options additional SQL fragment that will be appended to the generated SQL.
	 * @return Query the query object itself
	 */
	public function createTable($table, $columns, $options = null)
	{
		$this->operation = array(__FUNCTION__, $table, $columns, $options);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for renaming a DB table.
	 * @param string $table the table to be renamed. The name will be properly quoted by the method.
	 * @param string $newName the new table name. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function renameTable($table, $newName)
	{
		$this->operation = array(__FUNCTION__, $table, $newName);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB table.
	 * @param string $table the table to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropTable($table)
	{
		$this->operation = array(__FUNCTION__, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for truncating a DB table.
	 * @param string $table the table to be truncated. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function truncateTable($table)
	{
		$this->operation = array(__FUNCTION__, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for adding a new DB column.
	 * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
	 * @param string $column the name of the new column. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Query the query object itself
	 */
	public function addColumn($table, $column, $type)
	{
		$this->operation = array(__FUNCTION__, $table, $column, $type);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping a DB column.
	 * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
	 * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropColumn($table, $column)
	{
		$this->operation = array(__FUNCTION__, $table, $column);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for renaming a column.
	 * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
	 * @param string $name the old name of the column. The name will be properly quoted by the method.
	 * @param string $newName the new name of the column. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function renameColumn($table, $name, $newName)
	{
		$this->operation = array(__FUNCTION__, $table, $name, $newName);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for changing the definition of a column.
	 * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
	 * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
	 * @param string $type the column type. [[\yii\db\dao\QueryBuilder::getColumnType()]] will be called
	 * to convert the give column type to the physical one. For example, `string` will be converted
	 * as `varchar(255)`, and `string not null` becomes `varchar(255) not null`.
	 * @return Query the query object itself
	 */
	public function alterColumn($table, $column, $type)
	{
		$this->operation = array(__FUNCTION__, $table, $column, $type);
		return $this;
	}

	/**
	 * Builds a SQL statement for adding a foreign key constraint to an existing table.
	 * The method will properly quote the table and column names.
	 * @param string $name the name of the foreign key constraint.
	 * @param string $table the table that the foreign key constraint will be added to.
	 * @param string $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas.
	 * @param string $refTable the table that the foreign key references to.
	 * @param string $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas.
	 * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
	 * @return Query the query object itself
	 */
	public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
	{
		$this->operation = array(__FUNCTION__, $name, $table, $columns, $refTable, $refColumns, $delete, $update);
		return $this;
	}

	/**
	 * Builds a SQL statement for dropping a foreign key constraint.
	 * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropForeignKey($name, $table)
	{
		$this->operation = array(__FUNCTION__, $name, $table);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for creating a new index.
	 * @param string $name the name of the index. The name will be properly quoted by the method.
	 * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
	 * @param string $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
	 * by commas. The column names will be properly quoted by the method.
	 * @param boolean $unique whether to add UNIQUE constraint on the created index.
	 * @return Query the query object itself
	 */
	public function createIndex($name, $table, $columns, $unique = false)
	{
		$this->operation = array(__FUNCTION__, $name, $table, $columns, $unique);
		return $this;
	}

	/**
	 * Builds and executes a SQL statement for dropping an index.
	 * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
	 * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
	 * @return Query the query object itself
	 */
	public function dropIndex($name, $table)
	{
		$this->operation = array(__FUNCTION__, $name, $table);
		return $this;
	}

	/**
	 * Generates and returns the SQL statement according to this query.
	 * @param Connection $connection the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return string the generated SQL statement
	 */
	public function getSql($connection = null)
	{
		if ($connection === null) {
			$connection = \Yii::$application->db;
		}
		return $connection->getQueryBuilder()->build($this);
	}

	/**
	 * Creates a DB command that can be used to execute this query.
	 * @param Connection $connection the database connection used to generate the SQL statement.
	 * If this parameter is not given, the `db` application component will be used.
	 * @return Command the created DB command instance.
	 */
	public function createCommand($connection = null)
	{
		if ($connection === null) {
			$connection = \Yii::$application->db;
		}
		return $connection->createCommand($this);
	}

	/**
	 * Resets the query object to its original state.
	 * @return Query the query object itself
	 */
	public function reset()
	{
		foreach (get_object_vars($this) as $name => $value) {
			$this->$name = null;
		}
		return $this;
	}

	/**
	 * Returns the query in terms of an array.
	 * The array keys are the query property names, and the array values
	 * the corresponding property values.
	 * @param boolean $includeEmptyValues whether to include empty property values in the result.
	 * @return array the array representation of the criteria
	 */
	public function toArray($includeEmptyValues = false)
	{
		return $includeEmptyValues ? get_object_vars($this) : array_filter(get_object_vars($this));
	}

	/**
	 * Merges this query with another one.
	 *
	 * The merging is done according to the following rules:
	 *
	 * - [[select]]: the union of both queries' [[select]] property values.
	 * - [[selectOption]], [[distinct]], [[limit]], [[offset]]: the new query
	 * takes precedence over this query.
	 *  - [[where]], [[having]]: the new query's corresponding property value
	 * will be 'AND' together with the existing one.
	 * - [[params]], [[orderBy]], [[groupBy]], [[join]], [[union]]: the new query's
	 * corresponding property value will be appended to the existing one.
	 *
	 * In general, the merging makes the resulting query more restrictive and specific.
	 * @param array|Query $query the new query to be merged with this query.
	 * @return Query the query object itself
	 */
	public function mergeWith($query)
	{
		if (is_array($query)) {
			$class = '\\' . get_class($this);
			$query = $class::newInstance($query);
		}

		if ($this->select !== $query->select) {
			if (empty($this->select)) {
				$this->select = $query->select;
			} elseif (!empty($query->select)) {
				$select1 = is_string($this->select) ? preg_split('/\s*,\s*/', trim($this->select), -1, PREG_SPLIT_NO_EMPTY) : $this->select;
				$select2 = is_string($query->select) ? preg_split('/\s*,\s*/', trim($query->select), -1, PREG_SPLIT_NO_EMPTY) : $query->select;
				$this->select = array_merge($select1, array_diff($select2, $select1));
			}
		}

		if ($query->selectOption !== null) {
			$this->selectOption = $query->selectOption;
		}

		if ($query->distinct !== null) {
			$this->distinct = $query->distinct;
		}

		if ($query->limit !== null) {
			$this->limit = $query->limit;
		}

		if ($query->offset !== null) {
			$this->offset = $query->offset;
		}

		if ($query->where !== null) {
			$this->andWhere($query->where);
		}

		if ($query->having !== null) {
			$this->andHaving($query->having);
		}

		if ($query->params !== null) {
			$this->addParams($query->params);
		}

		if ($query->orderBy !== null) {
			$this->addOrderBy($query->orderBy);
		}

		if ($query->groupBy !== null) {
			$this->addGroupBy($query->groupBy);
		}

		if ($query->join !== null) {
			if (empty($this->join)) {
				$this->join = $query->join;
			} else {
				if (!is_array($this->join)) {
					$this->join = array($this->join);
				}
				if (is_array($query->join)) {
					$this->join = array_merge($this->join, $query->join);
				} else {
					$this->join[] = $query->join;
				}
			}
		}

		if ($query->union !== null) {
			if (empty($this->union)) {
				$this->union = $query->union;
			} else {
				if (!is_array($this->union)) {
					$this->union = array($this->union);
				}
				if (is_array($query->union)) {
					$this->union = array_merge($this->union, $query->union);
				} else {
					$this->union[] = $query->union;
				}
			}
		}

		return $this;
	}
}

<?php
/**
 * BaseQuery class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * BaseQuery is the base class that represents a SQL SELECT statement in a DBMS-independent way.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseQuery extends \yii\base\Component
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
	 * @var boolean whether to select distinct rows of data only. If this is set true,
	 * the SELECT clause would be changed to SELECT DISTINCT.
	 */
	public $distinct;
	/**
	 * @var string|array the table(s) to be selected from. This refers to the FROM clause in a SQL statement.
	 * It can be either a string (e.g. `'tbl_user, tbl_post'`) or an array (e.g. `array('tbl_user', 'tbl_post')`).
	 * @see from()
	 */
	public $from;
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
	 * It can be either a string (e.g. `'LEFT JOIN tbl_user ON tbl_user.id=author_id'`) or an array (e.g.
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
	 * @var string|BaseQuery[] the UNION clause(s) in a SQL statement. This can be either a string
	 * representing a single UNION clause or an array representing multiple UNION clauses.
	 * Each union clause can be a string or a `BaseQuery` object which refers to the SQL statement.
	 */
	public $union;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 */
	public $params;

	/**
	 * Sets the SELECT part of the query.
	 * @param string|array $columns the columns to be selected.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * Columns can contain table prefixes (e.g. "tbl_user.id") and/or column aliases (e.g. "tbl_user.id AS user_id").
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @param string $option additional option that should be appended to the 'SELECT' keyword. For example,
	 * in MySQL, the option 'SQL_CALC_FOUND_ROWS' can be used.
	 * @return BaseQuery the query object itself
	 */
	public function select($columns, $option = null)
	{
		$this->select = $columns;
		$this->selectOption = $option;
		return $this;
	}

	/**
	 * Sets the value indicating whether to SELECT DISTINCT or not.
	 * @param bool $value whether to SELECT DISTINCT or not.
	 * @return BaseQuery the query object itself
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
	 * @return BaseQuery the query object itself
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
	 * If the latter, it must be in one of the following two formats:
	 *
	 * - hash format: `array('column1' => value1, 'column2' => value2, ...)`
	 * - operator format: `array(operator, operand1, operand2, ...)`
	 *
	 * A condition in hash format represents the following SQL expression in general:
	 * `column1=value1 AND column2=value2 AND ...`. In case when a value is an array,
	 * an `IN` expression will be generated. And if a value is null, `IS NULL` will be used
	 * in the generated expression. Below are some examples:
	 *
	 * - `array('type'=>1, 'status'=>2)` generates `(type=1) AND (status=2)`.
	 * - `array('id'=>array(1,2,3), 'status'=>2)` generates `(id IN (1,2,3)) AND (status=2)`.
	 * - `array('status'=>null) generates `status IS NULL`.
	 *
	 * A condition in operator format generates the SQL expression according to the specified operator, which
	 * can be one of the followings:
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return BaseQuery the query object itself
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
	 * @return BaseQuery the query object itself
	 * @see addGroup()
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
	 * @return BaseQuery the query object itself
	 * @see group()
	 */
	public function addGroup($columns)
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * @return BaseQuery the query object itself
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
	 * @return BaseQuery the query object itself
	 * @see addOrder()
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
	 * @return BaseQuery the query object itself
	 * @see order()
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
	 * @return BaseQuery the query object itself
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset
	 * @return BaseQuery the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string|BaseQuery $sql the SQL statement to be appended using UNION
	 * @return BaseQuery the query object itself
	 */
	public function union($sql)
	{
		$this->union[] = $sql;
		return $this;
	}

	/**
	 * Sets the parameters to be bound to the query.
	 * @param array $params list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 * @return BaseQuery the query object itself
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
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 * @return BaseQuery the query object itself
	 * @see params()
	 */
	public function addParams($params)
	{
		if ($params !== array()) {
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
	 * Merges this query with another one.
	 * If a property of `$query` is not null, it will be used to overwrite
	 * the corresponding property of `$this`.
	 * @param BaseQuery $query the new query to be merged with this query.
	 * @return BaseQuery the query object itself
	 */
	public function mergeWith(BaseQuery $query)
	{
		$properties = array(
			'select', 'selectOption', 'distinct', 'from',
			'where', 'limit', 'offset', 'orderBy', 'groupBy',
			'join', 'having', 'union', 'params',
		);
		// todo: incorrect, do we need it? should we provide a configure() method instead?
		foreach ($properties as $name => $value) {
			if ($value !== null) {
				$this->$name = $value;
			}
		}
		return $this;
	}
}

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
 * Query represents the components in a DB query.
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
	 */
	public $join;
	/**
	 * @var string|array the condition to be applied in the GROUP BY clause.
	 * It can be either a string or an array. Please refer to [[where]] on how to specify the condition.
	 */
	public $having;
	/**
	 * @var array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 */
	public $params = array();
	/**
	 * @var string|array the UNION clause(s) in a SQL statement. This can be either a string
	 * representing a single UNION clause or an array representing multiple UNION clauses.
	 * Each union clause can be a string or a `Query` object which refers to the SQL statement.
	 */
	public $union;


	/**
	 * @param Connection $connection
	 * @return string
	 */
	public function getSql($connection)
	{
		return $connection->getQueryBuilder()->build($this);
	}

	public function addParams($params)
	{
		foreach ($params as $name => $value) {
			if (is_integer($name)) {
				$this->params[] = $value;
			} else {
				$this->params[$name] = $value;
			}
		}
	}

	public function mergeWith($query, $useAnd = true)
	{
		$and = $useAnd ? 'AND' : 'OR';
		if (is_array($query)) {
			$query = new self($query);
		}

		if ($this->select !== $query->select) {
			if ($this->select === '*') {
				$this->select = $query->select;
			} elseif ($query->select !== '*') {
				$select1 = is_string($this->select) ? preg_split('/\s*,\s*/', trim($this->select), -1, PREG_SPLIT_NO_EMPTY) : $this->select;
				$select2 = is_string($query->select) ? preg_split('/\s*,\s*/', trim($query->select), -1, PREG_SPLIT_NO_EMPTY) : $query->select;
				$this->select = array_merge($select1, array_diff($select2, $select1));
			}
		}

		if ($this->selectOption !== $query->selectOption) {
			if ($this->selectOption === null) {
				$this->selectOption = $query->selectOption;
			} elseif ($query->selectOption !== null) {
				$this->selectOption .= ' ' . $query->selectOption;
			}
		}

		if ($query->distinct) {
			$this->distinct = $query->distinct;
		}

		if ($this->where !== $query->where) {
			if (empty($this->where)) {
				$this->where = $query->where;
			} elseif (!empty($query->where)) {
				$this->where = array('AND', $this->where, $query->where);
			}
		}

		if ($this->params !== $query->params) {
			$this->params = $this->addParams($query->params);
		}

		if ($query->limit !== null) {
			$this->limit = $query->limit;
		}

		if ($query->offset !== null) {
			$this->offset = $query->offset;
		}

		if ($this->orderBy !== $query->orderBy) {
			if (empty($this->orderBy)) {
				$this->orderBy = $query->orderBy;
			} elseif (!empty($query->orderBy)) {
				if (!is_array($this->orderBy)) {
					$this->orderBy = array($this->orderBy);
				}
				if (is_array($query->orderBy)) {
					$this->orderBy = array_merge($this->orderBy, $query->orderBy);
				} else {
					$this->orderBy[] = $query->orderBy;
				}
			}
		}

		if ($this->groupBy !== $query->groupBy) {
			if (empty($this->groupBy)) {
				$this->groupBy = $query->groupBy;
			} elseif (!empty($query->groupBy)) {
				if (!is_array($this->groupBy)) {
					$this->groupBy = array($this->groupBy);
				}
				if (is_array($query->groupBy)) {
					$this->groupBy = array_merge($this->groupBy, $query->groupBy);
				} else {
					$this->groupBy[] = $query->groupBy;
				}
			}
		}

		if ($this->join !== $query->join) {
			if (empty($this->join)) {
				$this->join = $query->join;
			} elseif (!empty($query->join)) {
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

		if ($this->having !== $query->having) {
			if (empty($this->having)) {
				$this->having = $query->having;
			} elseif (!empty($query->having)) {
				$this->having = array('AND', $this->having, $query->having);
			}
		}

		if ($this->union !== $query->union) {
			if (empty($this->union)) {
				$this->union = $query->union;
			} elseif (!empty($query->union)) {
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
	}

	/**
	 * Appends a condition to the existing {@link condition}.
	 * The new condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The new condition can also be an array. In this case, all elements in the array
	 * will be concatenated together via the operator.
	 * This method handles the case when the existing condition is empty.
	 * After calling this method, the {@link condition} property will be modified.
	 * @param mixed $condition the new condition. It can be either a string or an array of strings.
	 * @param string $operator the operator to join different conditions. Defaults to 'AND'.
	 * @return Query the criteria object itself
	 */
	public function addCondition($condition, $operator = 'AND')
	{
		if (is_array($condition)) {
			if ($condition === array()) {
				return $this;
			}
			$condition = '(' . implode(') ' . $operator . ' (', $condition) . ')';
		}
		if ($this->condition === '') {
			$this->condition = $condition;
		} else
		{
			$this->condition = '(' . $this->condition . ') ' . $operator . ' (' . $condition . ')';
		}
		return $this;
	}

	/**
	 * Appends a search condition to the existing {@link condition}.
	 * The search condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The search condition is generated using the SQL LIKE operator with the given column name and
	 * search keyword.
	 * @param string $column the column name (or a valid SQL expression)
	 * @param string $keyword the search keyword. This interpretation of the keyword is affected by the next parameter.
	 * @param boolean $escape whether the keyword should be escaped if it contains characters % or _.
	 * When this parameter is true (default), the special characters % (matches 0 or more characters)
	 * and _ (matches a single character) will be escaped, and the keyword will be surrounded with a %
	 * character on both ends. When this parameter is false, the keyword will be directly used for
	 * matching without any change.
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @param string $like the LIKE operator. Defaults to 'LIKE'. You may also set this to be 'NOT LIKE'.
	 * @return Query the criteria object itself
	 */
	public function addSearchCondition($column, $keyword, $escape = true, $operator = 'AND', $like = 'LIKE')
	{
		if ($keyword == '') {
			return $this;
		}
		if ($escape) {
			$keyword = '%' . strtr($keyword, array('%' => '\%', '_' => '\_', '\\' => '\\\\')) . '%';
		}
		$condition = $column . " $like " . self::PARAM_PREFIX . self::$paramCount;
		$this->params[self::PARAM_PREFIX . self::$paramCount++] = $keyword;
		return $this->addCondition($condition, $operator);
	}

	/**
	 * Appends an IN condition to the existing {@link condition}.
	 * The IN condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The IN condition is generated by using the SQL IN operator which requires the specified
	 * column value to be among the given list of values.
	 * @param string $column the column name (or a valid SQL expression)
	 * @param array $values list of values that the column value should be in
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return Query the criteria object itself
	 */
	public function addInCondition($column, $values, $operator = 'AND')
	{
		if (($n = count($values)) < 1) {
			return $this->addCondition('0=1', $operator);
		} // 0=1 is used because in MSSQL value alone can't be used in WHERE
		if ($n === 1) {
			$value = reset($values);
			if ($value === null) {
				return $this->addCondition($column . ' IS NULL');
			}
			$condition = $column . '=' . self::PARAM_PREFIX . self::$paramCount;
			$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;
		} else
		{
			$params = array();
			foreach ($values as $value)
			{
				$params[] = self::PARAM_PREFIX . self::$paramCount;
				$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;
			}
			$condition = $column . ' IN (' . implode(', ', $params) . ')';
		}
		return $this->addCondition($condition, $operator);
	}

	/**
	 * Appends an NOT IN condition to the existing {@link condition}.
	 * The NOT IN condition and the existing condition will be concatenated via the specified operator
	 * which defaults to 'AND'.
	 * The NOT IN condition is generated by using the SQL NOT IN operator which requires the specified
	 * column value to be among the given list of values.
	 * @param string $column the column name (or a valid SQL expression)
	 * @param array $values list of values that the column value should not be in
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return Query the criteria object itself
	 */
	public function addNotInCondition($column, $values, $operator = 'AND')
	{
		if (($n = count($values)) < 1) {
			return $this;
		}
		if ($n === 1) {
			$value = reset($values);
			if ($value === null) {
				return $this->addCondition($column . ' IS NOT NULL');
			}
			$condition = $column . '!=' . self::PARAM_PREFIX . self::$paramCount;
			$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;
		} else
		{
			$params = array();
			foreach ($values as $value)
			{
				$params[] = self::PARAM_PREFIX . self::$paramCount;
				$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;
			}
			$condition = $column . ' NOT IN (' . implode(', ', $params) . ')';
		}
		return $this->addCondition($condition, $operator);
	}

	/**
	 * Appends a condition for matching the given list of column values.
	 * The generated condition will be concatenated to the existing {@link condition}
	 * via the specified operator which defaults to 'AND'.
	 * The condition is generated by matching each column and the corresponding value.
	 * @param array $columns list of column names and values to be matched (name=>value)
	 * @param string $columnOperator the operator to concatenate multiple column matching condition. Defaults to 'AND'.
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return Query the criteria object itself
	 */
	public function addColumnCondition($columns, $columnOperator = 'AND', $operator = 'AND')
	{
		$params = array();
		foreach ($columns as $name => $value)
		{
			if ($value === null) {
				$params[] = $name . ' IS NULL';
			} else
			{
				$params[] = $name . '=' . self::PARAM_PREFIX . self::$paramCount;
				$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;
			}
		}
		return $this->addCondition(implode(" $columnOperator ", $params), $operator);
	}

	/**
	 * Adds a comparison expression to the {@link condition} property.
	 *
	 * This method is a helper that appends to the {@link condition} property
	 * with a new comparison expression. The comparison is done by comparing a column
	 * with the given value using some comparison operator.
	 *
	 * The comparison operator is intelligently determined based on the first few
	 * characters in the given value. In particular, it recognizes the following operators
	 * if they appear as the leading characters in the given value:
	 * <ul>
	 * <li><code>&lt;</code>: the column must be less than the given value.</li>
	 * <li><code>&gt;</code>: the column must be greater than the given value.</li>
	 * <li><code>&lt;=</code>: the column must be less than or equal to the given value.</li>
	 * <li><code>&gt;=</code>: the column must be greater than or equal to the given value.</li>
	 * <li><code>&lt;&gt;</code>: the column must not be the same as the given value.
	 * Note that when $partialMatch is true, this would mean the value must not be a substring
	 * of the column.</li>
	 * <li><code>=</code>: the column must be equal to the given value.</li>
	 * <li>none of the above: the column must be equal to the given value. Note that when $partialMatch
	 * is true, this would mean the value must be the same as the given value or be a substring of it.</li>
	 * </ul>
	 *
	 * Note that any surrounding white spaces will be removed from the value before comparison.
	 * When the value is empty, no comparison expression will be added to the search condition.
	 *
	 * @param string $column the name of the column to be searched
	 * @param mixed $value the column value to be compared with. If the value is a string, the aforementioned
	 * intelligent comparison will be conducted. If the value is an array, the comparison is done
	 * by exact match of any of the value in the array. If the string or the array is empty,
	 * the existing search condition will not be modified.
	 * @param boolean $partialMatch whether the value should consider partial text match (using LIKE and NOT LIKE operators).
	 * Defaults to false, meaning exact comparison.
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @param boolean $escape whether the value should be escaped if $partialMatch is true and
	 * the value contains characters % or _. When this parameter is true (default),
	 * the special characters % (matches 0 or more characters)
	 * and _ (matches a single character) will be escaped, and the value will be surrounded with a %
	 * character on both ends. When this parameter is false, the value will be directly used for
	 * matching without any change.
	 * @return Query the criteria object itself
	 */
	public function compare($column, $value, $partialMatch = false, $operator = 'AND', $escape = true)
	{
		if (is_array($value)) {
			if ($value === array()) {
				return $this;
			}
			return $this->addInCondition($column, $value, $operator);
		} else
		{
			$value = "$value";
		}

		if (preg_match('/^(?:\s*(<>|<=|>=|<|>|=))?(.*)$/', $value, $matches)) {
			$value = $matches[2];
			$op = $matches[1];
		} else
		{
			$op = '';
		}

		if ($value === '') {
			return $this;
		}

		if ($partialMatch) {
			if ($op === '') {
				return $this->addSearchCondition($column, $value, $escape, $operator);
			}
			if ($op === '<>') {
				return $this->addSearchCondition($column, $value, $escape, $operator, 'NOT LIKE');
			}
		} elseif ($op === '')
		{
			$op = '=';
		}

		$this->addCondition($column . $op . self::PARAM_PREFIX . self::$paramCount, $operator);
		$this->params[self::PARAM_PREFIX . self::$paramCount++] = $value;

		return $this;
	}

	/**
	 * Adds a between condition to the {@link condition} property.
	 *
	 * The new between condition and the existing condition will be concatenated via
	 * the specified operator which defaults to 'AND'.
	 * If one or both values are empty then the condition is not added to the existing condition.
	 * This method handles the case when the existing condition is empty.
	 * After calling this method, the {@link condition} property will be modified.
	 * @param string $column the name of the column to search between.
	 * @param string $valueStart the beginning value to start the between search.
	 * @param string $valueEnd the ending value to end the between search.
	 * @param string $operator the operator used to concatenate the new condition with the existing one.
	 * Defaults to 'AND'.
	 * @return Query the criteria object itself
	 */
	public function addBetweenCondition($column, $valueStart, $valueEnd, $operator = 'AND')
	{
		if ($valueStart === '' || $valueEnd === '') {
			return $this;
		}

		$paramStart = self::PARAM_PREFIX . self::$paramCount++;
		$paramEnd = self::PARAM_PREFIX . self::$paramCount++;
		$this->params[$paramStart] = $valueStart;
		$this->params[$paramEnd] = $valueEnd;
		$condition = "$column BETWEEN $paramStart AND $paramEnd";

		if ($this->condition === '') {
			$this->condition = $condition;
		} else
		{
			$this->condition = '(' . $this->condition . ') ' . $operator . ' (' . $condition . ')';
		}
		return $this;
	}

	public function reset()
	{
		$this->select = null;
		$this->selectOption = null;
		$this->from = null;
		$this->distinct = null;
		$this->where = null;
		$this->limit = null;
		$this->offset = null;
		$this->orderBy = null;
		$this->groupBy = null;
		$this->join = null;
		$this->having = null;
		$this->params = array();
		$this->union = null;
	}

	public function fromArray($array)
	{
		$this->reset();
		foreach (array('select', 'selectOption', 'from', 'distinct', 'where', 'limit', 'offset', 'orderBy', 'groupBy', 'join', 'having', 'params', 'union') as $name) {
			if (isset($array[$name])) {
				$this->$name = $array[$name];
			}
		}
	}

	/**
	 * @return array the array representation of the criteria
	 */
	public function toArray()
	{
		$result = array();
		foreach (array('select', 'selectOption', 'from', 'distinct', 'where', 'limit', 'offset', 'orderBy', 'groupBy', 'join', 'having', 'params', 'union') as $name) {
			if (!empty($this->$name)) {
				$result[$name] = $this->$name;
			}
		}
		return $result;
	}
}

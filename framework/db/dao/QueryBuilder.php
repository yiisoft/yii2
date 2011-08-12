<?php
/**
 * This file contains the Command class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\dao;

/**
 * QueryBuilder builds a SQL statement based on the specification given as a [[Query]] object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\base\Component
{
	private $_connection;

	public function __construct(Connection $connection)
	{
		$this->_connection = $connection;
	}

	/**
	 * @return CDbConnection the connection associated with this command
	 */
	public function getConnection()
	{
		return $this->_connection;
	}

	public function build($query)
	{
		$clauses = array(
			$this->buildSelect($query->select, $query->distinct),
			$this->buildFrom($query->from),
			$this->buildJoin($query->join),
			$this->buildWhere($query->where),
			$this->buildGroupBy($query->groupBy),
			$this->buildHaving($query->having),
			$this->buildOrderBy($query->orderBy),
			$this->buildLimit($query->offset, $query->limit),
			$this->buildUnion($query->union),
		);

		return implode("\n", array_filter($clauses));
	}

	protected function buildSelect($columns, $distinct)
	{
		$select = $distinct ? 'SELECT DISTINCT' : 'SELECT';

		if (empty($columns)) {
			return $select . ' *';
		}

		if (is_string($columns)) {
			if (strpos($columns, '(') !== false) {
				return $select . ' ' . $columns;
			}
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
		}

		foreach ($columns as $i => $column) {
			if (is_object($column)) {
				$columns[$i] = (string)$column;
			}
			elseif (strpos($column, '(') === false) {
				if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches)) {
					$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' AS ' . $this->_connection->quoteColumnName($matches[2]);
				}
				else {
					$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
		}

		return $select . ' ' . implode(', ', $columns);
	}

	protected function buildFrom($tables)
	{
		if (is_string($tables) && strpos($tables, '(') !== false) {
			return $tables;
		}

		if (!is_array($tables)) {
			$tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ($tables as $i => $table) {
			if (strpos($table, '(') === false) {
				if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches)) { // with alias
					$tables[$i] = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
				}
				else {
					$tables[$i] = $this->_connection->quoteTableName($table);
				}
			}
		}
		return implode(', ', $tables);
	}

			$this->buildJoin($query->join),
			$this->buildWhere($query->where),
			$this->buildGroupBy($query->groupBy),
			$this->buildHaving($query->having),
			$this->buildOrderBy($query->orderBy),
			$this->buildLimit($query->offset, $query->limit),


		if (isset($query['union']))
			$sql .= "\nUNION (\n" . (is_array($query['union']) ? implode("\n) UNION (\n", $query['union']) : $query['union']) . ')';

		return $sql;
	}


	/**
	 * Sets the WHERE part of the query.
	 *
	 * The method requires a $conditions parameter, and optionally a $params parameter
	 * specifying the values to be bound to the query.
	 *
	 * The $conditions parameter should be either a string (e.g. 'id=1') or an array.
	 * If the latter, it must be of the format <code>array(operator, operand1, operand2, ...)</code>,
	 * where the operator can be one of the followings, and the possible operands depend on the corresponding
	 * operator:
	 * <ul>
	 * <li><code>and</code>: the operands should be concatenated together using AND. For example,
	 * array('and', 'id=1', 'id=2') will generate 'id=1 AND id=2'. If an operand is an array,
	 * it will be converted into a string using the same rules described here. For example,
	 * array('and', 'type=1', array('or', 'id=1', 'id=2')) will generate 'type=1 AND (id=1 OR id=2)'.
	 * The method will NOT do any quoting or escaping.</li>
	 * <li><code>or</code>: similar as the <code>and</code> operator except that the operands are concatenated using OR.</li>
	 * <li><code>in</code>: operand 1 should be a column or DB expression, and operand 2 be an array representing
	 * the range of the values that the column or DB expression should be in. For example,
	 * array('in', 'id', array(1,2,3)) will generate 'id IN (1,2,3)'.
	 * The method will properly quote the column name and escape values in the range.</li>
	 * <li><code>not in</code>: similar as the <code>in</code> operator except that IN is replaced with NOT IN in the generated condition.</li>
	 * <li><code>like</code>: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
	 * the values that the column or DB expression should be like.
	 * For example, array('like', 'name', '%tester%') will generate "name LIKE '%tester%'".
	 * When the value range is given as an array, multiple LIKE predicates will be generated and concatenated using AND.
	 * For example, array('like', 'name', array('%test%', '%sample%')) will generate
	 * "name LIKE '%test%' AND name LIKE '%sample%'".
	 * The method will properly quote the column name and escape values in the range.</li>
	 * <li><code>not like</code>: similar as the <code>like</code> operator except that LIKE is replaced with NOT LIKE in the generated condition.</li>
	 * <li><code>or like</code>: similar as the <code>like</code> operator except that OR is used to concatenated the LIKE predicates.</li>
	 * <li><code>or not like</code>: similar as the <code>not like</code> operator except that OR is used to concatenated the NOT LIKE predicates.</li>
	 * </ul>
	 * @param mixed $conditions the conditions that should be put in the WHERE part.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function where($conditions, $params = array())
	{
		$this->_query['where'] = $this->processConditions($conditions);
		foreach ($params as $name => $value)
			$this->params[$name] = $value;
		return $this;
	}

	/**
	 * Appends an INNER JOIN part to the query.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param mixed $conditions the join condition that should appear in the ON part.
	 * Please refer to {@link where} on how to specify conditions.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function join($table, $conditions, $params = array())
	{
		return $this->joinInternal('join', $table, $conditions, $params);
	}

	/**
	 * Sets the GROUP BY part of the query.
	 * @param mixed $columns the columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function group($columns)
	{
		if (is_string($columns) && strpos($columns, '(') !== false)
			$this->_query['group'] = $columns;
		else
		{
			if (!is_array($columns))
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($columns as $i => $column)
			{
				if (is_object($column))
					$columns[$i] = (string)$column;
				elseif (strpos($column, '(') === false)
					$columns[$i] = $this->_connection->quoteColumnName($column);
			}
			$this->_query['group'] = implode(', ', $columns);
		}
		return $this;
	}

	/**
	 * Sets the HAVING part of the query.
	 * @param mixed $conditions the conditions to be put after HAVING.
	 * Please refer to {@link where} on how to specify conditions.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function having($conditions, $params = array())
	{
		$this->_query['having'] = $this->processConditions($conditions);
		foreach ($params as $name => $value)
			$this->params[$name] = $value;
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * @param mixed $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function order($columns)
	{
		if (is_string($columns) && strpos($columns, '(') !== false)
			$this->_query['order'] = $columns;
		else
		{
			if (!is_array($columns))
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($columns as $i => $column)
			{
				if (is_object($column))
					$columns[$i] = (string)$column;
				elseif (strpos($column, '(') === false)
				{
					if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches))
						$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' ' . strtoupper($matches[2]);
					else
						$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
			$this->_query['order'] = implode(', ', $columns);
		}
		return $this;
	}

	/**
	 * Sets the LIMIT part of the query.
	 * @param integer $limit the limit
	 * @param integer $offset the offset
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function limit($limit, $offset = null)
	{
		$this->_query['limit'] = (int)$limit;
		if ($offset !== null)
			$this->offset($offset);
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string $sql the SQL statement to be appended using UNION
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	public function union($sql)
	{
		if (isset($this->_query['union']) && is_string($this->_query['union']))
			$this->_query['union'] = array($this->_query['union']);

		$this->_query['union'][] = $sql;

		return $this;
	}

	/**
	 * Generates the condition string that will be put in the WHERE part
	 * @param mixed $conditions the conditions that will be put in the WHERE part.
	 * @return string the condition string to put in the WHERE part
	 */
	private function buildConditions($conditions)
	{
		if (!is_array($conditions))
			return $conditions;
		elseif ($conditions === array())
			return '';
		$n = count($conditions);
		$operator = strtoupper($conditions[0]);
		if ($operator === 'OR' || $operator === 'AND')
		{
			$parts = array();
			for ($i = 1;$i < $n;++$i)
			{
				$condition = $this->processConditions($conditions[$i]);
				if ($condition !== '')
					$parts[] = '(' . $condition . ')';
			}
			return $parts === array() ? '' : implode(' ' . $operator . ' ', $parts);
		}

		if (!isset($conditions[1], $conditions[2]))
			return '';

		$column = $conditions[1];
		if (strpos($column, '(') === false)
			$column = $this->_connection->quoteColumnName($column);

		$values = $conditions[2];
		if (!is_array($values))
			$values = array($values);

		if ($operator === 'IN' || $operator === 'NOT IN')
		{
			if ($values === array())
				return $operator === 'IN' ? '0=1' : '';
			foreach ($values as $i => $value)
			{
				if (is_string($value))
					$values[$i] = $this->_connection->quoteValue($value);
				else
					$values[$i] = (string)$value;
			}
			return $column . ' ' . $operator . ' (' . implode(', ', $values) . ')';
		}

		if ($operator === 'LIKE' || $operator === 'NOT LIKE' || $operator === 'OR LIKE' || $operator === 'OR NOT LIKE')
		{
			if ($values === array())
				return $operator === 'LIKE' || $operator === 'OR LIKE' ? '0=1' : '';

			if ($operator === 'LIKE' || $operator === 'NOT LIKE')
				$andor = ' AND ';
			else
			{
				$andor = ' OR ';
				$operator = $operator === 'OR LIKE' ? 'LIKE' : 'NOT LIKE';
			}
			$expressions = array();
			foreach ($values as $value)
				$expressions[] = $column . ' ' . $operator . ' ' . $this->_connection->quoteValue($value);
			return implode($andor, $expressions);
		}

		throw new CDbException(Yii::t('yii', 'Unknown operator "{operator}".', array('{operator}' => $operator)));
	}

	/**
	 * Appends an JOIN part to the query.
	 * @param string $type the join type ('join', 'left join', 'right join', 'cross join', 'natural join')
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param mixed $conditions the join condition that should appear in the ON part.
	 * Please refer to {@link where} on how to specify conditions.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Command the command object itself
	 * @since 1.1.6
	 */
	private function joinInternal($type, $table, $conditions = '', $params = array())
	{
		if (strpos($table, '(') === false)
		{
			if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches))  // with alias
				$table = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
			else
				$table = $this->_connection->quoteTableName($table);
		}

		$conditions = $this->processConditions($conditions);
		if ($conditions != '')
			$conditions = ' ON ' . $conditions;

		if (isset($this->_query['join']) && is_string($this->_query['join']))
			$this->_query['join'] = array($this->_query['join']);

		$this->_query['join'][] = strtoupper($type) . ' ' . $table . $conditions;

		foreach ($params as $name => $value)
			$this->params[$name] = $value;
		return $this;
	}
}

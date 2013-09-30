<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\NotSupportedException;

/**
 * QueryBuilder builds a SELECT SQL statement based on the specification given as a [[Query]] object.
 *
 * QueryBuilder can also be used to build SQL statements such as INSERT, UPDATE, DELETE, CREATE TABLE,
 * from a [[Query]] object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\base\Object
{
	/**
	 * @var Connection the database connection.
	 */
	public $db;

	/**
	 * Constructor.
	 * @param Connection $connection the database connection.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($connection, $config = array())
	{
		$this->db = $connection;
		parent::__construct($config);
	}

	/**
	 * Generates a SELECT SQL statement from a [[Query]] object.
	 * @param Query $query the [[Query]] object from which the SQL statement will be generated
	 * @return array the generated SQL statement (the first array element) and the corresponding
	 * parameters to be bound to the SQL statement (the second array element).
	 */
	public function build($query)
	{
		$searchQuery = array();
		$this->buildSelect($searchQuery, $query->select);
//		$this->buildFrom(&$searchQuery, $query->from);
		$this->buildCondition($searchQuery, $query->where);
		$this->buildOrderBy($searchQuery, $query->orderBy);
		$this->buildLimit($searchQuery, $query->limit, $query->offset);

		return $searchQuery;
	}

	/**
	 * Converts an abstract column type into a physical column type.
	 * The conversion is done using the type map specified in [[typeMap]].
	 * The following abstract column types are supported (using MySQL as an example to explain the corresponding
	 * physical types):
	 *
	 * - `pk`: an auto-incremental primary key type, will be converted into "int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY"
	 * - `bigpk`: an auto-incremental primary key type, will be converted into "bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY"
	 * - `string`: string type, will be converted into "varchar(255)"
	 * - `text`: a long string type, will be converted into "text"
	 * - `smallint`: a small integer type, will be converted into "smallint(6)"
	 * - `integer`: integer type, will be converted into "int(11)"
	 * - `bigint`: a big integer type, will be converted into "bigint(20)"
	 * - `boolean`: boolean type, will be converted into "tinyint(1)"
	 * - `float``: float number type, will be converted into "float"
	 * - `decimal`: decimal number type, will be converted into "decimal"
	 * - `datetime`: datetime type, will be converted into "datetime"
	 * - `timestamp`: timestamp type, will be converted into "timestamp"
	 * - `time`: time type, will be converted into "time"
	 * - `date`: date type, will be converted into "date"
	 * - `money`: money type, will be converted into "decimal(19,4)"
	 * - `binary`: binary data type, will be converted into "blob"
	 *
	 * If the abstract type contains two or more parts separated by spaces (e.g. "string NOT NULL"), then only
	 * the first part will be converted, and the rest of the parts will be appended to the converted result.
	 * For example, 'string NOT NULL' is converted to 'varchar(255) NOT NULL'.
	 *
	 * For some of the abstract types you can also specify a length or precision constraint
	 * by prepending it in round brackets directly to the type.
	 * For example `string(32)` will be converted into "varchar(32)" on a MySQL database.
	 * If the underlying DBMS does not support these kind of constraints for a type it will
	 * be ignored.
	 *
	 * If a type cannot be found in [[typeMap]], it will be returned without any change.
	 * @param string $type abstract column type
	 * @return string physical column type.
	 */
	public function getColumnType($type)
	{
		if (isset($this->typeMap[$type])) {
			return $this->typeMap[$type];
		} elseif (preg_match('/^(\w+)\((.+?)\)(.*)$/', $type, $matches)) {
			if (isset($this->typeMap[$matches[1]])) {
				return preg_replace('/\(.+\)/', '(' . $matches[2] . ')', $this->typeMap[$matches[1]]) . $matches[3];
			}
		} elseif (preg_match('/^(\w+)\s+/', $type, $matches)) {
			if (isset($this->typeMap[$matches[1]])) {
				return preg_replace('/^\w+/', $this->typeMap[$matches[1]], $type);
			}
		}
		return $type;
	}

	/**
	 * @param array $columns
	 * @param boolean $distinct
	 * @param string $selectOption
	 * @return string the SELECT clause built from [[query]].
	 */
	public function buildSelect(&$query, $columns)
	{
		if (empty($columns)) {
			return;
		}
		foreach ($columns as $i => $column) {
			if (is_object($column)) {
				$columns[$i] = (string)$column;
			}
		}
		$query['fields'] = $columns;
	}

	/**
	 * @param array $columns
	 * @return string the ORDER BY clause built from [[query]].
	 */
	public function buildOrderBy(&$query, $columns)
	{
		if (empty($columns)) {
			return;
		}
		$orders = array();
		foreach ($columns as $name => $direction) {
			// allow elasticsearch extended syntax as described in http://www.elasticsearch.org/guide/reference/api/search/sort/
			if (is_array($direction)) {
				$orders[] = array($name => $direction);
			} elseif (is_string($direction)) {
				$orders[] = $direction;
			} else {
				$orders[] = array($name => ($direction === Query::SORT_DESC ? 'desc' : 'asc'));
			}
		}
		$query['sort'] = $orders;
	}

	/**
	 * @param integer $limit
	 * @param integer $offset
	 * @return string the LIMIT and OFFSET clauses built from [[query]].
	 */
	public function buildLimit(&$query, $limit, $offset)
	{
		if ($limit !== null && $limit >= 0) {
			$query['size'] = $limit;
		}
		if ($offset > 0) {
			$query['from'] = (int) $offset;
		}
	}

	/**
	 * Parses the condition specification and generates the corresponding SQL expression.
	 * @param string|array $condition the condition specification. Please refer to [[Query::where()]]
	 * on how to specify a condition.
	 * @param array $params the binding parameters to be populated
	 * @return string the generated SQL expression
	 * @throws \yii\db\Exception if the condition is in bad format
	 */
	public function buildCondition(&$query, $condition)
	{
		static $builders = array(
			'AND' => 'buildAndCondition',
			'OR' => 'buildAndCondition',
			'BETWEEN' => 'buildBetweenCondition',
			'NOT BETWEEN' => 'buildBetweenCondition',
			'IN' => 'buildInCondition',
			'NOT IN' => 'buildInCondition',
			'LIKE' => 'buildLikeCondition',
			'NOT LIKE' => 'buildLikeCondition',
			'OR LIKE' => 'buildLikeCondition',
			'OR NOT LIKE' => 'buildLikeCondition',
		);

		if (empty($condition)) {
			return;
		}
		if (!is_array($condition)) {
			throw new NotSupportedException('String conditions are not supported by elasticsearch.');
		}
		if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
			$operator = strtoupper($condition[0]);
			if (isset($builders[$operator])) {
				$method = $builders[$operator];
				array_shift($condition);
				$this->$method($query, $operator, $condition);
			} else {
				throw new Exception('Found unknown operator in query: ' . $operator);
			}
		} else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
			$this->buildHashCondition($query, $condition);
		}
	}

	private function buildHashCondition(&$query, $condition)
	{
		$query['query']['term'] = $condition;
		return; // TODO more
		$parts = array();
		foreach ($condition as $column => $value) {
			if (is_array($value)) { // IN condition
				$parts[] = $this->buildInCondition('IN', array($column, $value), $params);
			} else {
				if ($value === null) {
					$parts[] = "$column IS NULL"; // TODO null
				} elseif ($value instanceof Expression) {
					$parts[] = "$column=" . $value->expression;
					foreach ($value->params as $n => $v) {
						$params[$n] = $v;
					}
				} else {
					$phName = self::PARAM_PREFIX . count($params);
					$parts[] = "$column=$phName";
					$params[$phName] = $value;
				}
			}
		}
		return count($parts) === 1 ? $parts[0] : '(' . implode(') AND (', $parts) . ')';
	}

	private function buildAndCondition($operator, $operands, &$params)
	{
		$parts = array();
		foreach ($operands as $operand) {
			if (is_array($operand)) {
				$operand = $this->buildCondition($operand, $params);
			}
			if ($operand !== '') {
				$parts[] = $operand;
			}
		}
		if (!empty($parts)) {
			return '(' . implode(") $operator (", $parts) . ')';
		} else {
			return '';
		}
	}

	private function buildBetweenCondition($operator, $operands, &$params)
	{
		if (!isset($operands[0], $operands[1], $operands[2])) {
			throw new Exception("Operator '$operator' requires three operands.");
		}

		list($column, $value1, $value2) = $operands;

		if (strpos($column, '(') === false) {
			$column = $this->db->quoteColumnName($column);
		}
		$phName1 = self::PARAM_PREFIX . count($params);
		$params[$phName1] = $value1;
		$phName2 = self::PARAM_PREFIX . count($params);
		$params[$phName2] = $value2;

		return "$column $operator $phName1 AND $phName2";
	}

	private function buildInCondition($operator, $operands, &$params)
	{
		if (!isset($operands[0], $operands[1])) {
			throw new Exception("Operator '$operator' requires two operands.");
		}

		list($column, $values) = $operands;

		$values = (array)$values;

		if (empty($values) || $column === array()) {
			return $operator === 'IN' ? '0=1' : '';
		}

		if (count($column) > 1) {
			return $this->buildCompositeInCondition($operator, $column, $values, $params);
		} elseif (is_array($column)) {
			$column = reset($column);
		}
		foreach ($values as $i => $value) {
			if (is_array($value)) {
				$value = isset($value[$column]) ? $value[$column] : null;
			}
			if ($value === null) {
				$values[$i] = 'NULL';
			} elseif ($value instanceof Expression) {
				$values[$i] = $value->expression;
				foreach ($value->params as $n => $v) {
					$params[$n] = $v;
				}
			} else {
				$phName = self::PARAM_PREFIX . count($params);
				$params[$phName] = $value;
				$values[$i] = $phName;
			}
		}
		if (strpos($column, '(') === false) {
			$column = $this->db->quoteColumnName($column);
		}

		if (count($values) > 1) {
			return "$column $operator (" . implode(', ', $values) . ')';
		} else {
			$operator = $operator === 'IN' ? '=' : '<>';
			return "$column$operator{$values[0]}";
		}
	}

	protected function buildCompositeInCondition($operator, $columns, $values, &$params)
	{
		$vss = array();
		foreach ($values as $value) {
			$vs = array();
			foreach ($columns as $column) {
				if (isset($value[$column])) {
					$phName = self::PARAM_PREFIX . count($params);
					$params[$phName] = $value[$column];
					$vs[] = $phName;
				} else {
					$vs[] = 'NULL';
				}
			}
			$vss[] = '(' . implode(', ', $vs) . ')';
		}
		foreach ($columns as $i => $column) {
			if (strpos($column, '(') === false) {
				$columns[$i] = $this->db->quoteColumnName($column);
			}
		}
		return '(' . implode(', ', $columns) . ") $operator (" . implode(', ', $vss) . ')';
	}

	private function buildLikeCondition($operator, $operands, &$params)
	{
		if (!isset($operands[0], $operands[1])) {
			throw new Exception("Operator '$operator' requires two operands.");
		}

		list($column, $values) = $operands;

		$values = (array)$values;

		if (empty($values)) {
			return $operator === 'LIKE' || $operator === 'OR LIKE' ? '0=1' : '';
		}

		if ($operator === 'LIKE' || $operator === 'NOT LIKE') {
			$andor = ' AND ';
		} else {
			$andor = ' OR ';
			$operator = $operator === 'OR LIKE' ? 'LIKE' : 'NOT LIKE';
		}

		if (strpos($column, '(') === false) {
			$column = $this->db->quoteColumnName($column);
		}

		$parts = array();
		foreach ($values as $value) {
			$phName = self::PARAM_PREFIX . count($params);
			$params[$phName] = $value;
			$parts[] = "$column $operator $phName";
		}

		return implode($andor, $parts);
	}
}

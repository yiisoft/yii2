<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\InvalidParamException;
use yii\base\NotSupportedException;

/**
 * QueryBuilder builds an elasticsearch query based on the specification given as a [[Query]] object.
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
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
	public function __construct($connection, $config = [])
	{
		$this->db = $connection;
		parent::__construct($config);
	}

	/**
	 * Generates query from a [[Query]] object.
	 * @param Query $query the [[Query]] object from which the query will be generated
	 * @return array the generated SQL statement (the first array element) and the corresponding
	 * parameters to be bound to the SQL statement (the second array element).
	 */
	public function build($query)
	{
		$parts = [];

		if ($query->fields !== null) {
			$parts['fields'] = (array) $query->fields;
		}
		if ($query->limit !== null && $query->limit >= 0) {
			$parts['size'] = $query->limit;
		}
		if ($query->offset > 0) {
			$parts['from'] = (int) $query->offset;
		}

		$this->buildCondition($parts, $query->where);
		$this->buildOrderBy($parts, $query->orderBy);

		if (empty($parts['query'])) {
			$parts['query'] = ["match_all" => (object)[]];
		}

		return [
			'queryParts' => $parts,
			'index' => $query->index,
			'type' => $query->type,
			'options' => [
				'timeout' => $query->timeout
			],
		];
	}

	/**
	 * adds order by condition to the query
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
				$orders[] = array($name => ($direction === SORT_DESC ? 'desc' : 'asc'));
			}
		}
		$query['sort'] = $orders;
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
			throw new NotSupportedException('String conditions in where() are not supported by elasticsearch.');
		}
		if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
			$operator = strtoupper($condition[0]);
			if (isset($builders[$operator])) {
				$method = $builders[$operator];
				array_shift($condition);
				$this->$method($query, $operator, $condition);
			} else {
				throw new InvalidParamException('Found unknown operator in query: ' . $operator);
			}
		} else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
			$this->buildHashCondition($query, $condition);
		}
	}

	private function buildHashCondition(&$query, $condition)
	{
		foreach($condition as $attribute => $value) {
			// ['query']['filteredQuery']
			$query['filter']['bool']['must'][] = array(
				'term' => array($attribute => $value),
			);
		}
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

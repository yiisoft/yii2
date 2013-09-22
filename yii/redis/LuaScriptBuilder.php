<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use yii\base\NotSupportedException;

/**
 * LuaScriptBuilder builds lua scripts used for retrieving data from redis.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class LuaScriptBuilder extends \yii\base\Object
{
	public function buildAll($query)
	{
		// TODO add support for orderBy
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=n+1 pks[n]=redis.call('HGETALL','$key:a:' .. pk)", 'pks'); // TODO quote
	}

	public function buildOne($query)
	{
		// TODO add support for orderBy
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "do return redis.call('HGETALL','$key:a:' .. pk) end", 'pks'); // TODO quote
	}

	public function buildColumn($query, $field)
	{
		// TODO add support for orderBy and indexBy
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=n+1 pks[n]=redis.call('HGET','$key:a:' .. pk,'$field')", 'pks'); // TODO quote
	}

	public function buildCount($query)
	{
		return $this->build($query, 'n=n+1', 'n');
	}

	public function buildSum($query, $field)
	{
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=n+redis.call('HGET','$key:a:' .. pk,'$field')", 'n'); // TODO quote
	}

	public function buildAverage($query, $field)
	{
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=n+1 if v==nil then v=0 end v=v+redis.call('HGET','$key:a:' .. pk,'$field')", 'v/n'); // TODO quote
	}

	public function buildMin($query, $field)
	{
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=redis.call('HGET','$key:a:' .. pk,'$field') if v==nil or n<v then v=n end", 'v'); // TODO quote
	}

	public function buildMax($query, $field)
	{
		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		return $this->build($query, "n=redis.call('HGET','$key:a:' .. pk,'$field') if v==nil or n>v then v=n end", 'v'); // TODO quote
	}

	/**
	 * @param ActiveQuery $query
	 */
	public function build($query, $buildResult, $return)
	{
		$columns = array();
		if ($query->where !== null) {
			$condition = $this->buildCondition($query->where, $columns);
		} else {
			$condition = 'true';
		}

		$start = $query->offset === null ? 0 : $query->offset;
		$limitCondition = 'i>' . $start . ($query->limit === null ? '' : ' and i<=' . ($start + $query->limit));

		$modelClass = $query->modelClass;
		$key = $modelClass::tableName();
		$loadColumnValues = '';
		foreach($columns as $column) {
			$loadColumnValues .= "local $column=redis.call('HGET','$key:a:' .. pk, '$column')\n"; // TODO properly hash pk
		}

		return <<<EOF
local allpks=redis.call('LRANGE','$key',0,-1)
local pks={}
local n=0
local v=nil
local i=0
for k,pk in ipairs(allpks) do
    $loadColumnValues
    if $condition then
      i=i+1
      if $limitCondition then
        $buildResult
      end
    end
end
return $return
EOF;
	}

	/**
	 * Quotes a string value for use in a query.
	 * Note that if the parameter is not a string, it will be returned without change.
	 * @param string $str string to be quoted
	 * @return string the properly quoted string
	 * @see http://www.php.net/manual/en/function.PDO-quote.php
	 */
	public function quoteValue($str)
	{
		if (!is_string($str) && !is_int($str)) {
			return $str;
		}

		return "'" . addcslashes(str_replace("'", "\\'", $str), "\000\n\r\\\032") . "'";
	}

	/**
	 * Parses the condition specification and generates the corresponding SQL expression.
	 * @param string|array $condition the condition specification. Please refer to [[Query::where()]]
	 * on how to specify a condition.
	 * @param array $params the binding parameters to be populated
	 * @return string the generated SQL expression
	 * @throws \yii\db\Exception if the condition is in bad format
	 */
	public function buildCondition($condition, &$columns)
	{
		static $builders = array(
			'and' => 'buildAndCondition',
			'or' => 'buildAndCondition',
			'between' => 'buildBetweenCondition',
			'not between' => 'buildBetweenCondition',
			'in' => 'buildInCondition',
			'not in' => 'buildInCondition',
			'like' => 'buildLikeCondition',
			'not like' => 'buildLikeCondition',
			'or like' => 'buildLikeCondition',
			'or not like' => 'buildLikeCondition',
		);

		if (!is_array($condition)) {
			throw new NotSupportedException('Where must be an array.');
		}
		if (isset($condition[0])) { // operator format: operator, operand 1, operand 2, ...
			$operator = strtolower($condition[0]);
			if (isset($builders[$operator])) {
				$method = $builders[$operator];
				array_shift($condition);
				return $this->$method($operator, $condition, $columns);
			} else {
				throw new Exception('Found unknown operator in query: ' . $operator);
			}
		} else { // hash format: 'column1' => 'value1', 'column2' => 'value2', ...
			return $this->buildHashCondition($condition, $columns);
		}
	}

	private function buildHashCondition($condition, &$columns)
	{
		$parts = array();
		foreach ($condition as $column => $value) {
			// TODO replace special chars and keywords in column name
			$columns[$column] = $column;
			if (is_array($value)) { // IN condition
				$parts[] = $this->buildInCondition('IN', array($column, $value), $columns);
			} else {
				if ($value === null) {
					$parts[] = $column.'==nil';
				} elseif ($value instanceof Expression) {
					$parts[] = "$column==" . $value->expression;
				} else {
					$value = $this->quoteValue($value);
					$parts[] = "$column==$value";
				}
			}
		}
		return count($parts) === 1 ? $parts[0] : '(' . implode(') and (', $parts) . ')';
	}

	private function buildAndCondition($operator, $operands, &$columns)
	{
		$parts = array();
		foreach ($operands as $operand) {
			if (is_array($operand)) {
				$operand = $this->buildCondition($operand, $columns);
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

	private function buildBetweenCondition($operator, $operands, &$columns)
	{
		if (!isset($operands[0], $operands[1], $operands[2])) {
			throw new Exception("Operator '$operator' requires three operands.");
		}

		list($column, $value1, $value2) = $operands;

		// TODO replace special chars and keywords in column name
		$value1 = $this->quoteValue($value1);
		$value2 = $this->quoteValue($value2);
		$columns[$column] = $column;
		return "$column > $value1 and $column < $value2";
	}

	private function buildInCondition($operator, $operands, &$columns)
	{
		// TODO adjust implementation to respect NOT IN operator
		if (!isset($operands[0], $operands[1])) {
			throw new Exception("Operator '$operator' requires two operands.");
		}

		list($column, $values) = $operands;

		$values = (array)$values;

		if (empty($values) || $column === array()) {
			return $operator === 'IN' ? '0==1' : '';
		}

		if (count($column) > 1) {
			return $this->buildCompositeInCondition($operator, $column, $values, $columns);
		} elseif (is_array($column)) {
			$column = reset($column);
		}
		$parts = array();
		foreach ($values as $i => $value) {
			if (is_array($value)) {
				$value = isset($value[$column]) ? $value[$column] : null;
			}
			// TODO replace special chars and keywords in column name
			if ($value === null) {
				$parts[] = 'type('.$column.')=="nil"';
			} elseif ($value instanceof Expression) {
				$parts[] = "$column==" . $value->expression;
			} else {
				$value = $this->quoteValue($value);
				$parts[] = "$column==$value";
			}
		}
		if (count($parts) > 1) {
			return "(" . implode(' or ', $parts) . ')';
		} else {
			$operator = $operator === 'IN' ? '' : '!';
			return "$operator({$values[0]})";
		}
	}

	protected function buildCompositeInCondition($operator, $columns, $values, &$params)
	{
		throw new NotSupportedException('composie IN is not yet supported.');
		// TODO implement correclty
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
		throw new NotSupportedException('LIKE is not yet supported.');
		// TODO implement correclty
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

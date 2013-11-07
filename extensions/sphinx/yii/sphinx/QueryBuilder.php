<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\sphinx;

/**
 * Class QueryBuilder
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
class QueryBuilder extends \yii\db\mysql\QueryBuilder
{
	/**
	 * Generates a SELECT SQL statement from a [[Query]] object.
	 * @param Query $query the [[Query]] object from which the SQL statement will be generated
	 * @return array the generated SQL statement (the first array element) and the corresponding
	 * parameters to be bound to the SQL statement (the second array element).
	 */
	public function build($query)
	{
		$params = $query->params;
		$clauses = [
			$this->buildSelect($query->select, $query->distinct, $query->selectOption),
			$this->buildFrom($query->from),
			$this->buildWhere($query->where, $params),
			$this->buildGroupBy($query->groupBy),
			$this->buildWithin($query->within),
			$this->buildOrderBy($query->orderBy),
			$this->buildLimit($query->limit, $query->offset),
			$this->buildOption($query->options),
		];
		return [implode($this->separator, array_filter($clauses)), $params];
	}

	/**
	 * @param array $columns
	 * @return string the ORDER BY clause built from [[query]].
	 */
	public function buildWithin($columns)
	{
		if (empty($columns)) {
			return '';
		}
		$orders = [];
		foreach ($columns as $name => $direction) {
			if (is_object($direction)) {
				$orders[] = (string)$direction;
			} else {
				$orders[] = $this->db->quoteColumnName($name) . ($direction === Query::SORT_DESC ? ' DESC' : '');
			}
		}
		return 'WITHIN GROUP ORDER BY ' . implode(', ', $orders);
	}

	/**
	 * @param array $options
	 * @return string the OPTION clause build from [[query]]
	 */
	public function buildOption(array $options)
	{
		if (empty($options)) {
			return '';
		}
		$optionLines = [];
		foreach ($options as $name => $value) {
			$optionLines[] = $name . ' = ' . $value;
		}
		return 'OPTION ' . implode(', ', $optionLines);
	}
}
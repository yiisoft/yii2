<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

// TODO where to put these constants?
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
 * The BaseQuery trait represents the minimum method set of a database Query.
 *
 * It has support for getting [[one]] instance or [[all]].
 * Allows pagination via [[limit]] and [[offset]].
 * Sorting is supported via [[orderBy]] and items can be limited to match some conditions unsing [[where]].
 *
 * By calling [[createCommand()]], we can get a [[Command]] instance which can be further
 * used to perform/execute the DB query against a database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
trait QueryTrait
{
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
	 * @var array how to sort the query results. This is used to construct the ORDER BY clause in a SQL statement.
	 * The array keys are the columns to be sorted by, and the array values are the corresponding sort directions which
	 * can be either [[Query::SORT_ASC]] or [[Query::SORT_DESC]]. The array may also contain [[Expression]] objects.
	 * If that is the case, the expressions will be converted into strings without any change.
	 */
	public $orderBy;
	/**
	 * @var string|callable $column the name of the column by which the query results should be indexed by.
	 * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
	 * row data. For more details, see [[indexBy()]]. This property is only used by [[all()]].
	 */
	public $indexBy;

	/**
	 * Sets the [[indexBy]] property.
	 * @param string|callable $column the name of the column by which the query results should be indexed by.
	 * This can also be a callable (e.g. anonymous function) that returns the index value based on the given
	 * row data. The signature of the callable should be:
	 *
	 * ~~~
	 * function ($row)
	 * {
	 *     // return the index value corresponding to $row
	 * }
	 * ~~~
	 *
	 * @return static the query object itself
	 */
	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
	}

	/**
	 * Executes the query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	abstract public function all();

	/**
	 * Executes the query and returns a single row of result.
	 * @return array|boolean the first row (in terms of an array) of the query result. False is returned if the query
	 * results in nothing.
	 */
	abstract public function one();

	/**
	 * Returns the number of records.
	 * @return integer number of records
	 */
	abstract public function count();

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	abstract public function exists();

	/**
	 * Sets the WHERE part of the query.
	 *
	 * The method requires a $condition parameter.
	 *
	 * The $condition parameter should be an array in one of the following two formats:
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
	 * For example, `['like', 'name', '%tester%']` will generate `name LIKE '%tester%'`.
	 * When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
	 * using `AND`. For example, `['like', 'name', ['%test%', '%sample%']]` will generate
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
	 * @param array $condition the conditions that should be put in the WHERE part.
	 * @return static the query object itself
	 * @see andWhere()
	 * @see orWhere()
	 */
	public function where($condition)
	{
		$this->where = $condition;
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'AND' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @return static the query object itself
	 * @see where()
	 * @see orWhere()
	 */
	public function andWhere($condition)
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = ['and', $this->where, $condition];
		}
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @return static the query object itself
	 * @see where()
	 * @see andWhere()
	 */
	public function orWhere($condition)
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = ['or', $this->where, $condition];
		}
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see addOrderBy()
	 */
	public function orderBy($columns)
	{
		$this->orderBy = $this->normalizeOrderBy($columns);
		return $this;
	}

	/**
	 * Adds additional ORDER BY columns to the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `['id' => Query::SORT_ASC, 'name' => Query::SORT_DESC]`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return static the query object itself
	 * @see orderBy()
	 */
	public function addOrderBy($columns)
	{
		$columns = $this->normalizeOrderBy($columns);
		if ($this->orderBy === null) {
			$this->orderBy = $columns;
		} else {
			$this->orderBy = array_merge($this->orderBy, $columns);
		}
		return $this;
	}

	protected function normalizeOrderBy($columns)
	{
		if (is_array($columns)) {
			return $columns;
		} else {
			$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			$result = [];
			foreach ($columns as $column) {
				if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
					$result[$matches[1]] = strcasecmp($matches[2], 'desc') ? self::SORT_ASC : self::SORT_DESC;
				} else {
					$result[$column] = self::SORT_ASC;
				}
			}
			return $result;
		}
	}

	/**
	 * Sets the LIMIT part of the query.
	 * @param integer $limit the limit. Use null or negative value to disable limit.
	 * @return static the query object itself
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset. Use null or negative value to disable offset.
	 * @return static the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}
}
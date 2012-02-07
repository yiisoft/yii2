<?php
/**
 * ActiveQuery class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\base\VectorIterator;
use yii\db\dao\Query;
use yii\db\Exception;

/**
 * ActiveFinder.php is ...
 * todo: add SQL monitor
 *
 * todo: add ActiveQueryBuilder
 * todo: quote join/on part of the relational query
 * todo: modify QueryBuilder about join() methods
 * todo: unify ActiveQuery and ActiveRelation in query building process
 * todo: intelligent table aliasing (first table name, then relation name, finally t?)
 * todo: allow using tokens in primary query fragments
 * todo: findBySql
 * todo: base limited
 * todo: lazy loading
 * todo: scope
 * todo: test via option
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends \yii\base\Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var \yii\db\dao\Query the Query object
	 */
	public $query;
	/**
	 * @var array list of relations that this query should be performed with
	 */
	public $with;
	/**
	 * @var string the table alias to be used for query
	 */
	public $tableAlias;
	/**
	 * @var string the name of the column that the result should be indexed by
	 */
	public $indexBy;
	/**
	 * @var boolean whether to return query results as arrays
	 */
	public $asArray;
	/**
	 * @var array list of scopes that should be applied to this query
	 */
	public $scopes;
	/**
	 * @var array list of query results
	 */
	public $records;
	public $sql;

	/**
	 * @param string $modelClass the name of the ActiveRecord class.
	 */
	public function __construct($modelClass)
	{
		$this->modelClass = $modelClass;
		$this->query = new Query;
	}

	public function all()
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return $this->records;
	}

	public function one($limitOne = true)
	{
		if ($this->records === null) {
			if ($limitOne) {
				$this->limit(1);
			}
			$this->records = $this->findRecords();
		}
		return isset($this->records[0]) ? $this->records[0] : null;
	}

	public function exists()
	{
		// todo
		return $this->select(array('1'))->asArray(true)->one() !== null;
	}

	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	public function with()
	{
		$this->with = func_get_args();
		if (isset($this->with[0]) && is_array($this->with[0])) {
			// the parameter is given as an array
			$this->with = $this->with[0];
		}
		return $this;
	}

	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
	}

	public function tableAlias($value)
	{
		$this->tableAlias = $value;
		return $this;
	}

	/**
	 * Returns the database connection used by this query.
	 * This method returns the connection used by the [[modelClass]].
	 * @return \yii\db\dao\Connection the database connection used by this query
	 */
	public function getDbConnection()
	{
		$class = $this->modelClass;
		return $class::getDbConnection();
	}

	/**
	 * Returns the number of items in the vector.
	 * @return integer the number of items in the vector
	 */
	public function getCount()
	{
		return $this->count();
	}

	/**
	 * Sets the parameters about query caching.
	 * This is a shortcut method to {@link CDbConnection::cache()}.
	 * It changes the query caching parameter of the {@link dbConnection} instance.
	 * @param integer $duration the number of seconds that query results may remain valid in cache.
	 * If this is 0, the caching will be disabled.
	 * @param CCacheDependency $dependency the dependency that will be used when saving the query results into cache.
	 * @param integer $queryCount number of SQL queries that need to be cached after calling this method. Defaults to 1,
	 * meaning that the next SQL query will be cached.
	 * @return ActiveRecord the active record instance itself.
	 */
	public function cache($duration, $dependency = null, $queryCount = 1)
	{
		$this->getDbConnection()->cache($duration, $dependency, $queryCount);
		return $this;
	}

	/**
	 * Returns an iterator for traversing the items in the vector.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the vector.
	 * @return VectorIterator an iterator for traversing the items in the vector.
	 */
	public function getIterator()
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return new VectorIterator($this->records);
	}

	/**
	 * Returns the number of items in the vector.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($vector)`.
	 * @param boolean $bySql whether to get the count by performing a SQL COUNT query.
	 * If this is false, it will count the number of records brought back by this query.
	 * @return integer number of items in the vector.
	 */
	public function count($bySql = false)
	{
		if ($bySql) {
			return $this->performCountQuery();
		} else {
			if ($this->records === null) {
				$this->records = $this->findRecords();
			}
			return count($this->records);
		}
	}

	/**
	 * Returns a value indicating whether there is an item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($vector[$offset])`.
	 * @param integer $offset the offset to be checked
	 * @return boolean whether there is an item at the specified offset.
	 */
	public function offsetExists($offset)
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return isset($this->records[$offset]);
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $vector[$offset];`.
	 * This is equivalent to [[itemAt]].
	 * @param integer $offset the offset to retrieve item.
	 * @return ActiveRecord the item at the offset
	 * @throws Exception if the offset is out of range
	 */
	public function offsetGet($offset)
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return isset($this->records[$offset]) ? $this->records[$offset] : null;
	}

	/**
	 * Sets the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$vector[$offset] = $item;`.
	 * If the offset is null or equal to the number of the existing items,
	 * the new item will be appended to the vector.
	 * Otherwise, the existing item at the offset will be replaced with the new item.
	 * @param integer $offset the offset to set item
	 * @param ActiveRecord $item the item value
	 * @throws Exception if the offset is out of range, or the vector is read only.
	 */
	public function offsetSet($offset, $item)
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		$this->records[$offset] = $item;
	}

	/**
	 * Unsets the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($vector[$offset])`.
	 * This is equivalent to [[removeAt]].
	 * @param integer $offset the offset to unset item
	 * @throws Exception if the offset is out of range, or the vector is read only.
	 */
	public function offsetUnset($offset)
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		unset($this->records[$offset]);
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
	 * @return ActiveQuery the query object itself
	 */
	public function select($columns, $option = '')
	{
		$this->query->select($columns, $option);
		return $this;
	}

	/**
	 * Sets the value indicating whether to SELECT DISTINCT or not.
	 * @param bool $value whether to SELECT DISTINCT or not.
	 * @return ActiveQuery the query object itself
	 */
	public function distinct($value = true)
	{
		$this->query->distinct($value);
		return $this;
	}

	/**
	 * Sets the FROM part of the query.
	 * @param string|array $tables the table(s) to be selected from. This can be either a string (e.g. 'tbl_user')
	 * or an array (e.g. array('tbl_user', 'tbl_profile')) specifying one or several table names.
	 * Table names can contain schema prefixes (e.g. 'public.tbl_user') and/or table aliases (e.g. 'tbl_user u').
	 * The method will automatically quote the table names unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return ActiveQuery the query object itself
	 */
	public function from($tables)
	{
		$this->query->from($tables);
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
	 * For anonymous parameters, they can alternatively be specified as separate parameters to this method.
	 * For example, `where('type=? AND status=?', 100, 1)`.
	 * @return ActiveQuery the query object itself
	 * @see andWhere()
	 * @see orWhere()
	 */
	public function where($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->where($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'AND' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see where()
	 * @see orWhere()
	 */
	public function andWhere($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->andWhere($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see where()
	 * @see andWhere()
	 */
	public function orWhere($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->orWhere($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
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
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 */
	public function join($table, $condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->join($table, $condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
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
	 * @return ActiveQuery the query object itself
	 */
	public function leftJoin($table, $condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->leftJoin($table, $condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
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
	 * @return ActiveQuery the query object itself
	 */
	public function rightJoin($table, $condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->rightJoin($table, $condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Appends a CROSS JOIN part to the query.
	 * Note that not all DBMS support CROSS JOIN.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return ActiveQuery the query object itself
	 */
	public function crossJoin($table)
	{
		$this->query->crossJoin($table);
		return $this;
	}

	/**
	 * Appends a NATURAL JOIN part to the query.
	 * Note that not all DBMS support NATURAL JOIN.
	 * @param string $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @return ActiveQuery the query object itself
	 */
	public function naturalJoin($table)
	{
		$this->query->naturalJoin($table);
		return $this;
	}

	/**
	 * Sets the GROUP BY part of the query.
	 * @param string|array $columns the columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return ActiveQuery the query object itself
	 * @see addGroupBy()
	 */
	public function groupBy($columns)
	{
		$this->query->groupBy($columns);
		return $this;
	}

	/**
	 * Adds additional group-by columns to the existing ones.
	 * @param string|array $columns additional columns to be grouped by.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return ActiveQuery the query object itself
	 * @see groupBy()
	 */
	public function addGroupBy($columns)
	{
		$this->query->addGroupBy($columns);
		return $this;
	}

	/**
	 * Sets the HAVING part of the query.
	 * @param string|array $condition the conditions to be put after HAVING.
	 * Please refer to [[where()]] on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see andHaving()
	 * @see orHaving()
	 */
	public function having($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->having($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Adds an additional HAVING condition to the existing one.
	 * The new condition and the existing one will be joined using the 'AND' operator.
	 * @param string|array $condition the new HAVING condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see having()
	 * @see orHaving()
	 */
	public function andHaving($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->andHaving($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Adds an additional HAVING condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new HAVING condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @param array $params the parameters (name=>value) to be bound to the query.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see having()
	 * @see andHaving()
	 */
	public function orHaving($condition, $params = array())
	{
		if (is_array($params)) {
			$this->query->orHaving($condition, $params);
		} else {
			call_user_func_array(array($this->query, __FUNCTION__), func_get_args());
		}
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return ActiveQuery the query object itself
	 * @see addOrderBy()
	 */
	public function orderBy($columns)
	{
		$this->query->orderBy($columns);
		return $this;
	}

	/**
	 * Adds additional ORDER BY columns to the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return ActiveQuery the query object itself
	 * @see orderBy()
	 */
	public function addOrderBy($columns)
	{
		$this->query->addOrderBy($columns);
		return $this;
	}

	/**
	 * Sets the LIMIT part of the query.
	 * @param integer $limit the limit
	 * @return ActiveQuery the query object itself
	 */
	public function limit($limit)
	{
		$this->query->limit($limit);
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset
	 * @return ActiveQuery the query object itself
	 */
	public function offset($offset)
	{
		$this->query->offset($offset);
		return $this;
	}

	/**
	 * Appends a SQL statement using UNION operator.
	 * @param string|Query $sql the SQL statement to be appended using UNION
	 * @return ActiveQuery the query object itself
	 */
	public function union($sql)
	{
		$this->query->union($sql);
		return $this;
	}

	public function getParams()
	{
		return $this->query->params;
	}

	/**
	 * Sets the parameters to be bound to the query.
	 * @param array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see addParams()
	 */
	public function params($params)
	{
		$this->query->params($params);
		return $this;
	}

	/**
	 * Adds additional parameters to be bound to the query.
	 * @param array list of query parameter values indexed by parameter placeholders.
	 * For example, `array(':name'=>'Dan', ':age'=>31)`.
	 * Please refer to [[where()]] on alternative syntax of specifying anonymous parameters.
	 * @return ActiveQuery the query object itself
	 * @see params()
	 */
	public function addParams($params)
	{
		$this->query->addParams($params);
		return $this;
	}

	public function joinWith()
	{
		// todo: inner join with one or multiple relations as filters
	}

	protected function findRecords()
	{
		if (!empty($this->with)) {
			// todo: handle findBySql() and limit cases
			$joinTree = $this->buildRelationalQuery();
		}

		if ($this->sql === null) {
			$this->initFrom($this->query);
			$command = $this->query->createCommand($this->getDbConnection());
			$this->sql = $command->getSql();
		} else {
			$command = $this->getDbConnection()->createCommand($this->sql);
			$command->bindValues($this->query->params);
		}
echo $command->sql;
		$rows = $command->queryAll();

		if (!empty($this->with)) {
			foreach ($rows as $row) {
				$joinTree->populateData($row);
			}
			return array_values($joinTree->records);
		}

		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			$records = array();
			foreach ($rows as $row) {
				$records[$row[$this->indexBy]] = $row;
			}
			return $records;
		} else {
			$records = array();
			$class = $this->modelClass;
			if ($this->indexBy === null) {
				foreach ($rows as $row) {
					$records[] = $class::populateData($row);
				}
			} else {
				$attribute = $this->indexBy;
				foreach ($rows as $row) {
					$record = $class::populateData($row);
					$records[$record->$attribute] = $record;
				}
			}
			return $records;
		}
	}

	protected function performCountQuery()
	{
		if ($this->sql === null) {
			$this->initFrom($this->query);
			$this->query->select = 'COUNT(*)';
			$command = $this->query->createCommand($this->getDbConnection());
			$this->sql = $command->getSql();
		} else {
			$command = $this->getDbConnection()->createCommand($this->sql);
			$command->bindValues($this->query->params);
		}
		return $command->queryScalar();
	}

	protected function initFrom($query)
	{
		if ($query->from === null) {
			$modelClass = $this->modelClass;
			$tableName = $modelClass::tableName();
			if ($this->tableAlias !== null) {
				$tableName .= ' ' . $this->tableAlias;
			}
			$query->from = array($tableName);
		}
	}

	protected function buildRelationalQuery()
	{
		$joinTree = new JoinElement($this, null, null);
		$this->buildJoinTree($joinTree, $this->with);
		$this->buildTableAlias($joinTree);
		$query = new Query;
		foreach ($joinTree->children as $child) {
			$child->buildQuery($query);
		}

		$select = $joinTree->buildSelect($this->query->select);
		if (!empty($query->select)) {
			$this->query->select = array_merge($select, $query->select);
		} else {
			$this->query->select = $select;
		}
		if (!empty($query->where)) {
			$this->query->andWhere('(' . implode(') AND (', $query->where) . ')');
		}
		if (!empty($query->having)) {
			$this->query->andHaving('(' . implode(') AND (', $query->having) . ')');
		}
		if (!empty($query->join)) {
			if ($this->query->join === null) {
				$this->query->join = $query->join;
			} else {
				$this->query->join = array_merge($this->query->join, $query->join);
			}
		}
		if (!empty($query->orderBy)) {
			$this->query->addOrderBy($query->orderBy);
		}
		if (!empty($query->groupBy)) {
			$this->query->addGroupBy($query->groupBy);
		}
		if (!empty($query->params)) {
			$this->query->addParams($query->params);
		}

		return $joinTree;
	}

	/**
	 * @param JoinElement $parent
	 * @param array|string $with
	 * @param array $config
	 * @return null|JoinElement
	 * @throws \yii\db\Exception
	 */
	protected function buildJoinTree($parent, $with, $config = array())
	{
		if (is_array($with)) {
			foreach ($with as $name => $value) {
				if (is_string($value)) {
					$this->buildJoinTree($parent, $value);
				} elseif (is_string($name) && is_array($value)) {
					$this->buildJoinTree($parent, $name, $value);
				}
			}
			return null;
		}

		if (($pos = strrpos($with, '.')) !== false) {
			$parent = $this->buildJoinTree($parent, substr($with, 0, $pos));
			$with = substr($with, $pos + 1);
		}

		if (isset($parent->children[$with])) {
			$child = $parent->children[$with];
			$child->joinOnly = false;
		} else {
			$modelClass = $parent->relation->modelClass;
			$relations = $modelClass::getMetaData()->relations;
			if (!isset($relations[$with])) {
				throw new Exception("$modelClass has no relation named '$with'.");
			}
			$relation = clone $relations[$with];
			if ($relation->via !== null && isset($relations[$relation->via])) {
				$relation->via = null;
				$parent2 = $this->buildJoinTree($parent, $relation->via);
				if ($parent2->joinOnly === null) {
					$parent2->joinOnly = true;
				}
				$child = new JoinElement($relation, $parent2, $parent);
			} else {
				$child = new JoinElement($relation, $parent, $parent);
			}
		}

		foreach ($config as $name => $value) {
			$child->relation->$name = $value;
		}

		return $child;
	}

	protected function buildTableAlias($element, &$count = 0)
	{
		if ($element->relation->tableAlias === null) {
			$element->relation->tableAlias = 't' . ($count++);
		}
		foreach ($element->children as $child) {
			$this->buildTableAlias($child, $count);
		}
	}
}

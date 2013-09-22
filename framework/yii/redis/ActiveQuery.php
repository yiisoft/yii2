<?php
/**
 * ActiveRecord class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

/**
 * ActiveQuery represents a DB query associated with an Active Record class.
 *
 * ActiveQuery instances are usually created by [[yii\db\redis\ActiveRecord::find()]]
 * and [[yii\db\redis\ActiveRecord::count()]].
 *
 * ActiveQuery mainly provides the following methods to retrieve the query results:
 *
 * - [[one()]]: returns a single record populated with the first row of data.
 * - [[all()]]: returns all records based on the query results.
 * - [[count()]]: returns the number of records.
 * - [[sum()]]: returns the sum over the specified column.
 * - [[average()]]: returns the average over the specified column.
 * - [[min()]]: returns the min over the specified column.
 * - [[max()]]: returns the max over the specified column.
 * - [[scalar()]]: returns the value of the first column in the first row of the query result.
 * - [[exists()]]: returns a value indicating whether the query result has data or not.
 *
 * You can use query methods, such as [[limit()]], [[orderBy()]] to customize the query options.
 *
 * ActiveQuery also provides the following additional query options:
 *
 * - [[with()]]: list of relations that this query should be performed with.
 * - [[indexBy()]]: the name of the column by which the query result should be indexed.
 * - [[asArray()]]: whether to return each record as an array.
 *
 * These options can be configured using methods of the same name. For example:
 *
 * ~~~
 * $customers = Customer::find()->with('orders')->asArray()->all();
 * ~~~
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveQuery extends \yii\base\Component
{
	/**
	 * @var string the name of the ActiveRecord class.
	 */
	public $modelClass;
	/**
	 * @var array list of relations that this query should be performed with
	 */
	public $with;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
	 */
	public $asArray;
	/**
	 * @var array query condition. This refers to the WHERE clause in a SQL statement.
	 * @see where()
	 */
	public $where;
	/**
	 * @var integer maximum number of records to be returned. If not set or less than 0, it means no limit.
	 */
	public $limit;
	/**
	 * @var integer zero-based offset from where the records are to be returned.
	 * If not set, it means starting from the beginning.
	 * If less than zero it means starting n elements from the end.
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
	 * @var string the name of the column by which query results should be indexed by.
	 * This is only used when the query result is returned as an array when calling [[all()]].
	 */
	public $indexBy;

	/**
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();

		$script = $db->luaScriptBuilder->buildAll($this);
		$data = $db->executeCommand('EVAL', array($script, 0));

		$rows = array();
		foreach($data as $dataRow) {
			$row = array();
			$c = count($dataRow);
			for($i = 0; $i < $c; ) {
				$row[$dataRow[$i++]] = $dataRow[$i++];
			}
			$rows[] = $row;
		}

		if ($rows !== array()) {
			$models = $this->createModels($rows);
			if (!empty($this->with)) {
				$this->populateRelations($models, $this->with);
			}
			return $models;
		} else {
			return array();
		}
	}

	/**
	 * Executes query and returns a single row of result.
	 * @return ActiveRecord|array|null a single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one()
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();

		$script = $db->luaScriptBuilder->buildOne($this);
		$data = $db->executeCommand('EVAL', array($script, 0));

		if ($data === array()) {
			return null;
		}
		$row = array();
		$c = count($data);
		for($i = 0; $i < $c; ) {
			$row[$data[$i++]] = $data[$i++];
		}
		if (!$this->asArray) {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			$model = $class::create($row);
			if (!empty($this->with)) {
				$models = array($model);
				$this->populateRelations($models, $this->with);
				$model = $models[0];
			}
			return $model;
		} else {
			return $row;
		}
	}

	/**
	 * Executes the query and returns the first column of the result.
	 * @param string $column name of the column to select
	 * @return array the first column of the query result. An empty array is returned if the query results in nothing.
	 */
	public function column($column)
	{
		// TODO add support for indexBy and orderBy
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		$script = $db->luaScriptBuilder->buildColumn($this, $column);
		return $db->executeCommand('EVAL', array($script, 0));
	}

	/**
	 * Returns the number of records.
	 * @param string $q the COUNT expression. Defaults to '*'.
	 * Make sure you properly quote column names.
	 * @return integer number of records
	 */
	public function count()
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		if ($this->offset === null && $this->limit === null && $this->where === null) {
			return $db->executeCommand('LLEN', array($modelClass::tableName()));
		} else {
			$script = $db->luaScriptBuilder->buildCount($this);
			return $db->executeCommand('EVAL', array($script, 0));
		}
	}

	/**
	 * Returns the number of records.
	 * @param string $column the column to sum up
	 * @return integer number of records
	 */
	public function sum($column)
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		$script = $db->luaScriptBuilder->buildSum($this, $column);
		return $db->executeCommand('EVAL', array($script, 0));
	}

	/**
	 * Returns the average of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the average of the specified column values.
	 */
	public function average($column)
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		$script = $db->luaScriptBuilder->buildAverage($this, $column);
		return $db->executeCommand('EVAL', array($script, 0));
	}

	/**
	 * Returns the minimum of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the minimum of the specified column values.
	 */
	public function min($column)
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		$script = $db->luaScriptBuilder->buildMin($this, $column);
		return $db->executeCommand('EVAL', array($script, 0));
	}

	/**
	 * Returns the maximum of the specified column values.
	 * @param string $column the column name or expression.
	 * Make sure you properly quote column names in the expression.
	 * @return integer the maximum of the specified column values.
	 */
	public function max($column)
	{
		$modelClass = $this->modelClass;
		/** @var Connection $db */
		$db = $modelClass::getDb();
		$script = $db->luaScriptBuilder->buildMax($this, $column);
		return $db->executeCommand('EVAL', array($script, 0));
	}

	/**
	 * Returns the query result as a scalar value.
	 * The value returned will be the first column in the first row of the query results.
	 * @param string $column name of the column to select
	 * @return string|boolean the value of the first column in the first row of the query result.
	 * False is returned if the query result is empty.
	 */
	public function scalar($column)
	{
		$record = $this->one();
		return $record->$column;
	}

	/**
	 * Returns a value indicating whether the query result contains any row of data.
	 * @return boolean whether the query result contains any row of data.
	 */
	public function exists()
	{
		return $this->one() !== null;
	}


	/**
	 * Sets the [[asArray]] property.
	 * @param boolean $value whether to return the query results in terms of arrays instead of Active Records.
	 * @return ActiveQuery the query object itself
	 */
	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	/**
	 * Sets the ORDER BY part of the query.
	 * @param string|array $columns the columns (and the directions) to be ordered by.
	 * Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array
	 * (e.g. `array('id' => Query::SORT_ASC, 'name' => Query::SORT_DESC)`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
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
	 * (e.g. `array('id' => Query::SORT_ASC, 'name' => Query::SORT_DESC)`).
	 * The method will automatically quote the column names unless a column contains some parenthesis
	 * (which means the column contains a DB expression).
	 * @return Query the query object itself
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
			$result = array();
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
	 * @param integer $limit the limit
	 * @return ActiveQuery the query object itself
	 */
	public function limit($limit)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset
	 * @return ActiveQuery the query object itself
	 */
	public function offset($offset)
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Specifies the relations with which this query should be performed.
	 *
	 * The parameters to this method can be either one or multiple strings, or a single array
	 * of relation names and the optional callbacks to customize the relations.
	 *
	 * The followings are some usage examples:
	 *
	 * ~~~
	 * // find customers together with their orders and country
	 * Customer::find()->with('orders', 'country')->all();
	 * // find customers together with their country and orders of status 1
	 * Customer::find()->with(array(
	 *     'orders' => function($query) {
	 *         $query->andWhere('status = 1');
	 *     },
	 *     'country',
	 * ))->all();
	 * ~~~
	 *
	 * @return ActiveQuery the query object itself
	 */
	public function with()
	{
		$this->with = func_get_args();
		if (isset($this->with[0]) && is_array($this->with[0])) {
			// the parameter is given as an array
			$this->with = $this->with[0];
		}
		return $this;
	}

	/**
	 * Sets the [[indexBy]] property.
	 * @param string $column the name of the column by which the query results should be indexed by.
	 * @return ActiveQuery the query object itself
	 */
	public function indexBy($column)
	{
		$this->indexBy = $column;
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
	 * - `array('type' => 1, 'status' => 2)` generates `(type = 1) AND (status = 2)`.
	 * - `array('id' => array(1, 2, 3), 'status' => 2)` generates `(id IN (1, 2, 3)) AND (status = 2)`.
	 * - `array('status' => null) generates `status IS NULL`.
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
	 * `array('in', 'id', array(1, 2, 3))` will generate `id IN (1, 2, 3)`.
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
	 * @return ActiveQuery the query object itself
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
	 * @return ActiveQuery the query object itself
	 * @see where()
	 * @see orWhere()
	 */
	public function andWhere($condition)
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = array('and', $this->where, $condition);
		}
		return $this;
	}

	/**
	 * Adds an additional WHERE condition to the existing one.
	 * The new condition and the existing one will be joined using the 'OR' operator.
	 * @param string|array $condition the new WHERE condition. Please refer to [[where()]]
	 * on how to specify this parameter.
	 * @return ActiveQuery the query object itself
	 * @see where()
	 * @see andWhere()
	 */
	public function orWhere($condition)
	{
		if ($this->where === null) {
			$this->where = $condition;
		} else {
			$this->where = array('or', $this->where, $condition);
		}
		return $this;
	}

	// TODO: refactor, it is duplicated from yii/db/ActiveQuery
	private function createModels($rows)
	{
		$models = array();
		if ($this->asArray) {
			if ($this->indexBy === null) {
				return $rows;
			}
			foreach ($rows as $row) {
				$models[$row[$this->indexBy]] = $row;
			}
		} else {
			/** @var $class ActiveRecord */
			$class = $this->modelClass;
			if ($this->indexBy === null) {
				foreach ($rows as $row) {
					$models[] = $class::create($row);
				}
			} else {
				foreach ($rows as $row) {
					$model = $class::create($row);
					$models[$model->{$this->indexBy}] = $model;
				}
			}
		}
		return $models;
	}

	// TODO: refactor, it is duplicated from yii/db/ActiveQuery
	private function populateRelations(&$models, $with)
	{
		$primaryModel = new $this->modelClass;
		$relations = $this->normalizeRelations($primaryModel, $with);
		foreach ($relations as $name => $relation) {
			if ($relation->asArray === null) {
				// inherit asArray from primary query
				$relation->asArray = $this->asArray;
			}
			$relation->findWith($name, $models);
		}
	}

	/**
	 * TODO: refactor, it is duplicated from yii/db/ActiveQuery
	 * @param ActiveRecord $model
	 * @param array $with
	 * @return ActiveRelation[]
	 */
	private function normalizeRelations($model, $with)
	{
		$relations = array();
		foreach ($with as $name => $callback) {
			if (is_integer($name)) {
				$name = $callback;
				$callback = null;
			}
			if (($pos = strpos($name, '.')) !== false) {
				// with sub-relations
				$childName = substr($name, $pos + 1);
				$name = substr($name, 0, $pos);
			} else {
				$childName = null;
			}

			$t = strtolower($name);
			if (!isset($relations[$t])) {
				$relation = $model->getRelation($name);
				$relation->primaryModel = null;
				$relations[$t] = $relation;
			} else {
				$relation = $relations[$t];
			}

			if (isset($childName)) {
				$relation->with[$childName] = $callback;
			} elseif ($callback !== null) {
				call_user_func($callback, $relation);
			}
		}
		return $relations;
	}
}

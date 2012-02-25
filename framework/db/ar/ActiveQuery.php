<?php
/**
 * ActiveFinder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ar;

use yii\base\VectorIterator;
use yii\db\dao\BaseQuery;
use yii\db\dao\Expression;
use yii\db\Exception;

/**
 * ActiveFinder.php is ...
 * todo: add SQL monitor
 * todo: better handling on join() support in QueryBuilder: use regexp to detect table name and quote it
 * todo: do not support anonymous parameter binding
 * todo: add ActiveFinderBuilder
 * todo: quote join/on part of the relational query
 * todo: modify QueryBuilder about join() methods
 * todo: unify ActiveFinder and ActiveRelation in query building process
 * todo: intelligent table aliasing (first table name, then relation name, finally t?)
 * todo: allow using tokens in primary query fragments
 * todo: findBySql
 * todo: base limited
 * todo: lazy loading
 * todo: scope
 * todo: test via option
 * todo: count, sum, exists
 *
 * @property integer $count
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends BaseQuery implements \IteratorAggregate, \ArrayAccess, \Countable
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
	 * @var string the table alias to be used for query
	 */
	public $tableAlias;
	/**
	 * @var string the name of the column that the result should be indexed by.
	 * This is only useful when the query result is returned as an array.
	 */
	public $indexBy;
	/**
	 * @var boolean whether to return each record as an array. If false (default), an object
	 * of [[modelClass]] will be created to represent each record.
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
	 * Executes query and returns all results as an array.
	 * @return array the query results. If the query results in nothing, an empty array will be returned.
	 */
	public function all()
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return $this->records;
	}

	/**
	 * Executes query and returns a single row of result.
	 * @return null|array|ActiveRecord the single row of query result. Depending on the setting of [[asArray]],
	 * the query result may be either an array or an ActiveRecord object. Null will be returned
	 * if the query results in nothing.
	 */
	public function one()
	{
		if ($this->records === null) {
			// todo: load only one record
			$this->records = $this->findRecords();
		}
		return isset($this->records[0]) ? $this->records[0] : null;
	}

	public function value()
	{
		$result = $this->asArray()->one();
		return $result === null ? null : reset($result);
	}

	public function exists()
	{
		return $this->select(array(new Expression('1')))->asArray()->one() !== null;
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
	 * @return integer number of items in the vector.
	 */
	public function count()
	{
		if ($this->records === null) {
			$this->records = $this->findRecords();
		}
		return count($this->records);
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

		$rows = $command->queryAll();

		if (isset($joinTree)) {
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
}

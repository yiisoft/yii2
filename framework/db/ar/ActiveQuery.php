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

use yii\db\dao\BaseQuery;
use yii\base\VectorIterator;

/**
 * ActiveFinder.php is ...
 * todo: add SQL monitor
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveQuery extends BaseQuery implements \IteratorAggregate, \ArrayAccess, \Countable
{
	public $modelClass;

	public $with;
	public $alias;
	public $indexBy;
	public $asArray;

	public $records;
	public $sql;

	public function all($refresh = false)
	{
		if ($this->records === null || $refresh) {
			$this->records = $this->performQuery();
		}
		return $this->records;
	}

	public function one($refresh = false)
	{
		if ($this->records === null || $refresh) {
			$this->limit = 1;
			$this->records = $this->performQuery();
		}
		if (isset($this->records[0])) {
			return $this->records[0];
		} else {
			return null;
		}
	}

	public function asArray($value = true)
	{
		$this->asArray = $value;
		return $this;
	}

	public function with()
	{
		$this->with = func_get_args();
		return $this;
	}

	public function indexBy($column)
	{
		$this->indexBy = $column;
		return $this;
	}

	public function alias($tableAlias)
	{
		$this->alias = $tableAlias;
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
	 * @return Iterator an iterator for traversing the items in the vector.
	 */
	public function getIterator()
	{
		if ($this->records === null) {
			$this->records = $this->performQuery();
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
				$this->records = $this->performQuery();
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
			$this->records = $this->performQuery();
		}
		return isset($this->records[$offset]);
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $vector[$offset];`.
	 * This is equivalent to [[itemAt]].
	 * @param integer $offset the offset to retrieve item.
	 * @return mixed the item at the offset
	 * @throws Exception if the offset is out of range
	 */
	public function offsetGet($offset)
	{
		if ($this->records === null) {
			$this->records = $this->performQuery();
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
	 * @param mixed $item the item value
	 * @throws Exception if the offset is out of range, or the vector is read only.
	 */
	public function offsetSet($offset, $item)
	{
		if ($this->records === null) {
			$this->records = $this->performQuery();
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
			$this->records = $this->performQuery();
		}
		unset($this->records[$offset]);
	}

	protected function performQuery()
	{
		$db = $this->getDbConnection();
		$this->sql = $this->getSql($db);
		$command = $db->createCommand($this->sql);
		$command->bindValues($this->params);
		$rows = $command->queryAll();
		if ($this->asArray) {
			$records = $rows;
		} else {
			$records = array();
			$class = $this->modelClass;
			foreach ($rows as $row) {
				$records[] = $class::populateData($row);
			}
		}
		return $records;
	}

	protected function performCountQuery()
	{
		$this->select = 'COUNT(*)';
		$class = $this->modelClass;
		$command = $this->createCommand($class::getDbConnection());
		$this->sql = $command->getSql();
		$count = $command->queryScalar();
		return $count;
	}
}

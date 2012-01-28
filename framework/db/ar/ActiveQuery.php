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

	private $_count;
	private $_sql;
	private $_countSql;
	private $_records;

	public function all()
	{
		return $this->performQuery();
	}

	public function one()
	{
		$this->limit = 1;
		$records = $this->performQuery();
		if (isset($records[0])) {
			$this->_count = 1;
			return $records[0];
		} else {
			$this->_count = 0;
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

	protected function performQuery()
	{
		$class = $this->modelClass;
		$db = $class::getDbConnection();
		$this->_sql = $this->getSql($db);
		$command = $db->createCommand($this->_sql);
		$command->bindValues($this->params);
		$rows = $command->queryAll();
		if ($this->_asArray) {
			$records = $rows;
		} else {
			$records = array();
			foreach ($rows as $row) {
				$records[] = $class::populateRecord($row);
			}
		}
		$this->_count = count($records);
		return $records;
	}

//
//	public function getSql($connection = null)
//	{
//
//	}

	public function setSql($value)
	{
		$this->_sql = $value;
	}

	public function getCountSql()
	{

	}

	public function getOneSql()
	{

	}

	/**
	 * Returns the number of items in the vector.
	 * @return integer the number of items in the vector
	 */
	public function getCount()
	{
		if ($this->_count !== null) {
			return $this->_count;
		} else {
			return $this->_count = $this->performCountQuery();
		}
	}

	protected function performCountQuery()
	{
		$select = $this->select;
		$this->select = 'COUNT(*)';
		$class = $this->modelClass;
		$command = $this->createCommand($class::getDbConnection());
		$this->_countSql = $command->getSql();
		$count = $command->queryScalar();
		$this->select = $select;
		return $count;
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
		$this->connection->cache($duration, $dependency, $queryCount);
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
		$records = $this->performQuery();
		return new VectorIterator($records);
	}

	/**
	 * Returns the number of items in the vector.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($vector)`.
	 * @return integer number of items in the vector.
	 */
	public function count()
	{
		return $this->getCount();
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
		if ($this->_records === null) {
			$this->_records = $this->performQuery();
		}
		return isset($this->_records[$offset]);
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
		if ($this->_records === null) {
			$this->_records = $this->performQuery();
		}
		return isset($this->_records[$offset]) ? $this->_records[$offset] : null;
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
		if ($this->_records === null) {
			$this->_records = $this->performQuery();
		}
		$this->_records[$offset] = $item;
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
		if ($this->_records === null) {
			$this->_records = $this->performQuery();
		}
		unset($this->_records[$offset]);
	}
}

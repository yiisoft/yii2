<?php
/**
 * DbCache class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\Exception;
use yii\db\dao\Connection;
use yii\db\dao\Query;

/**
 * DbCache implements a cache application component by storing cached data in a database.
 *
 * DbCache stores cache data in a DB table whose name is specified via [[cacheTableName]].
 * For MySQL database, the table should be created beforehand as follows :
 *
 * ~~~
 * CREATE TABLE tbl_cache (
 *   id char(128) NOT NULL,
 *   expire int(11) DEFAULT NULL,
 *   data LONGBLOB,
 *   PRIMARY KEY (id),
 *   KEY expire (expire)
 * );
 * ~~~
 *
 * You should replace `LONGBLOB` as follows if you are using a different DBMS:
 *
 * - PostgreSQL: `BYTEA`
 * - SQLite, SQL server, Oracle: `BLOB`
 *
 * DbCache connects to the database via the DB connection specified in [[connectionID]]
 * which must refer to a valid DB application component.
 *
 * Please refer to [[Cache]] for common cache operations that are supported by DbCache.
 *
 * @property Connection $dbConnection The DB connection instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbCache extends Cache
{
	/**
	 * @var string the ID of the [[Connection|DB connection]] application component.
	 * Defaults to 'db'.
	 */
	public $connectionID = 'db';
	/**
	 * @var string name of the DB table to store cache content. Defaults to 'tbl_cache'.
	 * The table must be created before using this cache component.
	 */
	public $cacheTableName = 'tbl_cache';
	/**
	 * @var integer the probability (parts per million) that garbage collection (GC) should be performed
	 * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
	 * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
	 **/
	public $gcProbability = 100;
	/**
	 * @var Connection the DB connection instance
	 */
	private $_db;

	/**
	 * Returns the DB connection instance used for caching purpose.
	 * @return Connection the DB connection instance
	 * @throws Exception if [[connectionID]] does not point to a valid application component.
	 */
	public function getDbConnection()
	{
		if ($this->_db === null) {
			$db = \Yii::$application->getComponent($this->connectionID);
			if ($db instanceof Connection) {
				$this->_db = $db;
			} else {
				throw new Exception("DbCache.connectionID must refer to the ID of a DB connection application component.");
			}
		}
		return $this->_db;
	}

	/**
	 * Sets the DB connection used by the cache component.
	 * @param Connection $value the DB connection instance
	 */
	public function setDbConnection($value)
	{
		$this->_db = $value;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$query = new Query;
		$query->select(array('data'))
			->from($this->cacheTableName)
			->where('id = :id AND (expire = 0 OR expire > :time)', array(':id' => $key, ':time' => time()));
		$db = $this->getDbConnection();
		if ($db->queryCachingDuration >= 0) {
			$duration = $db->queryCachingDuration;
			$db->queryCachingDuration = -1;
			$result = $query->createCommand($db)->queryScalar();
			$db->queryCachingDuration = $duration;
			return $result;
		} else {
			return $query->createCommand($db)->queryScalar();
		}
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		if (empty($keys)) {
			return array();
		}
		$query = new Query;
		$query->select(array('id', 'data'))
			->from($this->cacheTableName)
			->where(array('id' => $keys))
			->andWhere("expire = 0 OR expire > " . time() . ")");

		$db = $this->getDbConnection();
		if ($db->queryCachingDuration >= 0) {
			$duration = $db->queryCachingDuration;
			$db->queryCachingDuration = -1;
			$rows = $query->createCommand($db)->queryAll();
			$db->queryCachingDuration = $duration;
		} else {
			$rows = $query->createCommand($db)->queryAll();
		}

		$results = array();
		foreach ($keys as $key) {
			$results[$key] = false;
		}
		foreach ($rows as $row) {
			$results[$row['id']] = $row['data'];
		}
		return $results;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key, $value, $expire)
	{
		$query = new Query;
		$command = $query->update($this->cacheTableName, array(
			'expire' => $expire > 0 ? $expire + time() : 0,
			'data' => array($value, \PDO::PARAM_LOB),
		), array(
			'id' => $key,
		))->createCommand($this->getDbConnection());

		if ($command->execute()) {
			$this->gc();
			return true;
		} else {
			return $this->addValue($key, $value, $expire);
		}
 	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key, $value, $expire)
	{
		$this->gc();

		if ($expire > 0) {
			$expire += time();
		} else {
			$expire = 0;
		}

		$query = new Query;
		$command = $query->insert($this->cacheTableName, array(
			'id' => $key,
			'expire' => $expire,
			'data' => array($value, \PDO::PARAM_LOB),
		))->createCommand($this->getDbConnection());
		try {
			$command->execute();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		$query = new Query;
		$query->delete($this->cacheTableName, array('id' => $key))
			->createCommand($this->getDbConnection())
			->execute();
		return true;
	}

	/**
	 * Removes the expired data values.
	 * @param boolean $force whether to enforce the garbage collection regardless of [[gcProbability]].
	 * Defaults to false, meaning the actual deletion happens with the probability as specified by [[gcProbability]].
	 */
	public function gc($force = false)
	{
		if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
			$query = new Query;
			$query->delete($this->cacheTableName, 'expire > 0 AND expire < ' . time())
				->createCommand($this->getDbConnection())
				->execute();
		}
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		$query = new Query;
		$query->delete($this->cacheTableName)
			->createCommand($this->getDbConnection())
			->execute();
		return true;
	}
}

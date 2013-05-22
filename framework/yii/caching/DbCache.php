<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Query;

/**
 * DbCache implements a cache application component by storing cached data in a database.
 *
 * By default, DbCache stores session data in a DB table named 'tbl_cache'. This table
 * must be pre-created. The table name can be changed by setting [[cacheTable]].
 *
 * Please refer to [[Cache]] for common cache operations that are supported by DbCache.
 *
 * The following example shows how you can configure the application to use DbCache:
 *
 * ~~~
 * 'cache' => array(
 *     'class' => 'yii\caching\DbCache',
 *     // 'db' => 'mydb',
 *     // 'cacheTable' => 'my_cache',
 * )
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbCache extends Cache
{
	/**
	 * @var Connection|string the DB connection object or the application component ID of the DB connection.
	 * After the DbCache object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 */
	public $db = 'db';
	/**
	 * @var string name of the DB table to store cache content.
	 * The table should be pre-created as follows:
	 *
	 * ~~~
	 * CREATE TABLE tbl_cache (
	 *     id char(128) NOT NULL PRIMARY KEY,
	 *     expire int(11),
	 *     data BLOB
	 * );
	 * ~~~
	 *
	 * where 'BLOB' refers to the BLOB-type of your preferred DBMS. Below are the BLOB type
	 * that can be used for some popular DBMS:
	 *
	 * - MySQL: LONGBLOB
	 * - PostgreSQL: BYTEA
	 * - MSSQL: BLOB
	 *
	 * When using DbCache in a production server, we recommend you create a DB index for the 'expire'
	 * column in the cache table to improve the performance.
	 */
	public $cacheTable = 'tbl_cache';
	/**
	 * @var integer the probability (parts per million) that garbage collection (GC) should be performed
	 * when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
	 * This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.
	 **/
	public $gcProbability = 100;


	/**
	 * Initializes the DbCache component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->db)) {
			$this->db = Yii::$app->getComponent($this->db);
		}
		if (!$this->db instanceof Connection) {
			throw new InvalidConfigException("DbCache::db must be either a DB connection instance or the application component ID of a DB connection.");
		}
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		$query = new Query;
		$query->select(array('data'))
			->from($this->cacheTable)
			->where('[[id]] = :id AND ([[expire]] = 0 OR [[expire]] >' . time() . ')', array(':id' => $key));
		if ($this->db->enableQueryCache) {
			// temporarily disable and re-enable query caching
			$this->db->enableQueryCache = false;
			$result = $query->createCommand($this->db)->queryScalar();
			$this->db->enableQueryCache = true;
			return $result;
		} else {
			return $query->createCommand($this->db)->queryScalar();
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
			->from($this->cacheTable)
			->where(array('id' => $keys))
			->andWhere('([[expire]] = 0 OR [[expire]] > ' . time() . ')');

		if ($this->db->enableQueryCache) {
			$this->db->enableQueryCache = false;
			$rows = $query->createCommand($this->db)->queryAll();
			$this->db->enableQueryCache = true;
		} else {
			$rows = $query->createCommand($this->db)->queryAll();
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
		$command = $this->db->createCommand()
			->update($this->cacheTable, array(
				'expire' => $expire > 0 ? $expire + time() : 0,
				'data' => array($value, \PDO::PARAM_LOB),
			), array(
				'id' => $key,
			));

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

		try {
			$this->db->createCommand()
				->insert($this->cacheTable, array(
					'id' => $key,
					'expire' => $expire,
					'data' => array($value, \PDO::PARAM_LOB),
				))->execute();
			return true;
		} catch (\Exception $e) {
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
		$this->db->createCommand()
			->delete($this->cacheTable, array('id' => $key))
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
			$this->db->createCommand()
				->delete($this->cacheTable, '[[expire]] > 0 AND [[expire]] < ' . time())
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
		$this->db->createCommand()
			->delete($this->cacheTable)
			->execute();
		return true;
	}
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\redis\Connection;

/**
 * RedisCache implements a cache application component based on [redis](http://redis.io/) version 2.6 or higher.
 *
 * RedisCache needs to be configured with [[hostname]], [[port]] and [[database]] of the server
 * to connect to. By default RedisCache assumes there is a redis server running on localhost at
 * port 6379 and uses the database number 0.
 *
 * RedisCache also supports [the AUTH command](http://redis.io/commands/auth) of redis.
 * When the server needs authentication, you can set the [[password]] property to
 * authenticate with the server after connect.
 *
 * See [[Cache]] manual for common cache operations that RedisCache supports.
 * Unlike the [[CCache]], RedisCache allows the expire parameter of
 * [[set]] and [[add]] to be a floating point number, so you may specify the time in milliseconds.
 *
 * To use RedisCache as the cache application component, configure the application as follows,
 *
 * ~~~
 * [
 *     'components' => [
 *         'cache' => [
 *             'class' => 'RedisCache',
 *             'hostname' => 'localhost',
 *             'port' => 6379,
 *             'database' => 0,
 *         ],
 *     ],
 * ]
 * ~~~
 *
 * @property Connection $connection The redis connection object. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class RedisCache extends Cache
{
	/**
	 * @var string hostname to use for connecting to the redis server. Defaults to 'localhost'.
	 */
	public $hostname = 'localhost';
	/**
	 * @var int the port to use for connecting to the redis server. Default port is 6379.
	 */
	public $port = 6379;
	/**
	 * @var string the password to use to authenticate with the redis server. If not set, no AUTH command will be sent.
	 */
	public $password;
	/**
	 * @var int the redis database to use. This is an integer value starting from 0. Defaults to 0.
	 */
	public $database = 0;
	/**
	 * @var float timeout to use for connection to redis. If not set the timeout set in php.ini will be used: ini_get("default_socket_timeout")
	 */
	public $connectionTimeout = null;
	/**
	 * @var float timeout to use for redis socket when reading and writing data. If not set the php default value will be used.
	 */
	public $dataTimeout = null;
	/**
	 * @var Connection the redis connection
	 */
	private $_connection;


	/**
	 * Initializes the cache component by establishing a connection to the redis server.
	 */
	public function init()
	{
		parent::init();
		$this->getConnection();
	}

	/**
	 * Returns the redis connection object.
	 * Establishes a connection to the redis server if it does not already exists.
	 * @return Connection the redis connection object.
	 */
	public function getConnection()
	{
		if ($this->_connection === null) {
			$this->_connection = new Connection([
				'dsn' => 'redis://' . $this->hostname . ':' . $this->port . '/' . $this->database,
				'password' => $this->password,
				'connectionTimeout' => $this->connectionTimeout,
				'dataTimeout' => $this->dataTimeout,
			]);
		}
		return $this->_connection;
	}

	/**
	 * Checks whether a specified key exists in the cache.
	 * This can be faster than getting the value from the cache if the data is big.
	 * Note that this method does not check whether the dependency associated
	 * with the cached data, if there is any, has changed. So a call to [[get]]
	 * may return false while exists returns true.
	 * @param mixed $key a key identifying the cached value. This can be a simple string or
	 * a complex data structure consisting of factors representing the key.
	 * @return boolean true if a value exists in cache, false if the value is not in the cache or expired.
	 */
	public function exists($key)
	{
		return (bool) $this->_connection->executeCommand('EXISTS', [$this->buildKey($key)]);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return $this->_connection->executeCommand('GET', [$key]);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		$response = $this->_connection->executeCommand('MGET', $keys);
		$result = [];
		$i = 0;
		foreach($keys as $key) {
			$result[$key] = $response[$i++];
		}
		return $result;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param float $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * This can be a floating point number to specify the time in milliseconds.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key,$value,$expire)
	{
		if ($expire == 0) {
			return (bool) $this->_connection->executeCommand('SET', [$key, $value]);
		} else {
			$expire = (int) ($expire * 1000);
			return (bool) $this->_connection->executeCommand('PSETEX', [$key, $expire, $value]);
		}
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This is the implementation of the method declared in the parent class.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param float $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * This can be a floating point number to specify the time in milliseconds.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key,$value,$expire)
	{
		if ($expire == 0) {
			return (bool) $this->_connection->executeCommand('SETNX', [$key, $value]);
		} else {
			// TODO consider requiring redis version >= 2.6.12 that supports this in one command
			$expire = (int) ($expire * 1000);
			$this->_connection->executeCommand('MULTI');
			$this->_connection->executeCommand('SETNX', [$key, $value]);
			$this->_connection->executeCommand('PEXPIRE', [$key, $expire]);
			$response = $this->_connection->executeCommand('EXEC');
			return (bool) $response[0];
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
		return (bool) $this->_connection->executeCommand('DEL', [$key]);
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		return $this->_connection->executeCommand('FLUSHDB');
	}
}

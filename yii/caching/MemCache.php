<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * MemCache implements a cache application component based on [memcache](http://pecl.php.net/package/memcache)
 * and [memcached](http://pecl.php.net/package/memcached).
 *
 * MemCache supports both [memcache](http://pecl.php.net/package/memcache) and
 * [memcached](http://pecl.php.net/package/memcached). By setting [[useMemcached]] to be true or false,
 * one can let MemCache to use either memcached or memcache, respectively.
 *
 * MemCache can be configured with a list of memcache servers by settings its [[servers]] property.
 * By default, MemCache assumes there is a memcache server running on localhost at port 11211.
 *
 * See [[Cache]] for common cache operations that ApcCache supports.
 *
 * Note, there is no security measure to protected data in memcache.
 * All data in memcache can be accessed by any process running in the system.
 *
 * To use MemCache as the cache application component, configure the application as follows,
 *
 * ~~~
 * array(
 *     'components' => array(
 *         'cache' => array(
 *             'class' => 'MemCache',
 *             'servers' => array(
 *                 array(
 *                     'host' => 'server1',
 *                     'port' => 11211,
 *                     'weight' => 60,
 *                 ),
 *                 array(
 *                     'host' => 'server2',
 *                     'port' => 11211,
 *                     'weight' => 40,
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 * ~~~
 *
 * In the above, two memcache servers are used: server1 and server2. You can configure more properties of
 * each server, such as `persistent`, `weight`, `timeout`. Please see [[MemCacheServer]] for available options.
 *
 * @property \Memcache|\Memcached $memCache The memcache instance (or memcached if [[useMemcached]] is true) used by this component.
 * @property MemCacheServer[] $servers List of memcache server configurations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCache extends Cache
{
	/**
	 * @var boolean whether to use memcached or memcache as the underlying caching extension.
	 * If true, [memcached](http://pecl.php.net/package/memcached) will be used.
	 * If false, [memcache](http://pecl.php.net/package/memcache) will be used.
	 * Defaults to false.
	 */
	public $useMemcached = false;
	/**
	 * @var \Memcache|\Memcached the Memcache instance
	 */
	private $_cache = null;
	/**
	 * @var array list of memcache server configurations
	 */
	private $_servers = array();

	/**
	 * Initializes this application component.
	 * It creates the memcache instance and adds memcache servers.
	 */
	public function init()
	{
		parent::init();
		$servers = $this->getServers();
		$cache = $this->getMemCache();
		if (count($servers)) {
			foreach ($servers as $server) {
				if ($server->host === null) {
					throw new Exception("The 'host' property must be specified for every memcache server.");
				}
				if ($this->useMemcached) {
					$cache->addServer($server->host, $server->port, $server->weight);
				} else {
					$cache->addServer($server->host, $server->port, $server->persistent,
						$server->weight, $server->timeout, $server->retryInterval, $server->status);
				}
			}
		} else {
			$cache->addServer('127.0.0.1', 11211);
		}
	}

	/**
	 * Returns the underlying memcache (or memcached) object.
	 * @return \Memcache|\Memcached the memcache (or memcached) object used by this cache component.
	 * @throws InvalidConfigException if memcache or memcached extension is not loaded
	 */
	public function getMemcache()
	{
		if ($this->_cache === null) {
			$extension = $this->useMemcached ? 'memcached' : 'memcache';
			if (!extension_loaded($extension)) {
				throw new InvalidConfigException("MemCache requires PHP $extension extension to be loaded.");
			}
			$this->_cache = $this->useMemcached ? new \Memcached : new \Memcache;
		}
		return $this->_cache;
	}

	/**
	 * Returns the memcache server configurations.
	 * @return MemCacheServer[] list of memcache server configurations.
	 */
	public function getServers()
	{
		return $this->_servers;
	}

	/**
	 * @param array $config list of memcache server configurations. Each element must be an array
	 * with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
	 * @see http://www.php.net/manual/en/function.Memcache-addServer.php
	 */
	public function setServers($config)
	{
		foreach ($config as $c) {
			$this->_servers[] = new MemCacheServer($c);
		}
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return $this->_cache->get($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
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
		if ($expire > 0) {
			$expire += time();
		} else {
			$expire = 0;
		}

		return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
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
		if ($expire > 0) {
			$expire += time();
		} else {
			$expire = 0;
		}

		return $this->useMemcached ? $this->_cache->add($key, $value, $expire) : $this->_cache->add($key, $value, 0, $expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return $this->_cache->delete($key, 0);
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		return $this->_cache->flush();
	}
}

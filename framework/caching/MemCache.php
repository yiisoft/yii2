<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
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
 * See [[Cache]] for common cache operations that MemCache supports.
 *
 * Note, there is no security measure to protected data in memcache.
 * All data in memcache can be accessed by any process running in the system.
 *
 * To use MemCache as the cache application component, configure the application as follows,
 *
 * ```php
 * [
 *     'components' => [
 *         'cache' => [
 *             'class' => 'yii\caching\MemCache',
 *             'servers' => [
 *                 [
 *                     'host' => 'server1',
 *                     'port' => 11211,
 *                     'weight' => 60,
 *                 ],
 *                 [
 *                     'host' => 'server2',
 *                     'port' => 11211,
 *                     'weight' => 40,
 *                 ],
 *             ],
 *         ],
 *     ],
 * ]
 * ```
 *
 * In the above, two memcache servers are used: server1 and server2. You can configure more properties of
 * each server, such as `persistent`, `weight`, `timeout`. Please see [[MemCacheServer]] for available options.
 *
 * @property \Memcache|\Memcached $memcache The memcache (or memcached) object used by this cache component.
 * This property is read-only.
 * @property MemCacheServer[] $servers List of memcache server configurations. Note that the type of this
 * property differs in getter and setter. See [[getServers()]] and [[setServers()]] for details.
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
     * @var string an ID that identifies a Memcached instance. This property is used only when [[useMemcached]] is true.
     * By default the Memcached instances are destroyed at the end of the request. To create an instance that
     * persists between requests, you may specify a unique ID for the instance. All instances created with the
     * same ID will share the same connection.
     * @see http://ca2.php.net/manual/en/memcached.construct.php
     */
    public $persistentId;
    /**
     * @var array options for Memcached. This property is used only when [[useMemcached]] is true.
     * @see http://ca2.php.net/manual/en/memcached.setoptions.php
     */
    public $options;
    /**
     * @var string memcached sasl username. This property is used only when [[useMemcached]] is true.
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $username;
    /**
     * @var string memcached sasl password. This property is used only when [[useMemcached]] is true.
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $password;

    /**
     * @var \Memcache|\Memcached the Memcache instance
     */
    private $_cache;
    /**
     * @var array list of memcache server configurations
     */
    private $_servers = [];


    /**
     * Initializes this application component.
     * It creates the memcache instance and adds memcache servers.
     */
    public function init()
    {
        parent::init();
        $this->addServers($this->getMemcache(), $this->getServers());
    }

    /**
     * Add servers to the server pool of the cache specified
     *
     * @param \Memcache|\Memcached $cache
     * @param MemCacheServer[] $servers
     * @throws InvalidConfigException
     */
    protected function addServers($cache, $servers)
    {
        if (empty($servers)) {
            $servers = [new MemCacheServer([
                'host' => '127.0.0.1',
                'port' => 11211,
            ])];
        } else {
            foreach ($servers as $server) {
                if ($server->host === null) {
                    throw new InvalidConfigException("The 'host' property must be specified for every memcache server.");
                }
            }
        }
        if ($this->useMemcached) {
            $this->addMemcachedServers($cache, $servers);
        } else {
            $this->addMemcacheServers($cache, $servers);
        }
    }

    /**
     * Add servers to the server pool of the cache specified
     * Used for memcached PECL extension.
     *
     * @param \Memcached $cache
     * @param MemCacheServer[] $servers
     */
    protected function addMemcachedServers($cache, $servers)
    {
        $existingServers = [];
        if ($this->persistentId !== null) {
            foreach ($cache->getServerList() as $s) {
                $existingServers[$s['host'] . ':' . $s['port']] = true;
            }
        }
        foreach ($servers as $server) {
            if (empty($existingServers) || !isset($existingServers[$server->host . ':' . $server->port])) {
                $cache->addServer($server->host, $server->port, $server->weight);
            }
        }
    }

    /**
     * Add servers to the server pool of the cache specified
     * Used for memcache PECL extension.
     *
     * @param \Memcache $cache
     * @param MemCacheServer[] $servers
     */
    protected function addMemcacheServers($cache, $servers)
    {
        $class = new \ReflectionClass($cache);
        $paramCount = $class->getMethod('addServer')->getNumberOfParameters();
        foreach ($servers as $server) {
            // $timeout is used for memcache versions that do not have $timeoutms parameter
            $timeout = (int) ($server->timeout / 1000) + (($server->timeout % 1000 > 0) ? 1 : 0);
            if ($paramCount === 9) {
                $cache->addserver(
                    $server->host,
                    $server->port,
                    $server->persistent,
                    $server->weight,
                    $timeout,
                    $server->retryInterval,
                    $server->status,
                    $server->failureCallback,
                    $server->timeout
                );
            } else {
                $cache->addserver(
                    $server->host,
                    $server->port,
                    $server->persistent,
                    $server->weight,
                    $timeout,
                    $server->retryInterval,
                    $server->status,
                    $server->failureCallback
                );
            }
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

            if ($this->useMemcached) {
                $this->_cache = $this->persistentId !== null ? new \Memcached($this->persistentId) : new \Memcached;
                if ($this->username !== null || $this->password !== null) {
                    $this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                    $this->_cache->setSaslAuthData($this->username, $this->password);
                }
                if (!empty($this->options)) {
                    $this->_cache->setOptions($this->options);
                }
            } else {
                $this->_cache = new \Memcache;
            }
        }

        return $this->_cache;
    }

    /**
     * Returns the memcache or memcached server configurations.
     * @return MemCacheServer[] list of memcache server configurations.
     */
    public function getServers()
    {
        return $this->_servers;
    }

    /**
     * @param array $config list of memcache or memcached server configurations. Each element must be an array
     * with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
     * @see http://php.net/manual/en/memcache.addserver.php
     * @see http://php.net/manual/en/memcached.addserver.php
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
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
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
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    protected function setValue($key, $value, $duration)
    {
        $duration = $this->trimDuration($duration);
        $expire = $duration > 0 ? $duration + time() : 0;

        return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array array of failed keys. Always empty in case of using memcached.
     */
    protected function setValues($data, $duration)
    {
        $duration = $this->trimDuration($duration);

        if ($this->useMemcached) {
            $this->_cache->setMulti($data, $duration > 0 ? $duration + time() : 0);

            return [];
        } else {
            return parent::setValues($data, $duration);
        }
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    protected function addValue($key, $value, $duration)
    {
        $duration = $this->trimDuration($duration);
        $expire = $duration > 0 ? $duration + time() : 0;

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

    /**
     * Trims duration to 30 days (2592000 seconds).
     * @param integer $duration the number of seconds
     * @return integer the duration
     * @since 2.0.7
     * @see http://php.net/manual/en/memcache.set.php
     * @see http://php.net/manual/en/memcached.expiration.php
     */
    protected function trimDuration($duration)
    {
        if ($duration > 2592000) {
            Yii::warning('Duration has been truncated to 30 days due to Memcache/Memcached limitation.', __METHOD__);
            return 2592000;
        }
        return $duration;
    }
}

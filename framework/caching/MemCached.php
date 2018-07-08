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
 * MemCached implements a cache application component based on [memcached](http://pecl.php.net/package/memcached) PECL
 * extension.
 *
 * MemCached can be configured with a list of memcached servers by settings its [[servers]] property.
 * By default, MemCached assumes there is a memcached server running on localhost at port 11211.
 *
 * See [[\Psr\SimpleCache\CacheInterface]] for common cache operations that MemCached supports.
 *
 * Note, there is no security measure to protected data in memcached.
 * All data in memcached can be accessed by any process running in the system.
 *
 * To use MemCached as the cache application component, configure the application as follows,
 *
 * ```php
 * [
 *     'components' => [
 *         'cache' => [
 *             '__class' => \yii\caching\Cache::class,
 *             'handler' => [
 *                 '__class' => \yii\caching\MemCached::class,
 *                 'servers' => [
 *                     [
 *                         'host' => 'server1',
 *                         'port' => 11211,
 *                         'weight' => 60,
 *                     ],
 *                     [
 *                         'host' => 'server2',
 *                         'port' => 11211,
 *                         'weight' => 40,
 *                     ],
 *                 ],
 *             ],
 *         ],
 *     ],
 * ]
 * ```
 *
 * In the above, two memcached servers are used: server1 and server2. You can configure more properties of
 * each server, such as `persistent`, `weight`, `timeout`. Please see [[MemCacheServer]] for available options.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @property \Memcached $memcached The memcached object used by this cache component.
 * This property is read-only.
 * @property MemCachedServer[] $servers List of memcached server configurations. Note that the type of this
 * property differs in getter and setter. See [[getServers()]] and [[setServers()]] for details.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCached extends SimpleCache
{
    /**
     * @var string an ID that identifies a Memcached instance.
     * By default the Memcached instances are destroyed at the end of the request. To create an instance that
     * persists between requests, you may specify a unique ID for the instance. All instances created with the
     * same ID will share the same connection.
     * @see http://ca2.php.net/manual/en/memcached.construct.php
     */
    public $persistentId;
    /**
     * @var array options for Memcached.
     * @see http://ca2.php.net/manual/en/memcached.setoptions.php
     */
    public $options;
    /**
     * @var string memcached sasl username.
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $username;
    /**
     * @var string memcached sasl password.
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $password;

    /**
     * @var \Memcached the Memcached instance
     */
    private $_cache;
    /**
     * @var array list of memcached server configurations
     */
    private $_servers = [];


    /**
     * Initializes this application component.
     * It creates the memcached instance and adds memcached servers.
     */
    public function init()
    {
        parent::init();
        $this->addServers($this->getMemcached(), $this->getServers());
    }

    /**
     * Add servers to the server pool of the cache specified
     *
     * @param \Memcached $cache
     * @param MemCachedServer[] $servers
     * @throws InvalidConfigException
     */
    protected function addServers($cache, $servers)
    {
        if (empty($servers)) {
            $servers = [new MemCachedServer([
                'host' => '127.0.0.1',
                'port' => 11211,
            ])];
        } else {
            foreach ($servers as $server) {
                if ($server->host === null) {
                    throw new InvalidConfigException("The 'host' property must be specified for every memcached server.");
                }
            }
        }

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
     * Returns the underlying memcached object.
     * @return \Memcached the memcached object used by this cache component.
     * @throws InvalidConfigException if memcached extension is not loaded
     */
    public function getMemcached()
    {
        if ($this->_cache === null) {
            if (!extension_loaded('memcached')) {
                throw new InvalidConfigException('MemCached requires PHP memcached extension to be loaded.');
            }

            $this->_cache = $this->persistentId !== null ? new \Memcached($this->persistentId) : new \Memcached;
            if ($this->username !== null || $this->password !== null) {
                $this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                $this->_cache->setSaslAuthData($this->username, $this->password);
            }
            if (!empty($this->options)) {
                $this->_cache->setOptions($this->options);
            }
        }

        return $this->_cache;
    }

    /**
     * Returns the memcached server configurations.
     * @return MemCachedServer[] list of memcached server configurations.
     */
    public function getServers()
    {
        return $this->_servers;
    }

    /**
     * @param array $config list of memcached server configurations. Each element must be an array
     * with the following keys: host, port, persistent, weight, timeout, retryInterval, status.
     * @see http://php.net/manual/en/memcached.addserver.php
     */
    public function setServers($config)
    {
        foreach ($config as $c) {
            $this->_servers[] = new MemCachedServer($c);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        return $this->_cache->get($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValues($keys)
    {
        return $this->_cache->getMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $ttl)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $ttl > 0 ? $ttl + time() : 0;

        return $this->_cache->set($key, $value, $expire);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValues($values, $ttl)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $ttl > 0 ? $ttl + time() : 0;

        // Memcached::setMulti() returns boolean
        // @see http://php.net/manual/en/memcached.setmulti.php
        return $this->_cache->setMulti($values, $expire) ? [] : array_keys($values);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->_cache->flush();
    }
}

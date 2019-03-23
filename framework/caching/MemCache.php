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
 * MemCache 是基于 [memcache](http://pecl.php.net/package/memcache)
 * 和 [memcached](http://pecl.php.net/package/memcached) 实现的缓存应用组件。
 *
 * MemCache 支持 [memcache](http://pecl.php.net/package/memcache) 和
 * [memcached](http://pecl.php.net/package/memcached)。通过设置 [[useMemcached]] 为 true 或者 false，
 * 你可以让 MemCache 在使用 memcached 或者 memcache 之间随意切换。
 *
 * MemCache 通过设置 [[servers]] 属性来配置 memcache 服务器列表。
 * 默认情况下，MemCache 会认为有一个服务器运行在 localhost 的 11211 端口。
 *
 * 可以参考 [[Cache]] 查看 MemCache 支持的通用的缓存操作方法。
 *
 * 注意，存入 memcache 的数据并不会有任何安全保障措施。
 * 这些都可以被运行在同一个服务器上的任何其它进程访问。
 *
 * 要把 MemCache 当作缓存应用组件使用，参考下述的配置， 
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
 * 上述配置使用了两个 memcache 服务器：server1 和 server2。你也可以给每个服务器配置更多的属性，
 * 比如 `persistent`，`weight`，`timeout`。可用的配置选项可以参考 [[MemCacheServer]]。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @property \Memcache|\Memcached $memcache 缓存组件使用的 memcache（或者 memcached）对象。
 * 该属性只读。
 * @property MemCacheServer[] $servers memcache 服务器配置列表。
 * 注意该属性的操作类型不同于 getter 和 setter 方法。详情参考 [[getServers()]] 和 [[setServers()]] 方法。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MemCache extends Cache
{
    /**
     * @var bool 使用 memcached 还是 memcache 作为底层的缓存扩展。
     * 如果是 true，[memcached](http://pecl.php.net/package/memcached) 将会使用。
     * 如果是 false，[memcache](http://pecl.php.net/package/memcache) 将会使用。
     * 默认是 false。
     */
    public $useMemcached = false;
    /**
     * @var string 一个表示 Memcached 实例的字符串 ID。它在 [[useMemcached]] 为 true 时使用。
     * 默认情况下 Memcached 实例会在请求结束后销毁。为了创建一个在多请求间持久稳定的实例，
     * 你可以为实例指定一个唯一的 ID。
     * 这样所有基于同样的 ID 创建的实例都共享同一个连接。
     * @see http://ca2.php.net/manual/en/memcached.construct.php
     */
    public $persistentId;
    /**
     * @var array Memcached 的配置选项。它在 [[useMemcached]] 为 true 时使用。
     * @see http://ca2.php.net/manual/en/memcached.setoptions.php
     */
    public $options;
    /**
     * @var string memcached sasl 用户名。它在 [[useMemcached]] 为 true 时使用。
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $username;
    /**
     * @var string memcached sasl 密码。它在 [[useMemcached]] 为 true 时使用。
     * @see http://php.net/manual/en/memcached.setsaslauthdata.php
     */
    public $password;

    /**
     * @var \Memcache|\Memcached Memcache 对象
     */
    private $_cache;
    /**
     * @var array memcache 服务器配置列表
     */
    private $_servers = [];


    /**
     * 初始化应用组件。
     * 它将完成 memcache 实例化并添加 memcache 服务器。
     */
    public function init()
    {
        parent::init();
        $this->addServers($this->getMemcache(), $this->getServers());
    }

    /**
     * 添加服务器到缓存对象的服务器池。
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
     * 添加服务器到缓存对象的服务器池。
     * 这是使用 memcached PECL 扩展的情况。
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
     * 添加服务器到缓存对象的服务器池。
     * 这是使用 memcache PECL 扩展的情况。
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
     * 返回底层的 memcache（或者 memcached）对象。
     * @return \Memcache|\Memcached 应用组件使用的 memcache（或者 memcached）对象。
     * @throws InvalidConfigException 如果 memcache 或者 memcached 扩展没有加载。
     */
    public function getMemcache()
    {
        if ($this->_cache === null) {
            $extension = $this->useMemcached ? 'memcached' : 'memcache';
            if (!extension_loaded($extension)) {
                throw new InvalidConfigException("MemCache requires PHP $extension extension to be loaded.");
            }

            if ($this->useMemcached) {
                $this->_cache = $this->persistentId !== null ? new \Memcached($this->persistentId) : new \Memcached();
                if ($this->username !== null || $this->password !== null) {
                    $this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                    $this->_cache->setSaslAuthData($this->username, $this->password);
                }
                if (!empty($this->options)) {
                    $this->_cache->setOptions($this->options);
                }
            } else {
                $this->_cache = new \Memcache();
            }
        }

        return $this->_cache;
    }

    /**
     * 返回 memcache 或者 memcached 服务器的配置。
     * @return MemCacheServer[] memcache 服务器配置列表。
     */
    public function getServers()
    {
        return $this->_servers;
    }

    /**
     * @param array $config memcache 或者 memcached 服务器配置列表。每个元素必须是一个数组，
     * 数组的键可以是：host，port，persistent，weight，timeout，retryInterval，status。
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
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        return $this->_cache->get($key);
    }

    /**
     * 根据多个缓存键从缓存中一次获取多个缓存数据。
     * @param array $keys 指明缓存数据的缓存键列表。
     * @return array 由缓存键组成下标的缓存数据列表。
     */
    protected function getValues($keys)
    {
        return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。
     * @see [Memcache::set()](http://php.net/manual/en/memcache.set.php)
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcache.set.php
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $duration > 0 ? $duration + time() : 0;

        return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
    }

    /**
     * 一次性存入多个 键-值 对到缓存中。
     * @param array $data 数组，数组的键对应缓存的键而值就是要缓存的值。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @return array 未能存入缓存数据的键列表。
     */
    protected function setValues($data, $duration)
    {
        if ($this->useMemcached) {
            // Use UNIX timestamp since it doesn't have any limitation
            // @see http://php.net/manual/en/memcache.set.php
            // @see http://php.net/manual/en/memcached.expiration.php
            $expire = $duration > 0 ? $duration + time() : 0;

            // Memcached::setMulti() returns boolean
            // @see http://php.net/manual/en/memcached.setmulti.php
            return $this->_cache->setMulti($data, $expire) ? [] : array_keys($data);
        }

        return parent::setValues($data, $duration);
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。
     * @see [Memcache::set()](http://php.net/manual/en/memcache.set.php)
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcache.set.php
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $duration > 0 ? $duration + time() : 0;

        return $this->useMemcached ? $this->_cache->add($key, $value, $expire) : $this->_cache->add($key, $value, 0, $expire);
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key, 0);
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        return $this->_cache->flush();
    }
}

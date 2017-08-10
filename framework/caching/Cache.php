<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\base\Component;
use yii\di\Instance;
use yii\helpers\StringHelper;

/**
 * Cache provides support for the data caching, including cache key composition, dependencies and data serialization.
 * The actual data caching is performed via [[handler]], which should be configured to be [[\Psr\SimpleCache\CacheInterface]]
 * instance.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             'class' => yii\caching\Cache::class,
 *             'handler' => [
 *                 'class' => yii\caching\FileCache::class,
 *                 'cachePath' => '@runtime/cache',
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * A data item can be stored in the cache by calling [[set()]] and be retrieved back
 * later (in the same or different request) by [[get()]]. In both operations,
 * a key identifying the data item is required. An expiration time and/or a [[Dependency|dependency]]
 * can also be specified when calling [[set()]]. If the data item expires or the dependency
 * changes at the time of calling [[get()]], the cache will return no data.
 *
 * A typical usage pattern of cache is like the following:
 *
 * ```php
 * $key = 'demo';
 * $data = $cache->get($key);
 * if ($data === null) {
 *     // ...generate $data here...
 *     $cache->set($key, $data, $duration, $dependency);
 * }
 * ```
 *
 * Because Cache implements the [[\ArrayAccess]] interface, it can be used like an array. For example,
 *
 * ```php
 * $cache['foo'] = 'some data';
 * echo $cache['foo'];
 * ```
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview)
 * and [PSR-16 specification](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Cache extends Component implements CacheInterface
{
    /**
     * @var string a string prefixed to every cache key so that it is unique globally in the whole cache storage.
     * It is recommended that you set a unique cache key prefix for each application if the same cache
     * storage is being used by different applications.
     *
     * To ensure interoperability, only alphanumeric characters should be used.
     */
    public $keyPrefix;
    /**
     * @var null|array|false the functions used to serialize and unserialize cached data. Defaults to null, meaning
     * using the default PHP `serialize()` and `unserialize()` functions. If you want to use some more efficient
     * serializer (e.g. [igbinary](http://pecl.php.net/package/igbinary)), you may configure this property with
     * a two-element array. The first element specifies the serialization function, and the second the deserialization
     * function. If this property is set false, data will be directly sent to and retrieved from the underlying
     * cache component without any serialization or deserialization. You should not turn off serialization if
     * you are using [[Dependency|cache dependency]], because it relies on data serialization. Also, some
     * implementations of the cache can not correctly save and retrieve data different from a string type.
     */
    public $serializer;
    /**
     * @var \Psr\SimpleCache\CacheInterface|array|\Closure|string actual cache handler or its DI compatible configuration.
     * After the Cache object is created, if you want to change this property, you should only assign it
     * with a [[\Psr\SimpleCache\CacheInterface]] instance.
     * @since 2.1.0
     */
    public $handler;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->handler = Instance::ensure($this->handler instanceof \Closure ? call_user_func($this->handler) : $this->handler, \Psr\SimpleCache\CacheInterface::class);
    }

    /**
     * Builds a normalized cache key from a given key.
     *
     * If the given key is a string containing alphanumeric characters only and no more than 32 characters,
     * then the key will be returned back prefixed with [[keyPrefix]]. Otherwise, a normalized key
     * is generated by serializing the given key, applying MD5 hashing, and prefixing with [[keyPrefix]].
     *
     * @param mixed $key the key to be normalized
     * @return string the generated cache key
     */
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && StringHelper::byteLength($key) <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $key = $this->buildKey($key);
        $value = $this->handler->get($key);
        if ($value === null || $this->serializer === false) {
            return $default;
        } elseif ($this->serializer === null) {
            $value = unserialize($value);
        } else {
            $value = call_user_func($this->serializer[1], $value);
        }
        if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
            return $value[0];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $key = $this->buildKey($key);
        return $this->handler->has($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * Some caches (such as memcache, apc) allow retrieving multiple cached values at the same time,
     * which may improve the performance. In case a cache does not support this feature natively,
     * this method will try to simulate it.
     * @param string[] $keys list of string keys identifying the cached values
     * @param mixed $default Default value to return for keys that do not exist.
     * @return array list of cached values corresponding to the specified keys. The array
     * is returned in terms of (key, value) pairs.
     * If a value is not cached or expired, the corresponding array value will be false.
     * @since 2.0.7
     */
    public function getMultiple($keys, $default = null)
    {
        $keyMap = [];
        foreach ($keys as $key) {
            $keyMap[$key] = $this->buildKey($key);
        }
        $values = $this->handler->getMultiple(array_values($keyMap));
        $results = [];
        foreach ($keyMap as $key => $newKey) {
            $results[$key] = $default;
            if (isset($values[$newKey])) {
                if ($this->serializer === false) {
                    $results[$key] = $values[$newKey];
                } else {
                    $value = $this->serializer === null ? unserialize($values[$newKey])
                        : call_user_func($this->serializer[1], $values[$newKey]);

                    if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
                        $results[$key] = $value[0];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Stores a value identified by a key into cache.
     * If the cache already contains such a key, the existing value and
     * expiration time will be replaced with the new ones, respectively.
     *
     * @param mixed $key a key identifying the value to be cached. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @param mixed $value the value to be cached
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached item. If the dependency changes,
     * the corresponding value in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return bool whether the value is successfully stored into cache
     */
    public function set($key, $value, $ttl = null, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->handler->set($key, $value, $ttl);
    }

    /**
     * Stores multiple items in cache. Each item contains a value identified by a key.
     * If the cache already contains such a key, the existing value and
     * expiration time will be replaced with the new ones, respectively.
     *
     * @param array $items the items to be cached, as key-value pairs.
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached items. If the dependency changes,
     * the corresponding values in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return array array of failed keys
     * @since 2.0.7
     */
    public function setMultiple($items, $ttl = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        return $this->handler->setMultiple($data, $ttl);
    }

    /**
     * {@inheritdoc}
     * @since 2.1
     */
    public function deleteMultiple($keys)
    {
        $keyMap = [];
        foreach ($keys as $key) {
            $keyMap[] = $this->buildKey($key);
        }
        return $this->handler->deleteMultiple($keyMap);
    }

    /**
     * Stores multiple items in cache. Each item contains a value identified by a key.
     * If the cache already contains such a key, the existing value and expiration time will be preserved.
     *
     * @param array $items the items to be cached, as key-value pairs.
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached items. If the dependency changes,
     * the corresponding values in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return array array of failed keys
     * @since 2.0.7
     */
    public function addMultiple($items, $ttl = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        $values = $this->handler->getMultiple(array_keys($data));
        foreach ($values as $key => $value) {
            if ($value !== null) {
                unset($data[$key]);
            }
        }

        return $this->handler->setMultiple($data, $ttl);
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * Nothing will be done if the cache already contains the key.
     * @param mixed $key a key identifying the value to be cached. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @param mixed $value the value to be cached
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached item. If the dependency changes,
     * the corresponding value in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return bool whether the value is successfully stored into cache
     */
    public function add($key, $value, $ttl = null, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        if ($this->handler->has($key)) {
            return false;
        }

        return $this->handler->set($key, $value, $ttl);
    }

    /**
     * Deletes a value with the specified key from cache
     * @param mixed $key a key identifying the value to be deleted from cache. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @return bool if no error happens during deletion
     */
    public function delete($key)
    {
        $key = $this->buildKey($key);

        return $this->handler->delete($key);
    }

    /**
     * Deletes all values from cache.
     * Be careful of performing this operation if the cache is shared among multiple applications.
     * @return bool whether the flush operation was successful.
     */
    public function clear()
    {
        return $this->handler->clear();
    }

    /**
     * Returns whether there is a cache entry with a specified key.
     * This method is required by the interface [[\ArrayAccess]].
     * @param string $key a key identifying the cached value
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->get($key) !== false;
    }

    /**
     * Retrieves the value from cache with a specified key.
     * This method is required by the interface [[\ArrayAccess]].
     * @param string $key a key identifying the cached value
     * @return mixed the value stored in cache, false if the value is not in the cache or expired.
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Stores the value identified by a key into cache.
     * If the cache already contains such a key, the existing value will be
     * replaced with the new ones. To add expiration and dependencies, use the [[set()]] method.
     * This method is required by the interface [[\ArrayAccess]].
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Deletes the value with the specified key from cache
     * This method is required by the interface [[\ArrayAccess]].
     * @param string $key the key of the value to be deleted
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }

    /**
     * Method combines both [[set()]] and [[get()]] methods to retrieve value identified by a $key,
     * or to store the result of $callable execution if there is no cache available for the $key.
     *
     * Usage example:
     *
     * ```php
     * public function getTopProducts($count = 10) {
     *     $cache = $this->cache; // Could be Yii::$app->cache
     *     return $cache->getOrSet(['top-n-products', 'n' => $count], function ($cache) use ($count) {
     *         return Products::find()->mostPopular()->limit(10)->all();
     *     }, 1000);
     * }
     * ```
     *
     * @param mixed $key a key identifying the value to be cached. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @param callable|\Closure $callable the callable or closure that will be used to generate a value to be cached.
     * In case $callable returns `false`, the value will not be cached.
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached item. If the dependency changes,
     * the corresponding value in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is `false`.
     * @return mixed result of $callable execution
     * @since 2.0.11
     */
    public function getOrSet($key, $callable, $ttl = null, $dependency = null)
    {
        if (($value = $this->get($key)) !== null) {
            return $value;
        }

        $value = call_user_func($callable, $this);
        if (!$this->set($key, $value, $ttl, $dependency)) {
            Yii::warning('Failed to set cache value for key ' . json_encode($key), __METHOD__);
        }

        return $value;
    }
}

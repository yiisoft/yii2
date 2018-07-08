<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * CacheInterface defines the common interface to be implemented by cache classes.
 * It extends [[\Psr\SimpleCache\CacheInterface]] adding ability for cache dependency specification.
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
 * Because CacheInterface extends the [[\ArrayAccess]] interface, it can be used like an array. For example,
 *
 * ```php
 * $cache['foo'] = 'some data';
 * echo $cache['foo'];
 * ```
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview)
 * and [PSR-16 specification](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md).
 *
 * @see \Psr\SimpleCache\CacheInterface
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.13. Previous framework versions used abstract class [[\yii\caching\Cache]] as interface.
 */
interface CacheInterface extends \Psr\SimpleCache\CacheInterface, \ArrayAccess
{
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
    public function set($key, $value, $ttl = null, $dependency = null);

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
     */
    public function setMultiple($items, $ttl = null, $dependency = null);

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
    public function add($key, $value, $ttl = 0, $dependency = null);

    /**
     * Stores multiple items in cache. Each item contains a value identified by a key.
     * If the cache already contains such a key, the existing value and expiration time will be preserved.
     *
     * @param array $values the items to be cached, as key-value pairs.
     * @param null|int|\DateInterval $ttl the TTL value of this item. If not set, default value is used.
     * @param Dependency $dependency dependency of the cached items. If the dependency changes,
     * the corresponding values in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return array array of failed keys
     */
    public function addMultiple($values, $ttl = 0, $dependency = null);

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
     */
    public function getOrSet($key, $callable, $ttl = null, $dependency = null);
}

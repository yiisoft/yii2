<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;

/**
 * ApcCache provides APC caching in terms of an application component.
 *
 * To use this application component, the [APC PHP extension](http://www.php.net/apc) must be loaded.
 * Alternatively [APCu PHP extension](http://www.php.net/apcu) could be used via setting `useApcu` to `true`.
 * In order to enable APC or APCu for CLI you should add "apc.enable_cli = 1" to your php.ini.
 *
 * See [[Cache]] for common cache operations that ApcCache supports.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ApcCache extends Cache
{
    /**
     * @var bool whether to use apcu or apc as the underlying caching extension.
     * If true, [apcu](http://pecl.php.net/package/apcu) will be used.
     * If false, [apc](http://pecl.php.net/package/apc) will be used.
     * Defaults to false.
     * @since 2.0.7
     */
    public $useApcu = false;


    /**
     * Initializes this application component.
     * It checks if extension required is loaded.
     */
    public function init()
    {
        parent::init();
        $extension = $this->useApcu ? 'apcu' : 'apc';
        if (!extension_loaded($extension)) {
            throw new InvalidConfigException("ApcCache requires PHP $extension extension to be loaded.");
        }
    }

    /**
     * Checks whether a specified key exists in the cache.
     * This can be faster than getting the value from the cache if the data is big.
     * Note that this method does not check whether the dependency associated
     * with the cached data, if there is any, has changed. So a call to [[get]]
     * may return false while exists returns true.
     * @param mixed $key a key identifying the cached value. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @return bool true if a value exists in cache, false if the value is not in the cache or expired.
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);

        return $this->useApcu ? apcu_exists($key) : apc_exists($key);
    }

    /**
     * Retrieves a value from cache with a specified key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key a unique key identifying the cached value
     * @return mixed|false the value stored in cache, false if the value is not in the cache or expired.
     */
    protected function getValue($key)
    {
        return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    protected function getValues($keys)
    {
        $values = $this->useApcu ? apcu_fetch($keys) : apc_fetch($keys);
        return is_array($values) ? $values : [];
    }

    /**
     * Stores a value identified by a key in cache.
     * This is the implementation of the method declared in the parent class.
     *
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached. Most often it's a string. If you have disabled [[serializer]],
     * it could be something else.
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise.
     */
    protected function setValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_store($key, $value, $duration) : apc_store($key, $value, $duration);
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value
     * @param int $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array array of failed keys
     */
    protected function setValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_store($data, null, $duration) : apc_store($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached. Most often it's a string. If you have disabled [[serializer]],
     * it could be something else.
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    protected function addValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_add($key, $value, $duration) : apc_add($key, $value, $duration);
    }

    /**
     * Adds multiple key-value pairs to cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param int $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array array of failed keys
     */
    protected function addValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_add($data, null, $duration) : apc_add($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * Deletes a value with the specified key from cache
     * This is the implementation of the method declared in the parent class.
     * @param string $key the key of the value to be deleted
     * @return bool if no error happens during deletion
     */
    protected function deleteValue($key)
    {
        return $this->useApcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * Deletes all values from cache.
     * This is the implementation of the method declared in the parent class.
     * @return bool whether the flush operation was successful.
     */
    protected function flushValues()
    {
        return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}

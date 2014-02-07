<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * WinCache provides Windows Cache caching in terms of an application component.
 *
 * To use this application component, the [WinCache PHP extension](http://www.iis.net/expand/wincacheforphp)
 * must be loaded. Also note that "wincache.ucenabled" should be set to "On" in your php.ini file.
 *
 * See [[Cache]] manual for common cache operations that are supported by WinCache.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WinCache extends Cache
{
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
		$key = $this->buildKey($key);
		return wincache_ucache_exists($key);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return wincache_ucache_get($key);
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		return wincache_ucache_get($keys);
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
		return wincache_ucache_set($key, $value, $expire);
	}

	/**
	 * Stores multiple key-value pairs in cache.
	 * @param array $data array where key corresponds to cache key while value is the value stored
	 * @param integer $expire the number of seconds in which the cached values will expire. 0 means never expire.
	 * @return array array of failed keys
	 */
	protected function setValues($data, $expire)
	{
		return wincache_ucache_set($data, null, $expire);
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
		return wincache_ucache_add($key, $value, $expire);
	}

	/**
	 * Adds multiple key-value pairs to cache.
	 * The default implementation calls [[addValue()]] multiple times add values one by one. If the underlying cache
	 * storage supports multiadd, this method should be overridden to exploit that feature.
	 * @param array $data array where key corresponds to cache key while value is the value stored
	 * @param integer $expire the number of seconds in which the cached values will expire. 0 means never expire.
	 * @return array array of failed keys
	 */
	protected function addValues($data, $expire)
	{
		return wincache_ucache_add($data, null, $expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return wincache_ucache_delete($key);
	}

	/**
	 * Deletes all values from cache.
	 * This is the implementation of the method declared in the parent class.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		return wincache_ucache_clear();
	}
}

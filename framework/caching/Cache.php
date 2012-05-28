<?php
/**
 * Cache class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\ApplicationComponent;
use yii\base\Exception;

/**
 * Cache is the base class for cache classes with different cache storage implementation.
 *
 * A data item can be stored in cache by calling {@link set} and be retrieved back
 * later by {@link get}. In both operations, a key identifying the data item is required.
 * An expiration time and/or a dependency can also be specified when calling {@link set}.
 * If the data item expires or the dependency changes, calling {@link get} will not
 * return back the data item.
 *
 * Note, by definition, cache does not ensure the existence of a value
 * even if it does not expire. Cache is not meant to be a persistent storage.
 *
 * Cache implements the interface {@link ICache} with the following methods:
 * <ul>
 * <li>{@link get} : retrieve the value with a key (if any) from cache</li>
 * <li>{@link set} : store the value with a key into cache</li>
 * <li>{@link add} : store the value only if cache does not have this key</li>
 * <li>{@link delete} : delete the value with the specified key from cache</li>
 * <li>{@link flush} : delete all values from cache</li>
 * </ul>
 *
 * Child classes must implement the following methods:
 * <ul>
 * <li>{@link getValue}</li>
 * <li>{@link setValue}</li>
 * <li>{@link addValue}</li>
 * <li>{@link deleteValue}</li>
 * <li>{@link flushValues} (optional)</li>
 * <li>{@link serializeValue} (optional)</li>
 * <li>{@link unserializeValue} (optional)</li>
 * </ul>
 *
 * Cache also implements ArrayAccess so that it can be used like an array.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Cache extends ApplicationComponent implements \ArrayAccess
{
	/**
	 * @var string a string prefixed to every cache key so that it is unique. Defaults to null which means
	 * to use the {@link CApplication::getId() application ID}. If different applications need to access the same
	 * pool of cached data, the same prefix should be set for each of the applications explicitly.
	 */
	public $keyPrefix;
	/**
	 * @var boolean whether to hash the cache key for normalization purpose. Defaults to true.
	 * Setting this property to false makes sure the cache
	 * key will not be tampered when calling the relevant methods {@link get()}, {@link set()}, {@link add()} and {@link delete()}. This is useful if a Yii
	 * application as well as an external application need to access the same cache pool (also see description of {@link keyPrefix} regarding this use case).
	 * However, without normalization you should make sure the affected cache backend does support the structure (charset, length, etc.) of all the provided
	 * cache keys, otherwise there might be unexpected behavior.
	 **/
	public $hashKey = true;
	/**
	 * @var array|boolean the functions used to serialize and unserialize cached data. Defaults to null, meaning
	 * using the default PHP `serialize()` and `unserialize()` functions. If you want to use some more efficient
	 * serializer (e.g. [igbinary](http://pecl.php.net/package/igbinary)), you may configure this property with
	 * a two-element array. The first element specifies the serialization function, and the second the deserialization
	 * function. If this property is set false, data will be directly sent to and retrieved from the underlying
	 * cache component without any serialization or deserialization. You should not turn off serialization if
	 * you are using [[CacheDependency|cache dependency]], because it relies on data serialization.
	 */
	public $serializer;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by setting default cache key prefix.
	 */
	public function init()
	{
		parent::init();
		if ($this->keyPrefix === null) {
			$this->keyPrefix = \Yii::$application->id;
		}
	}

	/**
	 * @param string $key a key identifying a value to be cached
	 * @return string a key generated from the provided key which ensures the uniqueness across applications
	 */
	protected function generateCacheKey($key)
	{
		return $this->hashKey ? md5($this->keyPrefix . $key) : $this->keyPrefix . $key;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache, expired,
	 * or the dependency associated with the cached data has changed.
	 */
	public function get($id)
	{
		$value = $this->getValue($this->generateCacheKey($id));
		if ($value === false || $this->serializer === false) {
			return $value;
		} elseif ($this->serializer === null) {
			$value = unserialize($value);
		} else {
			$value = call_user_func($this->serializer[1], $value);
		}
		if (is_array($value) && ($value[1] instanceof CacheDependency) || !$value[1]->getHasChanged()) {
			return $value[0];
		} else {
			return false;
		}
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * Some caches (such as memcache, apc) allow retrieving multiple cached values at one time,
	 * which may improve the performance since it reduces the communication cost.
	 * In case a cache doesn't support this feature natively, it will be simulated by this method.
	 * @param array $ids list of keys identifying the cached values
	 * @return array list of cached values corresponding to the specified keys. The array
	 * is returned in terms of (key,value) pairs.
	 * If a value is not cached or expired, the corresponding array value will be false.
	 */
	public function mget(array $ids)
	{
		$uids = array();
		foreach ($ids as $id) {
			$uids[$id] = $this->generateCacheKey($id);
		}
		$values = $this->getValues($uids);
		$results = array();
		if ($this->serializer === false) {
			foreach ($uids as $id => $uid) {
				$results[$id] = isset($values[$uid]) ? $values[$uid] : false;
			}
		} else {
			foreach ($uids as $id => $uid) {
				$results[$id] = false;
				if (isset($values[$uid])) {
					$value = $this->serializer === null ? unserialize($values[$uid]) : call_user_func($this->serializer[1], $values[$uid]);
					if (is_array($value) && (!($value[1] instanceof CacheDependency) || !$value[1]->getHasChanged())) {
						$results[$id] = $value[0];
					}
				}
			}
		}
		return $results;
	}

	/**
	 * Stores a value identified by a key into cache.
	 * If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param CacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($id, $value, $expire = 0, $dependency = null)
	{
		if ($dependency !== null && $this->serializer !== false) {
			$dependency->evaluateDependency();
		}
		if ($this->serializer === null) {
			$value = array(serialize($value), $dependency);
		} elseif ($this->serializer !== false) {
			$value = array(call_user_func($this->serializer[0], $value), $dependency);
		}
		return $this->setValue($this->generateCacheKey($id), $value, $expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param CacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($id, $value, $expire = 0, $dependency = null)
	{
		if ($dependency !== null && $this->serializer !== false) {
			$dependency->evaluateDependency();
		}
		if ($this->serializer === null) {
			$value = array(serialize($value), $dependency);
		} elseif ($this->serializer !== false) {
			$value = array(call_user_func($this->serializer[0], $value), $dependency);
		}
		return $this->addValue($this->generateCacheKey($id), $value, $expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * @param string $id the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	public function delete($id)
	{
		return $this->deleteValue($this->generateCacheKey($id));
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 * @return boolean whether the flush operation was successful.
	 */
	public function flush()
	{
		return $this->flushValues();
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This method should be implemented by child classes to retrieve the data
	 * from specific cache storage. The uniqueness and dependency are handled
	 * in {@link get()} already. So only the implementation of data retrieval
	 * is needed.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
	 */
	protected function getValue($key)
	{
		return false;
	}

	/**
	 * Retrieves multiple values from cache with the specified keys.
	 * The default implementation simply calls {@link getValue} multiple
	 * times to retrieve the cached values one by one.
	 * If the underlying cache storage supports multiget, this method should
	 * be overridden to exploit that feature.
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		$results = array();
		foreach ($keys as $key) {
			$results[$key] = $this->getValue($key);
		}
		return $results;
	}

	/**
	 * Stores a value identified by a key in cache.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage. The uniqueness and dependency are handled
	 * in {@link set()} already. So only the implementation of data storage
	 * is needed.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function setValue($key, $value, $expire)
	{
		return true;
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * This method should be implemented by child classes to store the data
	 * in specific cache storage. The uniqueness and dependency are handled
	 * in {@link add()} already. So only the implementation of data storage
	 * is needed.
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	protected function addValue($key, $value, $expire)
	{
		return true;
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This method should be implemented by child classes to delete the data from actual cache storage.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	protected function deleteValue($key)
	{
		return true;
	}

	/**
	 * Deletes all values from cache.
	 * Child classes may implement this method to realize the flush operation.
	 * @return boolean whether the flush operation was successful.
	 */
	protected function flushValues()
	{
		return true;
	}

	/**
	 * Returns whether there is a cache entry with a specified key.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id a key identifying the cached value
	 * @return boolean
	 */
	public function offsetExists($id)
	{
		return $this->get($id) !== false;
	}

	/**
	 * Retrieves the value from cache with a specified key.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function offsetGet($id)
	{
		return $this->get($id);
	}

	/**
	 * Stores the value identified by a key into cache.
	 * If the cache already contains such a key, the existing value will be
	 * replaced with the new ones. To add expiration and dependencies, use the set() method.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 */
	public function offsetSet($id, $value)
	{
		$this->set($id, $value);
	}

	/**
	 * Deletes the value with the specified key from cache
	 * This method is required by the interface ArrayAccess.
	 * @param string $id the key of the value to be deleted
	 */
	public function offsetUnset($id)
	{
		$this->delete($id);
	}
}
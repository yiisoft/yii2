<?php
/**
 * CCache class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCache is the base class for cache classes with different cache storage implementation.
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
 * CCache implements the interface {@link ICache} with the following methods:
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
 * CCache also implements ArrayAccess so that it can be used like an array.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.caching
 * @since 1.0
 */
abstract class CCache extends CApplicationComponent implements ICache, ArrayAccess
{
	/**
	 * @var string a string prefixed to every cache key so that it is unique. Defaults to null which means
	 * to use the {@link CApplication::getId() application ID}. If different applications need to access the same
	 * pool of cached data, the same prefix should be set for each of the applications explicitly.
	 */
	public $keyPrefix;

	/**
	 * @var boolean whether to md5-hash the cache key for normalization purposes. Defaults to true. Setting this property to false makes sure the cache
	 * key will not be tampered when calling the relevant methods {@link get()}, {@link set()}, {@link add()} and {@link delete()}. This is useful if a Yii
	 * application as well as an external application need to access the same cache pool (also see description of {@link keyPrefix} regarding this use case).
	 * However, without normalization you should make sure the affected cache backend does support the structure (charset, length, etc.) of all the provided
	 * cache keys, otherwise there might be unexpected behavior.
	 * @since 1.1.11
	 **/
	public $hashKey=true;
	
	/**
	 * @var boolean whether to automatically serialize/unserialize the cache values. Defaults to true. Setting this property to false makes sure the cache
	 * value will not be tampered when calling the methods {@link set()} and {@link add()}. This is useful in case you want to store data which simply
	 * does not require serialization (e.g. integers, strings or raw binary data). Thus there might be a small increase in performance and a smaller overall
	 * cache size. Take in mind that you will be unable to store PHP structures like arrays or objects, you would have to serialize and unserialize them manually.
	 * Another negative side effect is that providing a dependency via {@link set()} or {@link add()} will have no effect since dependencies rely on serialization.
	 * Since all the relevant core application components rely on dependency support, you should be very careful disabling this feature. Usually you want to
	 * configure a dedicated cache component for the sole purpose of storing raw unserialized data, the main cache component should always support serialization.
	 * @since 1.1.11
	 **/
	public $autoSerialize=true;

	/**
	 * @var boolean wether to make use of the {@link http://pecl.php.net/package/igbinary igbinary} serializer for cache entry serialization. Defaults to false.
	 * <strong>NOTE:</strong> If this is set to true while the igbinary extension has not been loaded, cache serialization will silently fall back to PHP's default
	 * serializer. Since the two serialization formats are incompatible, caches should be purged before switching this on to prevent errors.
	 * @since 1.1.11
	 */
	public $useIgbinarySerializer=false;
	
	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by setting default cache key prefix.
	 */
	public function init()
	{
		parent::init();
		if($this->keyPrefix===null)
			$this->keyPrefix=Yii::app()->getId();
		$this->useIgbinarySerializer=$this->useIgbinarySerializer&&extension_loaded('igbinary');
	}

	/**
	 * @param string $key a key identifying a value to be cached
	 * @return sring a key generated from the provided key which ensures the uniqueness across applications
	 */
	protected function generateUniqueKey($key)
	{
		return $this->hashKey ? md5($this->keyPrefix.$key) : $this->keyPrefix.$key;
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * @param string $id a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache, expired or the dependency has changed.
	 */
	public function get($id)
	{
		if(($value=$this->getValue($this->generateUniqueKey($id)))!==false)
		{
			$data=$this->autoSerialize ? $this->unserializeValue($value) : $value;
			if(!$this->autoSerialize || (is_array($data) && (!($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged())))
			{
				Yii::trace('Serving "'.$id.'" from cache','system.caching.'.get_class($this));
				return $this->autoSerialize ? $data[0] : $data;
			}
		}
		return false;
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
	public function mget($ids)
	{
		$uniqueIDs=array();
		$results=array();
		foreach($ids as $id)
		{
			$uniqueIDs[$id]=$this->generateUniqueKey($id);
			$results[$id]=false;
		}
		$values=$this->getValues($uniqueIDs);
		foreach($uniqueIDs as $id=>$uniqueID)
		{
			if(!isset($values[$uniqueID]))
				continue;
			$data=$this->autoSerialize ? $this->unserializeValue($values[$uniqueID]) : $values[$uniqueID];
			if(!$this->autoSerialize || (is_array($data) && (!($data[1] instanceof ICacheDependency) || !$data[1]->getHasChanged())))
			{
				Yii::trace('Serving "'.$id.'" from cache','system.caching.'.get_class($this));
				$results[$id]=$this->autoSerialize ? $data[0] : $data;
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
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function set($id,$value,$expire=0,$dependency=null)
	{
		Yii::trace('Saving "'.$id.'" to cache','system.caching.'.get_class($this));
		if($dependency!==null && $this->autoSerialize)
			$dependency->evaluateDependency();
		$data=$this->autoSerialize ? $this->serializeValue(array($value,$dependency)) : $value;
		return $this->setValue($this->generateUniqueKey($id),$data,$expire);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain this key.
	 * Nothing will be done if the cache already contains the key.
	 * @param string $id the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 * @param integer $expire the number of seconds in which the cached value will expire. 0 means never expire.
	 * @param ICacheDependency $dependency dependency of the cached item. If the dependency changes, the item is labeled invalid.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	public function add($id,$value,$expire=0,$dependency=null)
	{
		Yii::trace('Adding "'.$id.'" to cache','system.caching.'.get_class($this));
		if($dependency!==null && $this->autoSerialize)
			$dependency->evaluateDependency();
		$data=$this->autoSerialize ? $this->serializeValue(array($value,$dependency)) : $value;
		return $this->addValue($this->generateUniqueKey($id),$data,$expire);
	}

	/**
	 * Deletes a value with the specified key from cache
	 * @param string $id the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	public function delete($id)
	{
		Yii::trace('Deleting "'.$id.'" from cache','system.caching.'.get_class($this));
		return $this->deleteValue($this->generateUniqueKey($id));
	}

	/**
	 * Deletes all values from cache.
	 * Be careful of performing this operation if the cache is shared by multiple applications.
	 * @return boolean whether the flush operation was successful.
	 */
	public function flush()
	{
		Yii::trace('Flushing cache','system.caching.'.get_class($this));
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
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function getValue($key)
	{
		throw new CException(Yii::t('yii','{className} does not support get() functionality.',
			array('{className}'=>get_class($this))));
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
		$results=array();
		foreach($keys as $key)
			$results[$key]=$this->getValue($key);
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
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function setValue($key,$value,$expire)
	{
		throw new CException(Yii::t('yii','{className} does not support set() functionality.',
			array('{className}'=>get_class($this))));
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
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function addValue($key,$value,$expire)
	{
		throw new CException(Yii::t('yii','{className} does not support add() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Deletes a value with the specified key from cache
	 * This method should be implemented by child classes to delete the data from actual cache storage.
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 * @throws CException if this method is not overridden by child classes
	 */
	protected function deleteValue($key)
	{
		throw new CException(Yii::t('yii','{className} does not support delete() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Deletes all values from cache.
	 * Child classes may implement this method to realize the flush operation.
	 * @return boolean whether the flush operation was successful.
	 * @throws CException if this method is not overridden by child classes
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		throw new CException(Yii::t('yii','{className} does not support flushValues() functionality.',
			array('{className}'=>get_class($this))));
	}

	/**
	 * Serializes the value before it will be stored in the actual cache backend.
	 * This method will be called if {@link autoSerialize} is set to true. Child classes may override this method to change
	 * the way the value is being serialized. The default implementation simply makes use of the PHP serialize() function
	 * unless {@link useIgbinarySerializer} is set to true and the igbinary extension is installed.
	 * Make sure to override {@link unserializeValue()} as well if you want to change the serialization process.
	 * @param mixed $value the unserialized representation of the value
	 * @return string the serialized representation of the value
	 * @since 1.1.11
	 **/
	protected function serializeValue($value)
	{
		if($this->useIgbinarySerializer)
			return igbinary_serialize($value);
		return serialize($value);
	}
	
	/**
	 * Unserializes the value after it was retrieved from the actual cache backend.
	 * This method will be called if {@link autoSerialize} is set to true. Child classes may override this method to change
	 * the way the value is being unserialized.  The default implementation simply makes use of the PHP unserialize() function
	 * unless {@link useIgbinarySerializer} is set to true and the igbinary extension is installed.
	 * Make sure to override {@link serializeValue()} as well if you want to change the serialization process.
	 * @param string $value the serialized representation of the value
	 * @return mixed the unserialized representation of the value
	 * @since 1.1.11
	 **/
	protected function unserializeValue($value)
	{
		if($this->useIgbinarySerializer)
			return igbinary_unserialize($value);
		return unserialize($value);
	}

	/**
	 * Returns whether there is a cache entry with a specified key.
	 * This method is required by the interface ArrayAccess.
	 * @param string $id a key identifying the cached value
	 * @return boolean
	 */
	public function offsetExists($id)
	{
		return $this->get($id)!==false;
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
	 * @return boolean if no error happens during deletion
	 */
	public function offsetUnset($id)
	{
		$this->delete($id);
	}
}
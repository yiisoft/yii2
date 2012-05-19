<?php
/**
 * CWinCache class file
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWinCache implements a cache application component based on {@link http://www.iis.net/expand/wincacheforphp WinCache}.
 *
 * To use this application component, the WinCache PHP extension must be loaded.
 *
 * See {@link CCache} manual for common cache operations that are supported by CWinCache.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @version $Id$
 * @package system.caching
 * @since 1.1.2
 */
class CWinCache extends CCache {
	/**
	 * Initializes this application component.
	 * This method is required by the {@link IApplicationComponent} interface.
	 * It checks the availability of WinCache extension and WinCache user cache.
	 * @throws CException if WinCache extension is not loaded or user cache is disabled
	 */
	public function init()
	{
		parent::init();
		if(!extension_loaded('wincache'))
			throw new CException(Yii::t('yii', 'CWinCache requires PHP wincache extension to be loaded.'));
		if(!ini_get('wincache.ucenabled'))
			throw new CException(Yii::t('yii', 'CWinCache user cache is disabled. Please set wincache.ucenabled to On in your php.ini.'));
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 * This is the implementation of the method declared in the parent class.
	 * @param string $key a unique key identifying the cached value
	 * @return string the value stored in cache, false if the value is not in the cache or expired.
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
	protected function setValue($key,$value,$expire)
	{
		return wincache_ucache_set($key,$value,$expire);
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
	protected function addValue($key,$value,$expire)
	{
		return wincache_ucache_add($key,$value,$expire);
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
	 * @since 1.1.5
	 */
	protected function flushValues()
	{
		return wincache_ucache_clear();
	}
}
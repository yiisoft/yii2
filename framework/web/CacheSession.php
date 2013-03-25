<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\caching\Cache;
use yii\base\InvalidConfigException;

/**
 * CacheSession implements a session component using cache as storage medium.
 *
 * The cache being used can be any cache application component.
 * The ID of the cache application component is specified via [[cacheID]], which defaults to 'cache'.
 *
 * Beware, by definition cache storage are volatile, which means the data stored on them
 * may be swapped out and get lost. Therefore, you must make sure the cache used by this component
 * is NOT volatile. If you want to use database as storage medium, use [[DbSession]] is a better choice.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CacheSession extends Session
{
	/**
	 * @var string the ID of the cache application component. Defaults to 'cache' (the primary cache application component.)
	 */
	public $cacheID = 'cache';

	/**
	 * @var Cache the cache component
	 */
	private $_cache;

	/**
	 * Returns a value indicating whether to use custom session storage.
	 * This method overrides the parent implementation and always returns true.
	 * @return boolean whether to use custom storage.
	 */
	public function getUseCustomStorage()
	{
		return true;
	}

	/**
	 * Returns the cache instance used for storing session data.
	 * @return Cache the cache instance
	 * @throws InvalidConfigException if [[cacheID]] does not point to a valid application component.
	 */
	public function getCache()
	{
		if ($this->_cache === null) {
			$cache = Yii::$app->getComponent($this->cacheID);
			if ($cache instanceof Cache) {
				$this->_cache = $cache;
			} else {
				throw new InvalidConfigException('CacheSession::cacheID must refer to the ID of a cache application component.');
			}
		}
		return $this->_cache;
	}

	/**
	 * Sets the cache instance used by the session component.
	 * @param Cache $value the cache instance
	 */
	public function setCache($value)
	{
		$this->_cache = $value;
	}

	/**
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$data = $this->getCache()->get($this->calculateKey($id));
		return $data === false ? '' : $data;
	}

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id, $data)
	{
		return $this->getCache()->set($this->calculateKey($id), $data, $this->getTimeout());
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		return $this->getCache()->delete($this->calculateKey($id));
	}

	/**
	 * Generates a unique key used for storing session data in cache.
	 * @param string $id session variable name
	 * @return string a safe cache key associated with the session variable name
	 */
	protected function calculateKey($id)
	{
		return $this->getCache()->buildKey(array(__CLASS__, $id));
	}
}

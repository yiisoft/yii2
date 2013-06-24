<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mutex\cache;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\caching\DbCache;
use yii\caching\MemCache;

/**
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class Mutex extends \yii\mutex\Mutex
{
	/**
	 * @var Cache|string the cache object or the application component ID of the cache object.
	 * The messages data will be cached using this cache object. Note, this property has meaning only
	 * in case [[cachingDuration]] set to non-zero value.
	 * After the Mutex object is created, if you want to change this property, you should only assign
	 * it with a cache object.
	 */
	public $cache = 'cache';


	/**
	 * Initializes the DbMessageSource component. Configured [[cache]] component will be initialized.
	 * @throws InvalidConfigException if [[cache]] is invalid.
	 */
	public function init()
	{
		parent::init();
		if (is_string($this->cache)) {
			$this->cache = Yii::$app->getComponent($this->cache);
		}
		if (!$this->cache instanceof Cache) {
			throw new InvalidConfigException('Mutex::cache must be either a cache object or the application component ID of the cache object.');
		}
	}

	/**
	 * This method should be extended by concrete mutex implementations. Acquires lock by given name.
	 * @param string $name of the lock to be acquired.
	 * @param integer $timeout to wait for lock to become released.
	 * @return boolean acquiring result.
	 */
	protected function acquire($name, $timeout = 0)
	{

	}

	/**
	 * This method should be extended by concrete mutex implementations. Releases lock by given name.
	 * @param string $name of the lock to be released.
	 * @return boolean release result.
	 */
	protected function release($name)
	{
		return $this->cache->delete("mutex.{$name}");
	}

	/**
	 * This method may optionally be extended by concrete mutex implementations. Checks whether lock has been
	 * already acquired by given name.
	 * @param string $name of the lock to be released.
	 * @return null|boolean whether lock has been already acquired. Returns `null` in case this feature
	 * is not supported by concrete mutex implementation.
	 */
	protected function getIsAcquired($name)
	{
		
	}

	/**
	 * This method should be extended by concrete mutex implementations. Returns whether current mutex
	 * implementation can be used in a distributed environment.
	 * @return boolean whether current mutex implementation can be used in a distributed environment.
	 */
	public function getIsDistributed()
	{
		return $this->cache instanceof DbCache || $this->cache instanceof MemCache;
	}
}

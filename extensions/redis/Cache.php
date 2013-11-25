<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

/**
 * Redis Cache implements a cache application component based on [redis](http://redis.io/) key-value store.
 *
 * Redis Cache requires redis version 2.6.12 or higher to work properly.
 *
 * It needs to be configured with a redis [[Connection]] that is also configured as an application component.
 * By default it will use the `redis` application component.
 *
 * See [[Cache]] manual for common cache operations that redis Cache supports.
 *
 * Unlike the [[Cache]], redis Cache allows the expire parameter of [[set]], [[add]], [[mset]] and [[madd]] to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * To use redis Cache as the cache application component, configure the application as follows,
 *
 * ~~~
 * [
 *     'components' => [
 *         'cache' => [
 *             'class' => 'yii\redis\Cache',
 *         ],
 *         'redis' => [
 *             'class' => 'yii\redis\Connection',
 *             'hostname' => 'localhost',
 *             'port' => 6379,
 *             'database' => 0,
 *         ]
 *     ],
 * ]
 * ~~~
 *
 * @property Connection $connection The redis connection object. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Cache extends \yii\caching\Cache
{
	/**
	 * @var string the id of the application component to use as the redis connection.
	 * It should be configured as a [[yii\redis\Connection]]. Defaults to `redis`.
	 */
	public $connectionId = 'redis';


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
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		return (bool) $connection->executeCommand('EXISTS', [$this->buildKey($key)]);
	}

	/**
	 * @inheritDocs
	 */
	protected function getValue($key)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		return $connection->executeCommand('GET', [$key]);
	}

	/**
	 * @inheritDocs
	 */
	protected function getValues($keys)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		$response = $connection->executeCommand('MGET', $keys);
		$result = [];
		$i = 0;
		foreach ($keys as $key) {
			$result[$key] = $response[$i++];
		}
		return $result;
	}

	/**
	 * @inheritDocs
	 */
	protected function setValue($key, $value, $expire)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		if ($expire == 0) {
			return (bool) $connection->executeCommand('SET', [$key, $value]);
		} else {
			$expire = (int) ($expire * 1000);
			return (bool) $connection->executeCommand('SET', [$key, $value, 'PX', $expire]);
		}
	}

	/**
	 * @inheritDocs
	 */
	protected function setValues($data, $expire)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);

		$args = [];
		foreach($data as $key => $value) {
			$args[] = $key;
			$args[] = $value;
		}

		$failedKeys = [];
		if ($expire == 0) {
			$connection->executeCommand('MSET', $args);
		} else {
			$expire = (int) ($expire * 1000);
			$connection->executeCommand('MULTI');
			$connection->executeCommand('MSET', $args);
			$index = [];
			foreach ($data as $key => $value) {
				$connection->executeCommand('PEXPIRE', [$key, $expire]);
				$index[] = $key;
			}
			$result = $connection->executeCommand('EXEC');
			array_shift($result);
			foreach($result as $i => $r) {
				if ($r != 1) {
					$failedKeys[] = $index[$i];
				}
			}
		}
		return $failedKeys;
	}

	/**
	 * @inheritDocs
	 */
	protected function addValue($key, $value, $expire)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		if ($expire == 0) {
			return (bool) $connection->executeCommand('SET', [$key, $value, 'NX']);
		} else {
			$expire = (int) ($expire * 1000);
			return (bool) $connection->executeCommand('SET', [$key, $value, 'PX', $expire, 'NX']);
		}
	}

	/**
	 * @inheritDocs
	 */
	protected function deleteValue($key)
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		return (bool) $connection->executeCommand('DEL', [$key]);
	}

	/**
	 * @inheritDocs
	 */
	protected function flushValues()
	{
		/** @var Connection $connection */
		$connection = \Yii::$app->getComponent($this->connectionId);
		return $connection->executeCommand('FLUSHDB');
	}
}

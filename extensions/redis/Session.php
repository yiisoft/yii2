<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\redis;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Redis Session implements a session component using [redis](http://redis.io/) as the storage medium.
 *
 * Redis Session requires redis version 2.6.12 or higher to work properly.
 *
 * It needs to be configured with a redis [[Connection]] that is also configured as an application component.
 * By default it will use the `redis` application component.
 *
 * To use redis Session as the session application component, configure the application as follows,
 *
 * ~~~
 * [
 *     'components' => [
 *         'session' => [
 *             'class' => 'yii\redis\Session',
 *             'redis' => [
 *                 'hostname' => 'localhost',
 *                 'port' => 6379,
 *                 'database' => 0,
 *             ]
 *         ],
 *     ],
 * ]
 * ~~~
 *
 * Or if you have configured the redis [[Connection]] as an application component, the following is sufficient:
 *
 * ~~~
 * [
 *     'components' => [
 *         'session' => [
 *             'class' => 'yii\redis\Session',
 *             // 'redis' => 'redis' // id of the connection application component
 *         ],
 *     ],
 * ]
 * ~~~
 *
 * @property boolean $useCustomStorage Whether to use custom storage. This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Session extends \yii\web\Session
{
	/**
	 * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
	 * This can also be an array that is used to create a redis [[Connection]] instance in case you do not want do configure
	 * redis connection as an application component.
	 * After the Session object is created, if you want to change this property, you should only assign it
	 * with a Redis [[Connection]] object.
	 */
	public $redis = 'redis';
	/**
	 * @var string a string prefixed to every cache key so that it is unique. If not set,
	 * it will use a prefix generated from [[Application::id]]. You may set this property to be an empty string
	 * if you don't want to use key prefix. It is recommended that you explicitly set this property to some
	 * static value if the cached data needs to be shared among multiple applications.
	 *
	 * To ensure interoperability, only alphanumeric characters should be used.
	 */
	public $keyPrefix;


	/**
	 * Initializes the redis Session component.
	 * This method will initialize the [[redis]] property to make sure it refers to a valid redis connection.
	 * @throws InvalidConfigException if [[redis]] is invalid.
	 */
	public function init()
	{
		if (is_string($this->redis)) {
			$this->redis = Yii::$app->getComponent($this->redis);
		} else if (is_array($this->redis)) {
			if (!isset($this->redis['class'])) {
				$this->redis['class'] = Connection::className();
			}
			$this->redis = Yii::createObject($this->redis);
		}
		if (!$this->redis instanceof Connection) {
			throw new InvalidConfigException("Session::redis must be either a Redis connection instance or the application component ID of a Redis connection.");
		}
		if ($this->keyPrefix === null) {
			$this->keyPrefix = substr(md5(Yii::$app->id), 0, 5);
		} elseif (!ctype_alnum($this->keyPrefix)) {
			throw new InvalidConfigException(get_class($this) . '::keyPrefix should only contain alphanumeric characters.');
		}
		parent::init();
	}

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
	 * Session read handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return string the session data
	 */
	public function readSession($id)
	{
		$data = $this->redis->executeCommand('GET', [$this->calculateKey($id)]);
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
		return (bool) $this->redis->executeCommand('SET', [$this->calculateKey($id), $data, 'EX', $this->getTimeout()]);
	}

	/**
	 * Session destroy handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @return boolean whether session is destroyed successfully
	 */
	public function destroySession($id)
	{
		return (bool) $this->redis->executeCommand('DEL', [$this->calculateKey($id)]);
	}

	/**
	 * Generates a unique key used for storing session data in cache.
	 * @param string $id session variable name
	 * @return string a safe cache key associated with the session variable name
	 */
	protected function calculateKey($id)
	{
		return $this->keyPrefix . md5(json_encode([__CLASS__, $id]));
	}
}

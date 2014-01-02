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
 * The ID of the cache application component is specified via [[cache]], which defaults to 'cache'.
 *
 * Beware, by definition cache storage are volatile, which means the data stored on them
 * may be swapped out and get lost. Therefore, you must make sure the cache used by this component
 * is NOT volatile. If you want to use database as storage medium, [[DbSession]] is a better choice.
 *
 * The following example shows how you can configure the application to use CacheSession:
 * Add the following to your application config under `components`:
 *
 * ~~~
 * 'session' => [
 *     'class' => 'yii\web\CacheSession',
 *     // 'cache' => 'mycache',
 * ]
 * ~~~
 *
 * @property boolean $useCustomStorage Whether to use custom storage. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CacheSession extends Session
{
	/**
	 * @var string|SessionHandlerInterface the name of class or an object implementing the session handler
	 */
	public $handler = 'CacheSessionHandler';
	/**
	 * @var Cache|string the cache object or the application component ID of the cache object.
	 * The session data will be stored using this cache object.
	 *
	 * After the CacheSession object is created, if you want to change this property,
	 * you should only assign it with a cache object.
	 */
	public $cache = 'cache';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		if (is_string($this->cache)) {
			$this->cache = Yii::$app->getComponent($this->cache);
		}
		if (!$this->cache instanceof Cache) {
			throw new InvalidConfigException('CacheSession::cache must refer to the application component ID of a cache object.');
		}
		parent::init();
		if ($this->handler instanceof CacheSessionHandler) {
			$this->handler->owner = $this;
		}
	}
}

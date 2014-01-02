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
 * CacheSessionHandler implements SessionHandlerInterface and provides a cache session data storage.
 *
 * CacheSessionHandler is used by CacheSession.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CacheSessionHandler extends SessionHandler
{
	/**
	 * @inheritdoc
	 */
	public function read($id)
	{
		$data = $this->owner->cache->get($this->calculateKey($id));
		return $data === false ? '' : $data;
	}

	/**
	 * @inheritdoc
	 */
	public function write($id, $data)
	{
		return $this->owner->cache->set($this->calculateKey($id), $data, $this->owner->getTimeout());
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($id)
	{
		return $this->owner->cache->delete($this->calculateKey($id));
	}

	/**
	 * Generates a unique key used for storing session data in cache.
	 * @param string $id session variable name
	 * @return mixed a safe cache key associated with the session variable name
	 */
	protected function calculateKey($id)
	{
		return [__CLASS__, $id];
	}
}

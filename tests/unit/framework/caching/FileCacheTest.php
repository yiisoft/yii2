<?php
namespace yiiunit\framework\caching;
use yii\caching\FileCache;
use yiiunit\TestCase;

/**
 * Class for testing file cache backend
 */
class FileCacheTest extends CacheTest
{
	private $_cacheInstance = null;

	/**
	 * @return FileCache
	 */
	protected function getCacheInstance()
	{
		if($this->_cacheInstance === null) {
			$this->_cacheInstance = new FileCache(array(
				'cachePath' => '@yiiunit/runtime/cache',
			));
		}
		return $this->_cacheInstance;
	}
}

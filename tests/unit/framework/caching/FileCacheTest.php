<?php
namespace yiiunit\framework\caching;
use yii\caching\FileCache;
use yiiunit\TestCase;
use yii\caching\TimeProvider;

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
		if ($this->_cacheInstance === null) {
			$this->_cacheInstance = new FileCache(array(
				'cachePath' => '@yiiunit/runtime/cache',
			));
		}
		return $this->_cacheInstance;
	}

    public function testExpire()
    {
        $cache = $this->getCacheInstance();
        $now = time();
        TimeProvider::setTime($now);
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        TimeProvider::setTime($now+1);
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        TimeProvider::setTime($now+3);
        $this->assertEquals(false, $cache->get('expire_test'));
        TimeProvider::setTime(null);
    }
}

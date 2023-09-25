<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ArrayCache provides caching for the current request only by storing the values in an array.
 *
 * See [[Cache]] for common cache operations that ArrayCache supports.
 *
 * Unlike the [[Cache]], ArrayCache allows the expire parameter of [[set]], [[add]], [[multiSet]] and [[multiAdd]] to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * For enhanced performance of ArrayCache, you can disable serialization of the stored data by setting [[$serializer]] to `false`.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ArrayCache extends Cache
{
    /**
     * @var \Yiisoft\Cache\ArrayCache $_cache 
     */
    private $_cache;

    function init()
    {
        $this->_cache = new \Yiisoft\Cache\ArrayCache();
    }


    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return $this->_cache->get($key, null) != null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        return $this->_cache->get($key, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $duration)
    {
        return $this->_cache->set($key, $value, $duration);
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $duration)
    {
        if ($this->_cache->get($key, null) != null) {
            return false;
        }

        $this->_cache->set($key, $value, $duration);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function flushValues()
    {
        return $this->_cache->clear();
    }
}

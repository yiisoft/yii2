<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ZendDataCache provides Zend data caching in terms of an application component.
 *
 * To use this application component, the [Zend Data Cache PHP extension](http://www.zend.com/en/products/server/)
 * must be loaded.
 *
 * See [[Cache]] for common cache operations that ZendDataCache supports.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ZendDataCache extends SimpleCache
{
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $result = zend_shm_cache_fetch($key);

        return $result === null ? false : $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $ttl)
    {
        return zend_shm_cache_store($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return zend_shm_cache_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return zend_shm_cache_clear();
    }
}

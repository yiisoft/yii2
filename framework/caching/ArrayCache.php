<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ArrayCache 通过把值存入数组来提供缓存，只对当前请求有效。
 *
 * 参考 [[Cache]] 查看 ArrayCache 支持的通用的缓存操作方法。
 *
 * 不像 [[Cache]] 那样，ArrayCache 允许 [[set]]，[[add]]，[[multiSet]] 和 [[multiAdd]] 方法的过期参数
 * 可以是浮点数，你可以以毫秒为单位指定过期时间（比如，0.1 表示 100 毫秒）。
 *
 * 为了增强 ArrayCache 的性能，你可以把 [[$serializer]] 设置为 `false` 来禁用缓存数据的序列化过程。
 *
 * 更多缓存的详情和使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ArrayCache extends Cache
{
    private $_cache = [];


    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);
        return isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true));
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return $this->_cache[$key][0];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $duration)
    {
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addValue($key, $value, $duration)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return false;
        }
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        unset($this->_cache[$key]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function flushValues()
    {
        $this->_cache = [];
        return true;
    }
}

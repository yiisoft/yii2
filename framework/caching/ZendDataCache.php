<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ZendDataCache 是使用 Zend 数据实现的缓存应用组件。
 *
 * 要使用这个组件，
 * 必须加载 [Zend Data Cache PHP extension](http://www.zend.com/en/products/server/)。
 *
 * 可以参考 [[Cache]] 查看 ZendDataCache 支持的通用的缓存操作方法。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @deprecated since 2.0.14. This class will be removed in 2.1.0.
 */
class ZendDataCache extends Cache
{
    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        $result = zend_shm_cache_fetch($key);

        return $result === null ? false : $result;
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串。 如果禁用了 [[serializer]],
     * 它也可以是其它数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        return zend_shm_cache_store($key, $value, $duration);
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串。 如果禁用了 [[serializer]],
     * 它也可以是其它数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        return zend_shm_cache_fetch($key) === null ? $this->setValue($key, $value, $duration) : false;
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return zend_shm_cache_delete($key);
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        return zend_shm_cache_clear();
    }
}

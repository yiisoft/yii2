<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * DummyCache 是缓存组件的替代符。
 *
 * DummyCache 并不缓存任何数据。它的出现使得开发人员
 * 总是能配置一个 'cache' 应用组件，在检测 `\Yii::$app->cache` 的存在性时能够通过。
 * 通过替换 DummyCache 为其它的缓存组件，
 * 你可以快速地从非缓存模式切换到缓存模式。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DummyCache extends Cache
{
    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        return false;
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        return true;
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        return true;
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return true;
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        return true;
    }
}

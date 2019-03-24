<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * XCache 是使用 XCache 扩展实现的缓存应用组件。
 *
 * 要使用该组件，必须加载 PHP 扩展 [XCache PHP extension](http://xcache.lighttpd.net/)。
 * 而且还要注意，如果需要 [[flush()]] 函数功能正常发挥作用，那么在 php.ini 中，
 * "xcache.admin.enable_auth" 必须设置为 "Off"。
 *
 * 可以参考 [[Cache]] 查看 XCache 支持的通用的缓存操作方法。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 * @deprecated since 2.0.14. This class will be removed in 2.1.0.
 */
class XCache extends Cache
{
    /**
     * 检测指定的键是否存在缓存中。
     * 如果缓存数据量大的话，这比从缓存中直接获取值稍快些。
     * 注意，如果缓存数据有缓存依赖，
     * 该方法不会检测缓存依赖是否发生变化。所以有可能调用 [[get]] 方法返回 false，
     * 而调用该方法返回 true。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果缓存值存在返回 true，如果缓存值不存在或者已经过期则返回 false。
     */
    public function exists($key)
    {
        $key = $this->buildKey($key);

        return xcache_isset($key);
    }

    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        return xcache_isset($key) ? xcache_get($key) : false;
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
        return xcache_set($key, $value, $duration);
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
        return !xcache_isset($key) ? $this->setValue($key, $value, $duration) : false;
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return xcache_unset($key);
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++) {
            if (xcache_clear_cache(XC_TYPE_VAR, $i) === false) {
                return false;
            }
        }

        return true;
    }
}

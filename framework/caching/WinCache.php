<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * WinCache 是使用 Windows Cache 实现的缓存组件。
 *
 * 要使用这个应用组件，必须加载 [WinCache PHP extension](http://www.iis.net/expand/wincacheforphp)。
 * 还要注意，在 php.ini 文件中把 "wincache.ucenabled" 设置为 "On"。
 *
 * 可以参考 [[Cache]] 查看 WinCache 支持的通用的缓存操作方法。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WinCache extends Cache
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

        return wincache_ucache_exists($key);
    }

    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return string|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        return wincache_ucache_get($key);
    }

    /**
     * 根据多个缓存的键一次从缓存中获取多个对应的缓存数据。
     * @param array $keys 指明多个缓存数据的字符串键列表。
     * @return array 由对应的键指定的缓存数据列表。
     */
    protected function getValues($keys)
    {
        return wincache_ucache_get($keys);
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
        return wincache_ucache_set($key, $value, $duration);
    }

    /**
     * 一次性存入多个 键-值 对到缓存中。
     * @param array $data 数组，数组的键对应缓存的键而值就是要缓存的值。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @return array 未能存入缓存数据的键列表。
     */
    protected function setValues($data, $duration)
    {
        return wincache_ucache_set($data, null, $duration);
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
        return wincache_ucache_add($key, $value, $duration);
    }

    /**
     * 一次性存入多个 键-值 对到缓存中。
     * 默认的实现就是通过循环调用 [[addValue()]] 方法添加缓存值。如果当前环境的缓存驱动
     * 支持 multi-add，该方法将会被覆盖而是尽量使用 multi-add 来发挥它的特性。
     * @param array $data 数组，数组的键对应缓存的键而值就是要缓存的值。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @return array 未能存入缓存数据的键列表。
     */
    protected function addValues($data, $duration)
    {
        return wincache_ucache_add($data, null, $duration);
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return wincache_ucache_delete($key);
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        return wincache_ucache_clear();
    }
}

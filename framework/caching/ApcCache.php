<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;

/**
 * ApcCache 用应用组件的方式提供 APC 缓存。
 *
 * 要使用这个应用组件，必须加载 [APC PHP extension](http://www.php.net/apc) 扩展。
 * 或者加载 [APCu PHP extension](http://www.php.net/apcu) 的话，设置 `useApcu` 为 `true` 也可以。
 * 如果要在 CLI 环境使用 APC 或 APCu 功能，应该把 "apc.enable_cli = 1" 添加到 php.ini。 
 *
 * 可以参考 [[Cache]] 查看 ApcCache 支持的通用的缓存操作方法。
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ApcCache extends Cache
{
    /**
     * @var bool 使用 apcu 或者 apc 作为最终确定的缓存扩展。
     * 如果是 true，表示使用 [apcu](http://pecl.php.net/package/apcu) 扩展。
     * 如果是 false，表示使用 [apc](http://pecl.php.net/package/apc) 扩展。
     * 默认是 false。
     * @since 2.0.7
     */
    public $useApcu = false;


    /**
     * 初始化应用组件。
     * 检测需要的扩展是否加载。
     */
    public function init()
    {
        parent::init();
        $extension = $this->useApcu ? 'apcu' : 'apc';
        if (!extension_loaded($extension)) {
            throw new InvalidConfigException("ApcCache requires PHP $extension extension to be loaded.");
        }
    }

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

        return $this->useApcu ? apcu_exists($key) : apc_exists($key);
    }

    /**
     * 根据指定的键从缓存中获取缓存数据。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    protected function getValue($key)
    {
        return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
    }

    /**
     * 根据多个缓存键从缓存中一次获取多个缓存数据。
     * @param array $keys 指明缓存数据的缓存键列表。
     * @return array 由缓存键组成下标的缓存数据列表。
     */
    protected function getValues($keys)
    {
        $values = $this->useApcu ? apcu_fetch($keys) : apc_fetch($keys);
        return is_array($values) ? $values : [];
    }

    /**
     * 根据指定的键把数据存入缓存中。
     * 该方法从父类中声明，在子类这里实现。
     *
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串。如果禁用 [[serializer]]，
     * 它也可以是其它的数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function setValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_store($key, $value, $duration) : apc_store($key, $value, $duration);
    }

    /**
     * 一次性存入多个键值对到缓存中。
     * @param array $data 数组的键就是缓存的键，值就是缓存的值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return array 那些缓存失败了的键组成的数组。
     */
    protected function setValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_store($data, null, $duration) : apc_store($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 该方法从父类中声明，在子类里实现。
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串。如果禁用 [[serializer]]，
     * 它也可以是其它的数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    protected function addValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_add($key, $value, $duration) : apc_add($key, $value, $duration);
    }

    /**
     * 一次性添加多个键值对到缓存中。
     * @param array $data 数组的键就是缓存的键，值就是缓存的值。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return array 那些缓存失败了的键组成的数组。
     */
    protected function addValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_add($data, null, $duration) : apc_add($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * 根据指定的键把数据从缓存中删除。
     * 该方法从父类中声明，在子类这里实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    protected function deleteValue($key)
    {
        return $this->useApcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * 从缓存中删除所有值。
     * 该方法从父类中声明，在子类这里实现。
     * @return bool 是否成功执行了删除操作。
     */
    protected function flushValues()
    {
        return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}

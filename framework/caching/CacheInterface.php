<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * CacheInterface 是缓存的基础接口。
 *
 * 数据可以通过调用 [[set()]] 方法存入缓存中，而后（在同一个请求或不同的请求）可以调用 [[get()]]
 * 方法再次获得这个数据。在这两个操作中，
 * 需要一个指明缓存数据的键。调用 [[set()]] 方法时还可以传递过期时间和 [[Dependency|dependency]]
 * 缓存依赖。如果在调用 [[get()]] 方法时缓存时间过期或者缓存依赖发生变化，
 * 那么缓存不会返回数据。
 *
 * 典型的缓存使用模式就像下面这样：
 *
 * ```php
 * $key = 'demo';
 * $data = $cache->get($key);
 * if ($data === false) {
 *     // ...generate $data here...
 *     $cache->set($key, $data, $duration, $dependency);
 * }
 * ```
 *
 * 因为 CacheInterface 继承了 [[\ArrayAccess]] 接口，可以像数组那样使用它，比如，
 *
 * ```php
 * $cache['foo'] = 'some data';
 * echo $cache['foo'];
 * ```
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.13. Previous framework versions used abstract class [[yii\caching\Cache]] as interface.
 */
interface CacheInterface extends \ArrayAccess
{
    /**
     * 根据给定的字符串构建一个标准化的缓存键。
     *
     * 如果给定的字符串只包含字母和数字，且长度不超过 32 个字符，
     * 那么缓存键就是增加了 [[keyPrefix]] 前缀的字符串。否则，给定的字符串
     * 会经过序列化，应用 MD5 散列，然后再增加 [[keyPrefix]] 前缀生成标准的缓存键。
     *
     * @param mixed $key 需要标准化的键。
     * @return string 生成的缓存键。
     */
    public function buildKey($key);

    /**
     * 根据指定的键从缓存中获取数据。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return mixed 缓存中的值，如果缓存值不存在，
     * 缓存已经过期或者和缓存数据相关的缓存依赖发生了变化则返回 false。
     */
    public function get($key);

    /**
     * 检测指定的键是否存在缓存中。
     * 如果缓存数据量大的话，这比从缓存中直接获取值稍快些。
     * 如果当前环境的缓存系统不支持该特性，该方法将会尝试模拟实现该特性，
     * 但是相比直接从缓存中获取数据在性能上没有什么提高。
     * 注意，缓存数据如果有关联的依赖存在，并且确实发生了变化，
     * 但是该方法不会检测缓存依赖的变化情况。所以有可能调用 [[get]] 方法返回 false，
     * 而调用该方法返回 true。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果缓存值存在返回 true，如果缓存值不存在或者已经过期则返回 false。
     */
    public function exists($key);

    /**
     * 根据多个缓存的键一次从缓存中获取多个对应的缓存数据。
     * 一些缓存驱动（比如 memcache，apc）允许一次性获取多个缓存数据，这无疑会提高性能。
     * 如果当前环境的缓存系统不支持该特性的话，
     * 该方法也会尝试模拟实现。
     * @param string[] $keys 指明多个缓存数据的字符串键列表。
     * @return array 对应缓存键列表的缓存数据，返回的数组格式是
     * （key, value）键值对。
     * 如果缓存值不存在或者缓存过期，那么对应的缓存值将会是 false。
     */
    public function multiGet($keys);

    /**
     * 根据键存入缓存值。
     * 如果相同的键已经存在缓存中，那么之前的缓存数据和过期时间，
     * 将会被新的缓存数据和缓存时间分别替换。
     *
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @param mixed $value 要缓存的值。
     * @param int $duration 以秒为单位的缓存数据的过期时间，如果没有传递该参数，
     * 默认使用 [[defaultDuration]]。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return bool 数据是否成功存入缓存。
     */
    public function set($key, $value, $duration = null, $dependency = null);

    /**
     * 存入多个数据到缓存中。每个数据项都包含缓存键和对应的缓存值。
     * 如果相同的键已经存在缓存中，那么之前的缓存数据和过期时间，
     * 将会被新的缓存数据和缓存时间分别替换。
     *
     * @param array $items 要缓存的数据项，作为键值对。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return array 未能存入缓存的键列表。
     */
    public function multiSet($items, $duration = 0, $dependency = null);

    /**
     * 如果对应缓存键不存在，那么把由它指明的缓存数据存入缓存中，
     * 如果缓存键存在，那么什么也不会做。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @param mixed $value 要缓存的值。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return bool 数据是否成功存入缓存。
     */
    public function add($key, $value, $duration = 0, $dependency = null);

    /**
     * 存入多个数据到缓存中。每个数据项都包含缓存键和对应的缓存值。
     * 如果缓存中已经存在了对应的键，那么这个存在的缓存值和过期时间将会继续保留。
     *
     * @param array $items 要缓存的数据项，作为键值对。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return array 未能存入缓存的键列表。
     */
    public function multiAdd($items, $duration = 0, $dependency = null);

    /**
     * 根据指定的键从缓存中删除数据值。
     * @param mixed $key 指明要删除的缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果删除过程没有发生错误。
     */
    public function delete($key);

    /**
     * 从缓存中删除所有的值。
     * 如果缓存系统在多个应用中共享的话，请谨慎执行该操作。
     * @return bool 是否冲刷缓存过程是成功地。
     */
    public function flush();

    /**
     * 联合了 [[set()]] 和 [[get()]] 两个方法的大方法，可以根据 $key 获取值， 
     * 或者在 $key 对应的缓存数据不存在的情况下，存入 $callable 执行后的结果作为缓存数据。
     *
     * 使用实例：
     *
     * ```php
     * public function getTopProducts($count = 10) {
     *     $cache = $this->cache; // Could be Yii::$app->cache
     *     return $cache->getOrSet(['top-n-products', 'n' => $count], function ($cache) use ($count) {
     *         return Products::find()->mostPopular()->limit($count)->all();
     *     }, 1000);
     * }
     * ```
     *
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @param callable|\Closure $callable 用来生成缓存数据的回调或者匿名函数。
     * 如果 $callable 返回 `false`，那么不会缓存该值。
     * @param int $duration 以秒为单位的缓存数据的过期时间，如果没有传递该参数，
     * 默认使用 [[defaultDuration]]。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 `false` 的话，该参数将会被忽略。
     * @return mixed $callable 的执行结果。
     */
    public function getOrSet($key, $callable, $duration = null, $dependency = null);
}

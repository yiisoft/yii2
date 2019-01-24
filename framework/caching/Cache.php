<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Yii;
use yii\base\Component;
use yii\helpers\StringHelper;

/**
 * Cache 是所有缓存类的基类，这些缓存类支持不同的缓存驱动。
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
 * 因为 Cache 实现了 [[\ArrayAccess]] 接口，可以像数组那样使用它。比如，
 *
 * ```php
 * $cache['foo'] = 'some data';
 * echo $cache['foo'];
 * ```
 *
 * 具体的驱动类应该实现如下的方法来做实际的缓存操作：
 *
 * - [[getValue()]]: 根据键（如果有）从缓存中获取值
 * - [[setValue()]]: 根据键把值存入缓存中
 * - [[addValue()]]: 只有缓存中没有这个键时才把值存入缓存中
 * - [[deleteValue()]]: 根据键从缓存中删除值
 * - [[flushValues()]]: 从缓存中删除所有值
 *
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Cache extends Component implements CacheInterface
{
    /**
     * @var string 每个缓存键的一个字符串前缀，据此可以保证在整个缓存系统层面上它都是全局唯一的。
     * 如果出现相同的缓存驱动在多个不同的应用环境下使用，建议你为每个应用环境里的这个缓存系统
     * 设置一个唯一的缓存键前缀。
     *
     * 为保证系统的共用性，你应该只使用由字母和数字组成的字符串。
     */
    public $keyPrefix;
    /**
     * @var null|array|false 用来序列化和反序列化缓存数据的函数。默认是 null，这意味着
     * 默认使用 PHP 的 `serialize()` 和 `unserialize()` 两个函数。如果你想使用更高效的序列化功能
     * （比如 [igbinary](http://pecl.php.net/package/igbinary)），可以配置该属性为两个元素的数组。
     * 第一个元素指明序列化的函数而第二个元素则指明反序列化的函数。
     * 如果该属性设置为 false，那么数据将直接在当前缓存组件下存入缓存，无需序列化过程，
     * 获取数据时同样不需要反序列化。如果你正使用 [[Dependency|cache dependency]] 缓存依赖，
     * 那么请你不要关闭序列化功能，因为缓存依赖的实现有赖于数据序列化功能。还有，
     * 一些缓存的实现类在保存和获取非字符串数据时并不都是完全合适的。
     */
    public $serializer;
    /**
     * @var int 以秒为单位的默认的缓存持续时间。默认是 0，意味着永不过期。
     * 在使用 [[set()]] 时并且没有明确传递时间参数时才会使用这个属性。
     * @since 2.0.11
     */
    public $defaultDuration = 0;


    /**
     * 根据给定的键构建标准的缓存键。
     *
     * 如果给定的键是一个只包含字母数字并且不超过 32 个字符的字符串，
     * 那么只增加 [[keyPrefix]] 后直接返回。否则，会经过序列化处理，比如应用 MD5 散列，
     * 然后再增加 [[keyPrefix]] 前缀后生成标准的键返回。
     *
     * @param mixed $key 要标准化的键。
     * @return string 生成的缓存键。
     */
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && StringHelper::byteLength($key) <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }

    /**
     * 根据指定的键从缓存中获取数据。
     * @param mixed $key 指明缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return mixed 缓存中的值，如果缓存值不存在，
     * 缓存已经过期或者和缓存数据相关的缓存依赖发生了变化则返回 false。
     */
    public function get($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);
        if ($value === false || $this->serializer === false) {
            return $value;
        } elseif ($this->serializer === null) {
            $value = unserialize($value);
        } else {
            $value = call_user_func($this->serializer[1], $value);
        }
        if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
            return $value[0];
        }

        return false;
    }

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
    public function exists($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);

        return $value !== false;
    }

    /**
     * 根据多个缓存的键一次从缓存中获取多个对应的缓存数据。
     * 一些缓存驱动（比如 memcache，apc）允许一次性获取多个缓存数据，这无疑会提高性能。
     * 如果当前环境的缓存系统不支持该特性的话，
     * 该方法也会尝试模拟实现。
     *
     * @param string[] $keys 指明多个缓存数据的字符串键列表。
     * @return array 对应缓存键列表的缓存数据，返回的数组格式是
     * （key, value）键值对。
     * 如果缓存值不存在或者缓存过期，那么对应的缓存值将会是 false。
     * @deprecated 该方法是 [[multiGet()]] 的别名，会在 2.1.0 版本移除。
     */
    public function mget($keys)
    {
        return $this->multiGet($keys);
    }

    /**
     * 根据多个缓存的键一次从缓存中获取多个对应的缓存数据。
     * 一些缓存驱动（比如 memcache，apc）允许一次性获取多个缓存数据，这无疑会提高性能。
     * 如果当前环境的缓存系统不支持该特性的话，
     * 该方法也会尝试模拟实现。
     * @param string[] $keys 指明多个缓存数据的字符串键列表。
     * @return array 对应缓存键列表的缓存数据，返回的数组格式是
     * （key, value）键值对。
     * 如果缓存值不存在或者缓存过期，那么对应的缓存值将会是 false。
     * @since 2.0.7
     */
    public function multiGet($keys)
    {
        $keyMap = [];
        foreach ($keys as $key) {
            $keyMap[$key] = $this->buildKey($key);
        }
        $values = $this->getValues(array_values($keyMap));
        $results = [];
        foreach ($keyMap as $key => $newKey) {
            $results[$key] = false;
            if (isset($values[$newKey])) {
                if ($this->serializer === false) {
                    $results[$key] = $values[$newKey];
                } else {
                    $value = $this->serializer === null ? unserialize($values[$newKey])
                        : call_user_func($this->serializer[1], $values[$newKey]);

                    if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
                        $results[$key] = $value[0];
                    }
                }
            }
        }

        return $results;
    }

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
    public function set($key, $value, $duration = null, $dependency = null)
    {
        if ($duration === null) {
            $duration = $this->defaultDuration;
        }

        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->setValue($key, $value, $duration);
    }

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
     * @deprecated 该方法是 [[multiSet()]] 的别名，会在 2.1.0 版本移除
     */
    public function mset($items, $duration = 0, $dependency = null)
    {
        return $this->multiSet($items, $duration, $dependency);
    }

    /**
     * 存入多个数据到缓存中。每个数据项都包含缓存键和对应的缓存值。
     * 如果相同的键已经存在缓存中，那么之前的缓存数据和过期时间，
     * 将会被新的缓存数据和缓存时间分别替换。
     *
     * @param array $items 要缓存的数据项，以键值对形式。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return array 未能存入缓存数据的键列表。
     * @since 2.0.7
     */
    public function multiSet($items, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        return $this->setValues($data, $duration);
    }

    /**
     * 存入多个数据到缓存中。每个数据项都包含缓存键和对应的缓存值。
     * 如果缓存中已经存在了对应的键，那么这个存在的缓存值和过期时间将会继续保留。
     *
     * @param array $items 要缓存的数据项，以键值对形式。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return array 未能存入缓存的键列表。
     * @deprecated 该方法是 [[multiAdd()]] 的别名，会在 2.1.0 版本移除。
     */
    public function madd($items, $duration = 0, $dependency = null)
    {
        return $this->multiAdd($items, $duration, $dependency);
    }

    /**
     * 存入多个数据到缓存中。每个数据项都包含缓存键和对应的缓存值。
     * 如果缓存中已经存在了对应的键，那么这个存在的缓存值和过期时间将会继续保留。
     *
     * @param array $items 要缓存的数据项，以键值对形式。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @param Dependency $dependency 缓存数据的依赖。如果依赖发生变化，
     * 那么使用 [[get()]] 方法获取对应的缓存数据时将是无效的。
     * 如果 [[serializer]] 是 false 的话，该参数将会被忽略。
     * @return array 未能存入缓存的键列表。
     * @since 2.0.7
     */
    public function multiAdd($items, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        return $this->addValues($data, $duration);
    }

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
    public function add($key, $value, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->addValue($key, $value, $duration);
    }

    /**
     * 根据指定的键从缓存中删除数据值。
     * @param mixed $key 指明要删除的缓存值的键。可以是一个简单的字符串，
     * 或者是一个包含着缓存键的复杂数据结构。
     * @return bool 如果删除过程没有发生错误。
     */
    public function delete($key)
    {
        $key = $this->buildKey($key);

        return $this->deleteValue($key);
    }

    /**
     * 从缓存中删除所有的值。
     * 如果缓存系统在多个应用中共享的话，请谨慎执行该操作。
     * @return bool 是否冲刷缓存过程是成功地。
     */
    public function flush()
    {
        return $this->flushValues();
    }

    /**
     * 根据指定的键从缓存中获取数据。
     * 这个从指定的缓存驱动中获取数据的方法，
     * 应该留给子类来实现。
     * @param string $key 指明缓存数据的唯一键。
     * @return mixed|false 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     * 大多数情况下它是一个字符串，如果你禁用了 [[serializer]]，它也可以是其它数据类型。
     */
    abstract protected function getValue($key);

    /**
     * 根据指定的键把数据存入缓存中。
     * 这个在指定的缓存驱动上存入数据的方法，
     * 应该留给子类来实现。
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串，如果你禁用了 [[serializer]]，
     * 它也可以是其它数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    abstract protected function setValue($key, $value, $duration);

    /**
     * 在指定的键不存在的情况下，才存入指定的缓存值。
     * 这个在指定的缓存驱动上存入数据的方法，
     * 应该留给子类来实现。
     * @param string $key 指明缓存值的键。
     * @param mixed $value 要缓存的值。大多数情况下它是一个字符串，如果你禁用了 [[serializer]]，
     * 它也可以是其它数据类型。
     * @param int $duration 缓存值过期的秒数。0 表示永不过期。
     * @return bool 如果成功存入缓存返回 true，否则返回 false。
     */
    abstract protected function addValue($key, $value, $duration);

    /**
     * 根据指定的键把数据从缓存中删除。
     * 这个从实际的缓存驱动里删除数据的方法应该留给子类来实现。
     * @param string $key 指明要删除缓存的键。
     * @return bool 如果删除过程没有发生错误。
     */
    abstract protected function deleteValue($key);

    /**
     * 从缓存中删除所有的值。
     * 子类应该通过实现该方法完成冲刷数据的操作。
     * @return bool 是否冲刷缓存过程是成功地。
     */
    abstract protected function flushValues();

    /**
     * 根据多个缓存的键一次从缓存中获取多个对应的缓存数据。
     * 默认的实现就是通过循环调用 [[getValue()]] 方法
     * 从缓存中依次获取数据。如果当前的缓存驱动支持 multiget，
     * 该方法将会被覆盖而是尽量使用 multiget 来发挥它的特性。
     * @param array $keys 指明多个缓存数据的字符串键列表。
     * @return array 由对应的键指定的缓存数据列表。
     */
    protected function getValues($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->getValue($key);
        }

        return $results;
    }

    /**
     * 一次性存入多个 键-值 对到缓存中。
     * 默认的实现就是通过循环调用 [[setValue()]] 方法。如果当前环境的缓存驱动
     * 支持 multi-set，该方法将会被覆盖而是尽量使用 multi-set 来发挥它的特性。
     * @param array $data 数组，数组的键对应缓存的键而值就是要缓存的值。
     * @param int $duration 缓存数据过期的秒数，0 意味着永不过期。
     * @return array 未能存入缓存数据的键列表。
     */
    protected function setValues($data, $duration)
    {
        $failedKeys = [];
        foreach ($data as $key => $value) {
            if ($this->setValue($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
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
        $failedKeys = [];
        foreach ($data as $key => $value) {
            if ($this->addValue($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    /**
     * 返回是否指定的键存在缓存中的布尔值。
     * 该方法是实现 [[\ArrayAccess]] 接口必须实现的方法。
     * @param string $key 一个指明缓存数据的键。
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->get($key) !== false;
    }

    /**
     * 根据指定的键从缓存中获取数据。
     * 该方法是实现 [[\ArrayAccess]] 接口必须实现的方法。
     * @param string $key 一个指明缓存数据的键。
     * @return mixed 缓存中的值，如果缓存值不存在或者已经过期则返回 false。
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * 把由键指定的值存入缓存中。
     * 如果缓存中已经有来这个键，那么之前存在的缓存值
     * 将会被新值替换。如果要增加过期参数和依赖，请使用 [[set()]] 方法。
     * 该方法是实现 [[\ArrayAccess]] 接口必须实现的方法。
     * @param string $key 指明缓存数据的键。
     * @param mixed $value 要缓存的数据。
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * 根据指定的键从缓存中删除数据。
     * 该方法是实现 [[\ArrayAccess]] 接口必须实现的方法。
     * @param string $key 要删除的值对应的键。
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }

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
     *         return Products::find()->mostPopular()->limit(10)->all();
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
     * @since 2.0.11
     */
    public function getOrSet($key, $callable, $duration = null, $dependency = null)
    {
        if (($value = $this->get($key)) !== false) {
            return $value;
        }

        $value = call_user_func($callable, $this);
        if (!$this->set($key, $value, $duration, $dependency)) {
            Yii::warning('Failed to set cache value for key ' . json_encode($key), __METHOD__);
        }

        return $value;
    }
}

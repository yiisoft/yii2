<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * ArrayCache provides caching for the current request only by storing the values in an array.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             '__class' => yii\caching\Cache::class,
 *             'handler' => [
 *                 '__class' => yii\caching\ArrayCache::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * See [[\Psr\SimpleCache\CacheInterface]] for common cache operations that ArrayCache supports.
 *
 * Unlike the [[Cache]], ArrayCache allows the expire parameter of [[set()]] and [[setMultiple()]]  to
 * be a floating point number, so you may specify the time in milliseconds (e.g. 0.1 will be 100 milliseconds).
 *
 * For enhanced performance of ArrayCache, you can disable serialization of the stored data by setting [[$serializer]] to `false`.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ArrayCache extends SimpleCache
{
    /**
     * @var array cached values.
     */
    private $_cache = [];


    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $key = $this->normalizeKey($key);
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
    protected function setValue($key, $value, $ttl)
    {
        $this->_cache[$key] = [$value, $ttl === 0 ? 0 : microtime(true) + $ttl];
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
    public function clear()
    {
        $this->_cache = [];
        return true;
    }
}

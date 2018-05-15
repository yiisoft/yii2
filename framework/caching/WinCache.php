<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * WinCache provides Windows Cache caching in terms of an application component.
 *
 * To use this application component, the [WinCache PHP extension](http://www.iis.net/expand/wincacheforphp)
 * must be loaded. Also note that "wincache.ucenabled" should be set to "On" in your php.ini file.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             '__class' => yii\caching\Cache::class,
 *             'handler' => [
 *                 '__class' => yii\caching\WinCache::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * See [[\Psr\SimpleCache\CacheInterface]] for common cache operations that are supported by WinCache.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class WinCache extends SimpleCache
{
    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return wincache_ucache_exists($this->normalizeKey($key));
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        return wincache_ucache_get($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValues($keys)
    {
        return wincache_ucache_get($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $ttl)
    {
        return wincache_ucache_set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValues($values, $ttl)
    {
        return wincache_ucache_set($values, null, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        return wincache_ucache_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return wincache_ucache_clear();
    }
}

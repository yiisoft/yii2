<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\Component;

/**
 * DummyCache is a placeholder cache component.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             '__class' => yii\caching\Cache::class,
 *             'handler' => [
 *                 '__class' => yii\caching\DummyCache::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * DummyCache does not cache anything. It is provided so that one can always configure
 * a 'cache' application component and save the check of existence of `\Yii::$app->cache`.
 * By replacing DummyCache with some other cache component, one can quickly switch from
 * non-caching mode to caching mode.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DummyCache extends Component implements \Psr\SimpleCache\CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        return array_fill_keys($keys, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        return true;
    }
}

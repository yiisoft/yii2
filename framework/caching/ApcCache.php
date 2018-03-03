<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use yii\base\InvalidConfigException;

/**
 * ApcCache provides APCu caching in terms of an application component.
 *
 * To use this application component, the [APCu PHP extension](http://www.php.net/apcu) must be loaded.
 * In order to enable APCu for CLI you should add "apc.enable_cli = 1" to your php.ini.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'cache' => [
 *             '__class' => yii\caching\Cache::class,
 *             'handler' => [
 *                 '__class' => yii\caching\ApcCache::class,
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * See [[\Psr\SimpleCache\CacheInterface]] for common cache operations that ApcCache supports.
 *
 * For more details and usage information on Cache, see the [guide article on caching](guide:caching-overview).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ApcCache extends SimpleCache
{
    /**
     * Initializes this application component.
     * It checks if extension required is loaded.
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!extension_loaded('apcu')) {
            throw new InvalidConfigException('ApcCache requires PHP apcu extension to be loaded.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return apcu_exists($this->normalizeKey($key));
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue($key)
    {
        return apcu_fetch($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValues($keys)
    {
        $values = apcu_fetch($keys);
        return is_array($values) ? $values : [];
    }

    /**
     * {@inheritdoc}
     */
    protected function setValue($key, $value, $ttl)
    {
        return apcu_store($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function setValues($values, $ttl)
    {
        $result = apcu_store($values, null, $ttl);
        return is_array($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteValue($key)
    {
        return apcu_delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return apcu_clear_cache();
    }
}

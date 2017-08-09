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
 * See [[Cache]] for common cache operations that ApcCache supports.
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
        return apcu_exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $value = apcu_fetch($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $values = apcu_fetch($keys);
        if (is_array($values)) {
            return $values;
        }
        return array_fill_keys($keys, $default);
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
    protected function setValues($data, $ttl)
    {
        $result = apcu_store($data, null, $ttl);
        return is_array($result);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
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

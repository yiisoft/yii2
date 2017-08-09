<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

use Psr\SimpleCache\CacheInterface;
use yii\base\Component;

/**
 * SimpleCache
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
abstract class SimpleCache extends Component implements CacheInterface
{
    /**
     * @var int default duration in seconds before a cache entry will expire. Default value is 0, meaning infinity.
     * This value is used by [[set()]] if the duration is not explicitly given.
     * @since 2.0.11
     */
    public $defaultTtl = 0;


    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->get($key, $default);
        }
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->setValues($values, $this->normalizeTtl($ttl));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->setValue($key, $value, $this->normalizeTtl($ttl));
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return ($this->get($key) !== null);
    }

    /**
     * Normalizes cache TTL handling `null` value and [[\DateInterval]] objects.
     * @param int|\DateInterval $ttl raw TTL
     * @return int TTL value as UNIX timestamp.
     */
    protected function normalizeTtl($ttl)
    {
        if ($ttl === null) {
            return $this->defaultTtl;
        }
        if ($ttl instanceof \DateInterval) {
            $seconds = $ttl->days * 86400 + $ttl->h * 3600 + $ttl->i * 60 + $ttl->s;
            if ($ttl->invert) {
                $seconds = $seconds * -1;
            }
            return $seconds;
        }
        return $ttl;
    }

    /**
     * Stores a value identified by a key in cache.
     * This method should be implemented by child classes to store the data
     * in specific cache storage.
     * @param string $key the key identifying the value to be cached
     * @param mixed $value the value to be cached. Most often it's a string. If you have disabled [[serializer]],
     * it could be something else.
     * @param int $ttl the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool true if the value is successfully stored into cache, false otherwise
     */
    abstract protected function setValue($key, $value, $ttl);

    /**
     * Stores multiple key-value pairs in cache.
     * The default implementation calls [[setValue()]] multiple times store values one by one. If the underlying cache
     * storage supports multi-set, this method should be overridden to exploit that feature.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param int $ttl the number of seconds in which the cached values will expire. 0 means never expire.
     * @return bool `true` on success and `false` on failure.
     */
    protected function setValues($data, $ttl)
    {
        $result = true;
        foreach ($data as $key => $value) {
            if ($this->setValue($key, $value, $ttl) === false) {
                $result = false;
            }
        }
        return $result;
    }
}
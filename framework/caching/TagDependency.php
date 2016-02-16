<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * TagDependency associates a cached data item with one or multiple [[tags]].
 *
 * By calling [[invalidate()]], you can invalidate all cached data items that are associated with the specified tag name(s).
 *
 * ```php
 * // setting multiple cache keys to store data forever and tagging them with "user-123"
 * Yii::$app->cache->set('user_42_profile', '', 0, new TagDependency(['tags' => 'user-123']));
 * Yii::$app->cache->set('user_42_stats', '', 0, new TagDependency(['tags' => 'user-123']));
 *
 * // invalidating all keys tagged with "user-123"
 * TagDependency::invalidate(Yii::$app->cache, 'user-123');
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TagDependency extends Dependency
{
    /**
     * @var string|array a list of tag names for this dependency. For a single tag, you may specify it as a string.
     */
    public $tags = [];


    /**
     * Generates the data needed to determine if dependency has been changed.
     * This method does nothing in this class.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     */
    protected function generateDependencyData($cache)
    {
        $timestamps = $this->getTimestamps($cache, (array) $this->tags);

        $newKeys = [];
        foreach ($timestamps as $key => $timestamp) {
            if ($timestamp === false) {
                $newKeys[] = $key;
            }
        }
        if (!empty($newKeys)) {
            $timestamps = array_merge($timestamps, static::touchKeys($cache, $newKeys));
        }

        return $timestamps;
    }

    /**
     * Performs the actual dependency checking.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return boolean whether the dependency is changed or not.
     */
    public function getHasChanged($cache)
    {
        $timestamps = $this->getTimestamps($cache, (array) $this->tags);
        return $timestamps !== $this->data;
    }

    /**
     * Invalidates all of the cached data items that are associated with any of the specified [[tags]].
     * @param Cache $cache the cache component that caches the data items
     * @param string|array $tags
     */
    public static function invalidate($cache, $tags)
    {
        $keys = [];
        foreach ((array) $tags as $tag) {
            $keys[] = $cache->buildKey([__CLASS__, $tag]);
        }
        static::touchKeys($cache, $keys);
    }

    /**
     * Generates the timestamp for the specified cache keys.
     * @param Cache $cache
     * @param string[] $keys
     * @return array the timestamp indexed by cache keys
     */
    protected static function touchKeys($cache, $keys)
    {
        $items = [];
        $time = microtime();
        foreach ($keys as $key) {
            $items[$key] = $time;
        }
        $cache->multiSet($items);
        return $items;
    }

    /**
     * Returns the timestamps for the specified tags.
     * @param Cache $cache
     * @param string[] $tags
     * @return array the timestamps indexed by the specified tags.
     */
    protected function getTimestamps($cache, $tags)
    {
        if (empty($tags)) {
            return [];
        }

        $keys = [];
        foreach ($tags as $tag) {
            $keys[] = $cache->buildKey([__CLASS__, $tag]);
        }

        return $cache->multiGet($keys);
    }
}

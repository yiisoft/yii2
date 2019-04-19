<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\caching;

/**
 * TagDependency 是基于缓存数据的一个或者多个 [[tags]] 实现的缓存依赖类。
 *
 * 通过调用 [[invalidate()]] 方法，你可以使得所有和指定的 tag 关联的缓存数据都置为无效。
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
 * 在 Cache 上更多的详情和详细的使用信息，请参考 [guide article on caching](guide:caching-overview)。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TagDependency extends Dependency
{
    /**
     * @var string|array 依赖的 tag 名称列表。如果仅仅是一个单独的 tag ,你可以指定它为一个字符串。
     */
    public $tags = [];


    /**
     * 生成在判断依赖是否发生变化时用到的依赖数据。
     * 在该类中这个方法不需要做什么。
     * @param CacheInterface $cache 正在计算缓存依赖的缓存组件。
     * @return mixed 判断依赖是否发生变化时的依赖数据。
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
     * {@inheritdoc}
     */
    public function isChanged($cache)
    {
        $timestamps = $this->getTimestamps($cache, (array) $this->tags);
        return $timestamps !== $this->data;
    }

    /**
     * 使得所有和任何指定的 [[tags]] 关联的缓存数据都置为无效。
     * @param CacheInterface $cache 缓存数据项的缓存组件。
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
     * 根据指定的缓存键生成时间戳。
     * @param CacheInterface $cache
     * @param string[] $keys
     * @return array 由缓存键为下标值为时间戳组成的数组。
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
     * 根据指定的 tags 返回时间戳。
     * @param CacheInterface $cache
     * @param string[] $tags
     * @return array 由 tags 为数组下标数组值为时间戳的数组。
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

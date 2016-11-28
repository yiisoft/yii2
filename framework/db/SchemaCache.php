<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\Object;
use yii\caching\Cache;
use yii\caching\TagDependency;

/**
 * Cache application component that is used to cache the table metadata.
 */
class SchemaCache extends Object
{
    /**
     * @var Connection the database connection.
     */
    public $db;
    /**
     * @var boolean whether to enable schema caching.
     * Note that in order to enable truly schema caching, a valid cache component as specified
     * by [[cache]] must be enabled and [[enabled]] must be set true.
     *
     * @see [[duration]]
     * @see [[exclude]]
     * @see [[cache]]
     */
    public $enabled = false;
    /**
     * @var integer number of seconds that table metadata can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     *
     * @see [[enabled]]
     */
    public $duration = 3600;
    /**
     * @var array list of tables whose metadata should NOT be cached. Defaults to empty array.
     * The table names may contain schema prefix, if any. Do not quote the table names.
     *
     * @see [[enabled]]
     */
    public $exclude = [];
    /**
     * @var Cache|string the cache object or the ID of the cache application component that
     * is used to cache the table metadata.
     *
     * @see [[enabled]]
     */
    public $cache = 'cache';

    /**
     * Obtains the metadata for the named table.
     *
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     *
     * @return null|boolean|TableSchema table metadata. Null if the named table does not exist. False if caching is disabled or table is missing in cache.
     */
    public function getTableSchema($name)
    {
        $cache = $this->getCache();
        if (null === $cache) {
            return false;
        }

        if (in_array($name, $this->exclude, true)) {
            return false;
        }

        return $cache->get($this->getCacheKey($name));
    }

    /**
     * Caching the table schema.
     *
     * @param string $name table name. The table name may contain schema name if any. Do not quote the table name.
     * @param TableSchema $schema table metadata.
     */
    public function cacheTableSchema($name, $schema)
    {
        $cache = $this->getCache();
        if (null === $cache) {
            return;
        }

        $cache->set($this->getCacheKey($name), $schema, $this->duration, new TagDependency([
            'tags' => $this->getCacheTag(),
        ]));
    }

    /**
     * Refreshes the schema.
     * This method cleans up all cached table schemas so that they can be re-created later
     * to reflect the database schema change.
     */
    public function refresh()
    {
        $cache = $this->getCache();
        if (null === $cache) {
            return;
        }

        TagDependency::invalidate($cache, $this->getCacheTag());
    }

    /**
     * Refreshes the particular table schema.
     * This method cleans up cached table schema so that it can be re-created later
     * to reflect the database schema change.
     *
     * @param string $name table name.
     */
    public function refreshTableSchema($name)
    {
        $cache = $this->getCache();
        if (null === $cache) {
            return;
        }

        $cache->delete($this->getCacheKey($name));
    }

    /**
     * Obtains the cache object that is used to cache the table metadata.
     *
     * @return Cache|null cache object. Null if caching is disabled or cache object is not extends of Cache component.
     */
    protected function getCache()
    {
        if (true !== $this->enabled) {
            return null;
        }

        $cache = (is_string($this->cache) ? Yii::$app->get($this->cache, false) : $this->cache);
        if ($cache instanceof Cache) {
            return $cache;
        }

        return null;
    }

    /**
     * Returns the cache key for the specified table name.
     *
     * @param string $name the table name
     *
     * @return string the cache key name
     */
    protected function getCacheKey($name)
    {
        return md5(serialize([
            __METHOD__,
            $this->db->dsn,
            $this->db->username,
            $name,
        ]));
    }

    /**
     * Returns the cache tag name.
     * This allows [[refresh()]] to invalidate all cached table schemas.
     *
     * @return string the cache tag name
     */
    protected function getCacheTag()
    {
        return md5(serialize([
            __METHOD__,
            $this->db->dsn,
            $this->db->username,
        ]));
    }
}
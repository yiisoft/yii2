<?php

namespace yii\db;

/**
 * Trait CacheableQueryTrait provides methods to cache query execution result.
 *
 * The class that uses this trait must have the $db property:
 * @property Connection $db
 *
 * @author hubeiwei <hubeiwei@hotmail.com>
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.14
 */
trait CacheableQueryTrait
{
    /**
     * @var int the default number of seconds that query results can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire. And use a negative number to indicate
     * query cache should not be used.
     * @see cache()
     */
    public $queryCacheDuration;
    /**
     * @var \yii\caching\Dependency the dependency to be associated with the cached query result for this command
     * @see cache()
     */
    public $queryCacheDependency;

    /**
     * Enables query cache for this command or query.
     * @param int $duration the number of seconds that query result of this command or query can remain valid in the cache.
     * If this is not set, the value of [[Connection::queryCacheDuration]] will be used instead.
     * Use 0 to indicate that the cached data will never expire.
     * @param \yii\caching\Dependency $dependency the cache dependency associated with the cached result.
     * @return $this the command or query object itself
     */
    public function cache($duration = null, $dependency = null)
    {
        $this->queryCacheDuration = $duration !== null ? $duration : $this->db->queryCacheDuration;
        $this->queryCacheDependency = $dependency;
        return $this;
    }

    /**
     * Disables query cache for this command or query.
     * @return $this the command or query object itself
     */
    public function noCache()
    {
        $this->queryCacheDuration = -1;
        return $this;
    }

    /**
     * Checks, whether caching is enabled
     * @return bool
     */
    public function hasCache()
    {
        return $this->queryCacheDuration !== null || $this->queryCacheDependency !== null;
    }
}

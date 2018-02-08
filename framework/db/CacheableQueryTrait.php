<?php

namespace yii\db;

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
     * Enables query cache for this command.
     * @param int $duration the number of seconds that query result of this command can remain valid in the cache.
     * If this is not set, the value of [[Connection::queryCacheDuration]] will be used instead.
     * Use 0 to indicate that the cached data will never expire.
     * @param \yii\caching\Dependency $dependency the cache dependency associated with the cached query result.
     * @return $this the command object itself
     */
    public function cache($duration = null, $dependency = null)
    {
        $this->queryCacheDuration = $duration;
        $this->queryCacheDependency = $dependency;
        return $this;
    }

    /**
     * Disables query cache for this command.
     * @return $this the command object itself
     */
    public function noCache()
    {
        $this->queryCacheDuration = -1;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasCache()
    {
        return $this->queryCacheDuration !== null || $this->queryCacheDependency !== null;
    }
}

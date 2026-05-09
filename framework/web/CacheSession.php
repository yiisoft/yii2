<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\caching\CacheInterface;
use yii\di\Instance;

/**
 * CacheSession implements a session component using cache as storage medium.
 *
 * The cache being used can be any cache application component.
 * The ID of the cache application component is specified via [[cache]], which defaults to 'cache'.
 *
 * Beware, by definition cache storage are volatile, which means the data stored on them
 * may be swapped out and get lost. Therefore, you must make sure the cache used by this component
 * is NOT volatile. If you want to use database as storage medium, [[DbSession]] is a better choice.
 *
 * The following example shows how you can configure the application to use CacheSession:
 * Add the following to your application config under `components`:
 *
 * ```
 * 'session' => [
 *     'class' => 'yii\web\CacheSession',
 *     // 'cache' => 'mycache',
 * ]
 * ```
 *
 * @property-read bool $useCustomStorage Whether to use custom storage.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CacheSession extends Session
{
    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * The session data will be stored using this cache object.
     *
     * After the CacheSession object is created, if you want to change this property,
     * you should only assign it with a cache object.
     *
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $cache = 'cache';


    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');
    }

    /**
     * Returns a value indicating whether to use custom session storage.
     * This method overrides the parent implementation and always returns true.
     * @return bool whether to use custom storage.
     */
    public function getUseCustomStorage()
    {
        return true;
    }

    /**
     * Session open handler.
     * @internal Do not call this method directly.
     * @param string $savePath session save path
     * @param string $sessionName session name
     * @return bool whether session is opened successfully
     */
    public function openSession($savePath, $sessionName)
    {
        if ($this->getUseStrictMode()) {
            $id = $this->getId();
            if (!$this->cache->exists($this->calculateKey($id))) {
                //This session id does not exist, mark it for forced regeneration
                $this->_forceRegenerateId = $id;
            }
        }

        return parent::openSession($savePath, $sessionName);
    }

    /**
     * Session read handler.
     * @internal Do not call this method directly.
     * @param string $id session ID
     * @return string|false the session data, or false on failure
     */
    public function readSession($id)
    {
        $data = $this->cache->get($this->calculateKey($id));

        return $data === false ? '' : $data;
    }

    /**
     * Session write handler.
     * @internal Do not call this method directly.
     * @param string $id session ID
     * @param string $data session data
     * @return bool whether session write is successful
     */
    public function writeSession($id, $data)
    {
        if ($this->getUseStrictMode() && $id === $this->_forceRegenerateId) {
            //Ignore write when forceRegenerate is active for this id
            return true;
        }

        return $this->cache->set($this->calculateKey($id), $data, $this->getTimeout());
    }

    /**
     * Session destroy handler.
     * @internal Do not call this method directly.
     * @param string $id session ID
     * @return bool whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        $cacheId = $this->calculateKey($id);
        if ($this->cache->exists($cacheId) === false) {
            return true;
        }

        return $this->cache->delete($cacheId);
    }

    /**
     * Generates a unique key used for storing session data in cache.
     * @param string $id session variable name
     * @return mixed a safe cache key associated with the session variable name
     */
    protected function calculateKey($id)
    {
        return [__CLASS__, $id];
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rbac;

use yii\caching\Cache;
use yii\di\Instance;

/**
 * CacheableManager
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
abstract class CacheableManager extends BaseManager
{
    /**
     * @var boolean whether to cache RBAC items.
     */
    public $enableCache = true;
    /**
     * @var integer number of seconds that RBAC items can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @see enableCache
     */
    public $cacheDuration = 3600;
    /**
     * @var string|Cache cache component to be used for caching RBAC items.
     */
    public $cache = 'cache';
    /**
     * @var array cached RBAC items.
     */
    private $_cacheItems = [];
    /**
     * @var array cached RBAC item parent name lists.
     */
    private $_cacheItemParents = [];


    /**
     * Initializes the application component.
     * This method overrides the parent implementation by restoring the cache data.
     */
    public function init()
    {
        parent::init();
        if ($this->enableCache) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
            $this->restoreCacheData();
        }
    }

    /**
     * Destructor.
     * Saves cached items.
     */
    public function __destruct()
    {
        if ($this->enableCache) {
            $this->saveCacheData();
        }
    }

    /**
     * Restores cached items from the cache storage
     */
    protected function restoreCacheData()
    {
        $cacheData = $this->cache->mget([
            $this->getCacheKey('items'),
            $this->getCacheKey('parents'),
        ]);
        if ($cacheData['items'] !== false) {
            $this->_cacheItems = $cacheData['items'];
        }
        if ($cacheData['parents'] !== false) {
            $this->_cacheItemParents = $cacheData['parents'];
        }
    }

    /**
     * Saves cached items into the cache storage
     */
    protected function saveCacheData()
    {
        $this->cache->add($this->getCacheKey('items'), $this->_cacheItems, $this->cacheDuration);
        $this->cache->add($this->getCacheKey('parents'), $this->_cacheItemParents, $this->cacheDuration);
    }

    /**
     * Clears related cache data.
     */
    public function clearCache()
    {
        $this->clearItemsCache();
        $this->clearItemParentsCache();
    }

    /**
     * Clears items cache.
     */
    protected function clearItemsCache()
    {
        if ($this->enableCache) {
            $this->cache->delete($this->getCacheKey('items'));
            $this->_cacheItems = [];
        }
    }

    /**
     * Clears item parents cache.
     */
    protected function clearItemParentsCache()
    {
        if ($this->enableCache) {
            $this->cache->delete($this->getCacheKey('parents'));
            $this->_cacheItemParents = [];
        }
    }

    /**
     * Returns cached item if exists.
     * @param string $itemName item name
     * @return Item|null cached item, `null` is returned, if item does not present in the cache.
     */
    protected function getCachedItem($itemName)
    {
        if (array_key_exists($itemName, $this->_cacheItems)) {
            return $this->_cacheItems[$itemName];
        }
        return null;
    }

    /**
     * Adds an item to the cache.
     * @param Item $item item to be cached.
     */
    protected function addCachedItem(Item $item)
    {
        $this->_cacheItems[$item->name] = $item;
    }

    /**
     * Returns cached parent names for specified item
     * @param string $itemName item name
     * @return array|null list of parent names, `null` is returned, if data does not present in the cache.
     */
    protected function getCachedItemParents($itemName)
    {
        if (array_key_exists($itemName, $this->_cacheItemParents)) {
            return $this->_cacheItemParents[$itemName];
        }
        return null;
    }

    /**
     * Adds item parent names for specified item to the cache.
     * @param string $itemName item name
     * @param array $parents list of parent item names.
     */
    protected function addCachedItemParents($itemName, array $parents)
    {
        $this->_cacheItemParents[$itemName] = $parents;
    }

    /**
     * Returns the cache key for the specified option.
     * @param string $name the cache option name
     * @return mixed the cache key
     */
    protected function getCacheKey($name)
    {
        return [
            __CLASS__,
            $name
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name)
    {
        $item = $this->getCachedItem($name);
        if (!is_object($item)) {
            $item = $this->getItemInternal($name);
            $this->addCachedItem($item);
        }
        return $item;
    }

    /**
     * @param string $name item name
     * @return Item
     */
    abstract protected function getItemInternal($name);

    /**
     * @param string $itemName item name
     * @return array list of item parent names
     */
    protected function getItemParents($itemName)
    {
        $parents = $this->getCachedItemParents($itemName);
        if (!is_array($parents)) {
            $parents = $this->getItemParentsInternal($itemName);
            $this->addCachedItemParents($itemName, $parents);
        }
        return $parents;
    }

    /**
     * @param string $itemName item name
     * @return array list of parent item names.
     */
    abstract protected function getItemParentsInternal($itemName);

    /**
     * @inheritdoc
     */
    public function remove($object)
    {
        $result = parent::remove($object);
        $this->clearItemsCache();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function update($name, $object)
    {
        $result = parent::update($name, $object);
        $this->clearItemsCache();
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        $this->addChildInternal($parent, $child);
        $this->clearItemParentsCache();
    }

    /**
     * @param Item $parent
     * @param Item $child
     */
    abstract protected function addChildInternal($parent, $child);

    /**
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        $this->removeChildInternal($parent, $child);
        $this->clearItemParentsCache();
    }

    /**
     * @param Item $parent
     * @param Item $child
     */
    abstract protected function removeChildInternal($parent, $child);
}
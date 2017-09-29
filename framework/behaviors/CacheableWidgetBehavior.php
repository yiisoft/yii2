<?php

namespace yii\behaviors;

use yii\base\Behavior;
use yii\base\Widget;
use yii\base\WidgetEvent;
use yii\caching\Cache;
use yii\caching\Dependency;
use yii\di\Instance;
use yii\web\View;

/**
 * Cacheable widget behavior.
 *
 * @property Widget $owner
 *
 * @group behaviors
 */
class CacheableWidgetBehavior extends Behavior
{
    /**
     * The cache object or the application component ID of the cache object
     * or a configuration array for creating the object.
     *
     * @var Cache|string|array
     */
    public $cache = 'cache';

    /**
     * Cache duration in seconds.
     *
     * @var int
     */
    public $cacheDuration = 60;

    /**
     * Cache dependency or `null` meaning no cache dependency.
     *
     * @var Dependency|null
     */
    public $cacheDependency;

    /**
     * Cache key or `null` meaning that it should be generated automaticcally.
     *
     * @var mixed|null
     */
    public $cacheKey;

    /**
     * Cache key variations. An array of factors that would cause the variation
     * of the content being cached.
     *
     * @var array
     */
    public $cacheKeyVariations = [];

    /**
     * Whether caching is enabled or not.
     *
     * @var bool
     */
    public $cacheEnabled = true;

    /**
     * Cache object.
     *
     * @var Cache
     */
    private $_cache;

    /**
     * Cache key.
     *
     * @var mixed
     */
    private $_cacheKey;

    /**
     * Owner widget view.
     *
     * @var View
     */
    private $_view;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->initializeCache();
        $this->initializeCacheKey();
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $this->initializeView();
        $this->initializeEventHandlers();
    }

    /**
     * Begins fragment caching. Prevents owner widget from execution
     * if its contents can be retrieved from the cache.
     *
     * @param WidgetEvent $event
     */
    public function beforeRun($event)
    {
        $cacheConfig = [
            'cache' => $this->_cache,
            'duration' => $this->cacheDuration,
            'dependency' => $this->cacheDependency,
            'enabled' => $this->cacheEnabled,
        ];
        if (!$this->_view->beginCache($this->_cacheKey, $cacheConfig)) {
            $event->isValid = false;
        }
    }

    /**
     * Outputs widget contents and ends fragment caching.
     *
     * @param WidgetEvent $event
     */
    public function afterRun($event)
    {
        echo $event->result;
        $event->result = null;

        $this->_view->endCache();
    }

    /**
     * Initializes cache instance.
     */
    private function initializeCache()
    {
        $this->_cache = Instance::ensure($this->cache, Cache::className());
    }

    /**
     * Initializes cache key.
     */
    private function initializeCacheKey()
    {
        if ($this->cacheKey !== null) {
            $this->_cacheKey = $this->cacheKey;
            return;
        }

        $this->_cacheKey = [get_class($this->owner)];
        foreach ($this->cacheKeyVariations as $cacheKeyVariation) {
            $this->_cacheKey[] = $cacheKeyVariation;
        }
    }


    /**
     * Initializes a reference to owner widget view.
     */
    private function initializeView()
    {
        $this->_view = $this->owner->view;
    }

    /**
     * Initializes widget event handlers.
     */
    private function initializeEventHandlers()
    {
        $this->owner->on(Widget::EVENT_BEFORE_RUN, [$this, 'beforeRun']);
        $this->owner->on(Widget::EVENT_AFTER_RUN, [$this, 'afterRun']);
    }
}

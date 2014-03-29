<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\Widget;
use yii\caching\Cache;
use yii\caching\Dependency;
use yii\di\Instance;

/**
 *
 * @property string|boolean $cachedContent The cached content. False is returned if valid content is not found
 * in the cache. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FragmentCache extends Widget
{
    /**
     * @var Cache|string the cache object or the application component ID of the cache object.
     * After the FragmentCache object is created, if you want to change this property,
     * you should only assign it with a cache object.
     */
    public $cache = 'cache';
    /**
     * @var integer number of seconds that the data can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     */
    public $duration = 60;
    /**
     * @var array|Dependency the dependency that the cached content depends on.
     * This can be either a [[Dependency]] object or a configuration array for creating the dependency object.
     * For example,
     *
     * ~~~
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(lastModified) FROM Post',
     * ]
     * ~~~
     *
     * would make the output cache depends on the last modified time of all posts.
     * If any post has its modification time changed, the cached content would be invalidated.
     */
    public $dependency;
    /**
     * @var array list of factors that would cause the variation of the content being cached.
     * Each factor is a string representing a variation (e.g. the language, a GET parameter).
     * The following variation setting will cause the content to be cached in different versions
     * according to the current application language:
     *
     * ~~~
     * [
     *     Yii::$app->language,
     * ]
     */
    public $variations;
    /**
     * @var boolean whether to enable the fragment cache. You may use this property to turn on and off
     * the fragment cache according to specific setting (e.g. enable fragment cache only for GET requests).
     */
    public $enabled = true;
    /**
     * @var array a list of placeholders for embedding dynamic contents. This property
     * is used internally to implement the content caching feature. Do not modify it.
     */
    public $dynamicPlaceholders;

    /**
     * Initializes the FragmentCache object.
     */
    public function init()
    {
        parent::init();

        $this->cache = $this->enabled ? Instance::ensure($this->cache, Cache::className()) : null;

        if ($this->getCachedContent() === false) {
            $this->getView()->cacheStack[] = $this;
            ob_start();
            ob_implicit_flush(false);
        }
    }

    /**
     * Marks the end of content to be cached.
     * Content displayed before this method call and after [[init()]]
     * will be captured and saved in cache.
     * This method does nothing if valid content is already found in cache.
     */
    public function run()
    {
        if (($content = $this->getCachedContent()) !== false) {
            echo $content;
        } elseif ($this->cache instanceof Cache) {
            $content = ob_get_clean();
            array_pop($this->getView()->cacheStack);
            if (is_array($this->dependency)) {
                $this->dependency = Yii::createObject($this->dependency);
            }
            $data = [$content, $this->dynamicPlaceholders];
            $this->cache->set($this->calculateKey(), $data, $this->duration, $this->dependency);

            if (empty($this->getView()->cacheStack) && !empty($this->dynamicPlaceholders)) {
                $content = $this->updateDynamicContent($content, $this->dynamicPlaceholders);
            }
            echo $content;
        }
    }

    /**
     * @var string|boolean the cached content. False if the content is not cached.
     */
    private $_content;

    /**
     * Returns the cached content if available.
     * @return string|boolean the cached content. False is returned if valid content is not found in the cache.
     */
    public function getCachedContent()
    {
        if ($this->_content === null) {
            $this->_content = false;
            if ($this->cache instanceof Cache) {
                $key = $this->calculateKey();
                $data = $this->cache->get($key);
                if (is_array($data) && count($data) === 2) {
                    list ($content, $placeholders) = $data;
                    if (is_array($placeholders) && count($placeholders) > 0) {
                        if (empty($this->getView()->cacheStack)) {
                            // outermost cache: replace placeholder with dynamic content
                            $content = $this->updateDynamicContent($content, $placeholders);
                        }
                        foreach ($placeholders as $name => $statements) {
                            $this->getView()->addDynamicPlaceholder($name, $statements);
                        }
                    }
                    $this->_content = $content;
                }
            }
        }

        return $this->_content;
    }

    protected function updateDynamicContent($content, $placeholders)
    {
        foreach ($placeholders as $name => $statements) {
            $placeholders[$name] = $this->getView()->evaluateDynamicContent($statements);
        }

        return strtr($content, $placeholders);
    }

    /**
     * Generates a unique key used for storing the content in cache.
     * The key generated depends on both [[id]] and [[variations]].
     * @return mixed a valid cache key
     */
    protected function calculateKey()
    {
        $factors = [__CLASS__, $this->getId()];
        if (is_array($this->variations)) {
            foreach ($this->variations as $factor) {
                $factors[] = $factor;
            }
        }

        return $factors;
    }
}

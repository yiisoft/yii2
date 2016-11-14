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
 * FragmentCache is used by [[\yii\base\View]] to provide caching of page fragments.
 *
 * @property string|false $cachedContent The cached content. False is returned if valid content is not found
 * in the cache. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FragmentCache extends Widget
{
    /**
     * @var Cache|array|string the cache object or the application component ID of the cache object.
     * After the FragmentCache object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $cache = 'cache';
    /**
     * @var int number of seconds that the data can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     */
    public $duration = 60;
    /**
     * @var array|Dependency the dependency that the cached content depends on.
     * This can be either a [[Dependency]] object or a configuration array for creating the dependency object.
     * For example,
     *
     * ```php
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(updated_at) FROM post',
     * ]
     * ```
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
     * ```php
     * [
     *     Yii::$app->language,
     * ]
     * ```
     */
    public $variations;
    /**
     * @var bool whether to enable the fragment cache. You may use this property to turn on and off
     * the fragment cache according to specific setting (e.g. enable fragment cache only for GET requests).
     */
    public $enabled = true;
    /**
     * @var callable[]|string[] a list of placeholders for embedding dynamic contents via [[\yii\base\View::renderDynamic()]] method:
     *
     * ```php
     * [
     *     'commentsCount' => $model->getCommentsCount,
     *     'username' => function () {
     *         return Yii::$app->user->identity->name;
     *     }
     * ]
     * ```
     */
    public $placeholders;


    /**
     * Initializes the FragmentCache object.
     */
    public function init()
    {
        parent::init();

        $this->cache = $this->enabled ? Instance::ensure($this->cache, Cache::className()) : null;

        if ($this->cache instanceof Cache && $this->getCachedContent() === false) {
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
        if (($content = $this->getCachedContent()) === false) {
            if ($this->cache instanceof Cache) {
                array_pop($this->getView()->cacheStack);
                $content = ob_get_clean();
                if ($content === false || $content === '') {
                    return;
                }
                if (is_array($this->dependency)) {
                    $this->dependency = Yii::createObject($this->dependency);
                }
                $this->cache->set($this->calculateKey(), $content, $this->duration, $this->dependency);
            }
        }

        if (!empty($content)) {
            if (!empty($this->placeholders)) {
                $content = $this->updateDynamicContent($content);
            }
            echo $content;
        }
    }

    public static function placeholderMarker($name)
    {
        return "<![CDATA[YII-DYNAMIC-$name]]>";
    }

    public function hasPlaceholder($name)
    {
        return array_key_exists($name, $this->placeholders);
    }

    /**
     * @var string|bool the cached content. False if the content is not cached.
     */
    private $_content;

    /**
     * Returns the cached content if available.
     * @return string|false the cached content. False is returned if valid content is not found in the cache.
     */
    public function getCachedContent()
    {
        if ($this->_content === null) {
            $this->_content = false;
            if ($this->cache instanceof Cache) {
                if ($content = $this->cache->get($this->calculateKey())) {
                    $this->_content = $content;
                }
            }
        }

        return $this->_content;
    }

    /**
     * Replaces placeholders in content by results of evaluated dynamic statements.
     *
     * @param string $content
     * @return string final content
     */
    protected function updateDynamicContent($content)
    {
        $replace = [];
        foreach ($this->placeholders as $name => $value) {
            $replace[self::placeholderMarker($name)] = $this->evaluateDynamicContent($value);
        }
        return strtr($content, $replace);
    }

    /**
     * Evaluates dynamic statement.
     *
     * @param $value
     * @return string
     */
    protected function evaluateDynamicContent($value)
    {
        ob_start();
        ob_implicit_flush(false);
        if (is_object($value) && $value instanceof \Closure) {
            echo call_user_func($value);
        } else {
            echo $value;
        }
        return ob_get_clean();
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

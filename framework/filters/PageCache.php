<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\base\Action;
use yii\caching\Cache;
use yii\caching\Dependency;
use yii\di\Instance;
use yii\web\Response;

/**
 * PageCache implements server-side caching of whole pages.
 *
 * It is an action filter that can be added to a controller and handles the `beforeAction` event.
 *
 * To use PageCache, declare it in the `behaviors()` method of your controller class.
 * In the following example the filter will be applied to the `index` action and
 * cache the whole page for maximum 60 seconds or until the count of entries in the post table changes.
 * It also stores different versions of the page depending on the application language.
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'pageCache' => [
 *             'class' => 'yii\filters\PageCache',
 *             'only' => ['index'],
 *             'duration' => 60,
 *             'dependency' => [
 *                 'class' => 'yii\caching\DbDependency',
 *                 'sql' => 'SELECT COUNT(*) FROM post',
 *             ],
 *             'variations' => [
 *                 \Yii::$app->language,
 *             ]
 *         ],
 *     ];
 * }
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PageCache extends ActionFilter
{
    /**
     * @var boolean whether the content being cached should be differentiated according to the route.
     * A route consists of the requested controller ID and action ID. Defaults to true.
     */
    public $varyByRoute = true;
    /**
     * @var Cache|array|string the cache object or the application component ID of the cache object.
     * After the PageCache object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
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
     * ```php
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(updated_at) FROM post',
     * ]
     * ```
     *
     * would make the output cache depend on the last modified time of all posts.
     * If any post has its modification time changed, the cached content would be invalidated.
     *
     * If [[cacheCookies]] or [[cacheHeaders]] is enabled, then [[\yii\caching\Dependency::reusable]] should be enabled as well to save performance.
     * This is because the cookies and headers are currently stored separately from the actual page content, causing the dependency to be evaluated twice.
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
     * ~~~
     */
    public $variations;
    /**
     * @var boolean whether to enable the page cache. You may use this property to turn on and off
     * the page cache according to specific setting (e.g. enable page cache only for GET requests).
     */
    public $enabled = true;
    /**
     * @var \yii\base\View the view component to use for caching. If not set, the default application view component
     * [[\yii\web\Application::view]] will be used.
     */
    public $view;
    /**
     * @var boolean|array a boolean value indicating whether to cache all cookies, or an array of
     * cookie names indicating which cookies can be cached. Be very careful with caching cookies, because
     * it may leak sensitive or private data stored in cookies to unwanted users.
     * @since 2.0.4
     */
    public $cacheCookies = false;
    /**
     * @var boolean|array a boolean value indicating whether to cache all HTTP headers, or an array of
     * HTTP header names (case-insensitive) indicating which HTTP headers can be cached.
     * Note if your HTTP headers contain sensitive information, you should white-list which headers can be cached.
     * @since 2.0.4
     */
    public $cacheHeaders = true;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->view === null) {
            $this->view = Yii::$app->getView();
        }
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (!$this->enabled) {
            return true;
        }

        $this->cache = Instance::ensure($this->cache, Cache::className());

        if (is_array($this->dependency)) {
            $this->dependency = Yii::createObject($this->dependency);
        }

        $properties = [];
        foreach (['cache', 'duration', 'dependency', 'variations'] as $name) {
            $properties[$name] = $this->$name;
        }
        $id = $this->varyByRoute ? $action->getUniqueId() : __CLASS__;
        $response = Yii::$app->getResponse();
        ob_start();
        ob_implicit_flush(false);
        if ($this->view->beginCache($id, $properties)) {
            $response->on(Response::EVENT_AFTER_SEND, [$this, 'cacheResponse']);
            return true;
        } else {
            $data = $this->cache->get($this->calculateCacheKey());
            if (is_array($data)) {
                $this->restoreResponse($response, $data);
            }
            $response->content = ob_get_clean();
            return false;
        }
    }

    /**
     * Restores response properties from the given data
     * @param Response $response the response to be restored
     * @param array $data the response property data
     * @since 2.0.3
     */
    protected function restoreResponse($response, $data)
    {
        if (isset($data['format'])) {
            $response->format = $data['format'];
        }
        if (isset($data['version'])) {
            $response->version = $data['version'];
        }
        if (isset($data['statusCode'])) {
            $response->statusCode = $data['statusCode'];
        }
        if (isset($data['statusText'])) {
            $response->statusText = $data['statusText'];
        }
        if (isset($data['headers']) && is_array($data['headers'])) {
            $headers = $response->getHeaders()->toArray();
            $response->getHeaders()->fromArray(array_merge($data['headers'], $headers));
        }
        if (isset($data['cookies']) && is_array($data['cookies'])) {
            $cookies = $response->getCookies()->toArray();
            $response->getCookies()->fromArray(array_merge($data['cookies'], $cookies));
        }
    }

    /**
     * Caches response properties.
     * @since 2.0.3
     */
    public function cacheResponse()
    {
        $this->view->endCache();
        $response = Yii::$app->getResponse();
        $data = [
            'format' => $response->format,
            'version' => $response->version,
            'statusCode' => $response->statusCode,
            'statusText' => $response->statusText,
        ];
        if (!empty($this->cacheHeaders)) {
            $headers = $response->getHeaders()->toArray();
            if (is_array($this->cacheHeaders)) {
                $filtered = [];
                foreach ($this->cacheHeaders as $name) {
                    $name = strtolower($name);
                    if (isset($headers[$name])) {
                        $filtered[$name] = $headers[$name];
                    }
                }
                $headers = $filtered;
            }
            $data['headers'] = $headers;
        }
        if (!empty($this->cacheCookies)) {
            $cookies = $response->getCookies()->toArray();
            if (is_array($this->cacheCookies)) {
                $filtered = [];
                foreach ($this->cacheCookies as $name) {
                    if (isset($cookies[$name])) {
                        $filtered[$name] = $cookies[$name];
                    }
                }
                $cookies = $filtered;
            }
            $data['cookies'] = $cookies;
        }
        $this->cache->set($this->calculateCacheKey(), $data, $this->duration, $this->dependency);
        echo ob_get_clean();
    }

    /**
     * @return array the key used to cache response properties.
     * @since 2.0.3
     */
    protected function calculateCacheKey()
    {
        $key = [__CLASS__];
        if ($this->varyByRoute) {
            $key[] = Yii::$app->requestedRoute;
        }
        if (is_array($this->variations)) {
            foreach ($this->variations as $value) {
                $key[] = $value;
            }
        }
        return $key;
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\caching\CacheInterface;
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
 * ```php
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
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Sergey Makinen <sergey@makinen.ru>
 * @since 2.0
 */
class PageCache extends ActionFilter
{
    /**
     * @var bool whether the content being cached should be differentiated according to the route.
     * A route consists of the requested controller ID and action ID. Defaults to `true`.
     */
    public $varyByRoute = true;
    /**
     * @var CacheInterface|array|string the cache object or the application component ID of the cache object.
     * After the PageCache object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $cache = 'cache';
    /**
     * @var int number of seconds that the data can remain valid in cache.
     * Use `0` to indicate that the cached data will never expire.
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
     * ```php
     * [
     *     Yii::$app->language,
     * ]
     * ```
     */
    public $variations;
    /**
     * @var bool whether to enable the page cache. You may use this property to turn on and off
     * the page cache according to specific setting (e.g. enable page cache only for GET requests).
     */
    public $enabled = true;
    /**
     * @var \yii\base\View the view component to use for caching. If not set, the default application view component
     * [[\yii\web\Application::view]] will be used.
     */
    public $view;
    /**
     * @var bool|array a boolean value indicating whether to cache all cookies, or an array of
     * cookie names indicating which cookies can be cached. Be very careful with caching cookies, because
     * it may leak sensitive or private data stored in cookies to unwanted users.
     * @since 2.0.4
     */
    public $cacheCookies = false;
    /**
     * @var bool|array a boolean value indicating whether to cache all HTTP headers, or an array of
     * HTTP header names (case-insensitive) indicating which HTTP headers can be cached.
     * Note if your HTTP headers contain sensitive information, you should white-list which headers can be cached.
     * @since 2.0.4
     */
    public $cacheHeaders = true;
    /**
     * @var array a list of placeholders for embedding dynamic contents. This property
     * is used internally to implement the content caching feature. Do not modify it.
     * @internal
     * @since 2.0.11
     */
    public $dynamicPlaceholders;


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
     * @return bool whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (!$this->enabled) {
            return true;
        }

        $this->cache = Instance::ensure($this->cache, 'yii\caching\CacheInterface');

        if (is_array($this->dependency)) {
            $this->dependency = Yii::createObject($this->dependency);
        }

        $response = Yii::$app->getResponse();
        $data = $this->cache->get($this->calculateCacheKey());
        if (!is_array($data) || !isset($data['cacheVersion']) || $data['cacheVersion'] !== 1) {
            $this->view->cacheStack[] = $this;
            ob_start();
            ob_implicit_flush(false);
            $response->on(Response::EVENT_AFTER_SEND, [$this, 'cacheResponse']);
            Yii::trace('Valid page content is not found in the cache.', __METHOD__);
            return true;
        }

        $this->restoreResponse($response, $data);
        Yii::trace('Valid page content is found in the cache.', __METHOD__);
        return false;
    }

    /**
     * This method is invoked right before the response caching is to be started.
     * You may override this method to cancel caching by returning `false` or store an additional data
     * in a cache entry by returning an array instead of `true`.
     * @return bool|array whether to cache or not, return an array instead of `true` to store an additional data.
     * @since 2.0.11
     */
    public function beforeCacheResponse()
    {
        return true;
    }

    /**
     * This method is invoked right after the response restoring is finished (but before the response is sent).
     * You may override this method to do last-minute preparation before the response is sent.
     * @param array|null $data an array of an additional data stored in a cache entry or `null`.
     * @since 2.0.11
     */
    public function afterRestoreResponse($data)
    {
    }

    /**
     * Restores response properties from the given data.
     * @param Response $response the response to be restored.
     * @param array $data the response property data.
     * @since 2.0.3
     */
    protected function restoreResponse($response, $data)
    {
        foreach (['format', 'version', 'statusCode', 'statusText', 'content'] as $name) {
            $response->{$name} = $data[$name];
        }
        foreach (['headers', 'cookies'] as $name) {
            if (isset($data[$name]) && is_array($data[$name])) {
                $response->{$name}->fromArray(array_merge($data[$name], $response->{$name}->toArray()));
            }
        }
        if (!empty($data['dynamicPlaceholders']) && is_array($data['dynamicPlaceholders'])) {
            if (empty($this->view->cacheStack)) {
                // outermost cache: replace placeholder with dynamic content
                $response->content = $this->updateDynamicContent($response->content, $data['dynamicPlaceholders']);
            }
            foreach ($data['dynamicPlaceholders'] as $name => $statements) {
                $this->view->addDynamicPlaceholder($name, $statements);
            }
        }
        $this->afterRestoreResponse(isset($data['cacheData']) ? $data['cacheData'] : null);
    }

    /**
     * Caches response properties.
     * @since 2.0.3
     */
    public function cacheResponse()
    {
        array_pop($this->view->cacheStack);
        $beforeCacheResponseResult = $this->beforeCacheResponse();
        if ($beforeCacheResponseResult === false) {
            $content = ob_get_clean();
            if (empty($this->view->cacheStack) && !empty($this->dynamicPlaceholders)) {
                $content = $this->updateDynamicContent($content, $this->dynamicPlaceholders);
            }
            echo $content;
            return;
        }

        $response = Yii::$app->getResponse();
        $data = [
            'cacheVersion' => 1,
            'cacheData' => is_array($beforeCacheResponseResult) ? $beforeCacheResponseResult : null,
            'content' => ob_get_clean(),
        ];
        if ($data['content'] === false || $data['content'] === '') {
            return;
        }

        $data['dynamicPlaceholders'] = $this->dynamicPlaceholders;
        foreach (['format', 'version', 'statusCode', 'statusText'] as $name) {
            $data[$name] = $response->{$name};
        }
        $this->insertResponseCollectionIntoData($response, 'headers', $data);
        $this->insertResponseCollectionIntoData($response, 'cookies', $data);
        $this->cache->set($this->calculateCacheKey(), $data, $this->duration, $this->dependency);
        if (empty($this->view->cacheStack) && !empty($this->dynamicPlaceholders)) {
            $data['content'] = $this->updateDynamicContent($data['content'], $this->dynamicPlaceholders);
        }
        echo $data['content'];
    }

    /**
     * Inserts (or filters/ignores according to config) response headers/cookies into a cache data array.
     * @param Response $response the response.
     * @param string $collectionName currently it's `headers` or `cookies`.
     * @param array $data the cache data.
     */
    private function insertResponseCollectionIntoData(Response $response, $collectionName, array &$data)
    {
        $property = 'cache' . ucfirst($collectionName);
        if ($this->{$property} === false) {
            return;
        }

        $all = $response->{$collectionName}->toArray();
        if (is_array($this->{$property})) {
            $filtered = [];
            foreach ($this->{$property} as $name) {
                if ($collectionName === 'headers') {
                    $name = strtolower($name);
                }
                if (isset($all[$name])) {
                    $filtered[$name] = $all[$name];
                }
            }
            $all = $filtered;
        }
        $data[$collectionName] = $all;
    }

    /**
     * Replaces placeholders in content by results of evaluated dynamic statements.
     * @param string $content content to be parsed.
     * @param array $placeholders placeholders and their values.
     * @return string final content.
     * @since 2.0.11
     */
    protected function updateDynamicContent($content, $placeholders)
    {
        foreach ($placeholders as $name => $statements) {
            $placeholders[$name] = $this->view->evaluateDynamicContent($statements);
        }

        return strtr($content, $placeholders);
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

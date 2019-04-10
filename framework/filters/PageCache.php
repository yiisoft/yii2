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
use yii\base\DynamicContentAwareInterface;
use yii\base\DynamicContentAwareTrait;
use yii\caching\CacheInterface;
use yii\caching\Dependency;
use yii\di\Instance;
use yii\web\Response;

/**
 * PageCache 实现整个页面的服务器端缓存。
 *
 * 它是一个动作过滤器可以添加到控制器中并处理 `beforeAction` 事件。
 *
 * 要使用 PageCache，请在控制器类的 `behaviors()` 方法中声明它。
 * 在下面的示例中过滤器将应用于 `index` 操作
 * 并缓存整个页面最多60秒或者直到 POST 表中的条目数发生变化。
 * 它还根据应用程序语言存储不同版本的页面。
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
class PageCache extends ActionFilter implements DynamicContentAwareInterface
{
    use DynamicContentAwareTrait;

    /**
     * 页缓存版本，用于在缓存的数据格式更改时
     * 检测缓存值中的不兼容性。
     */
    const PAGE_CACHE_VERSION = 1;

    /**
     * @var bool 是否应根据路由区分要缓存的内容。
     * 路由请求的控制器 ID 和操作 ID 组成。默认值为`true`。
     */
    public $varyByRoute = true;
    /**
     * @var CacheInterface|array|string 缓存对象或缓存对象的应用程序组件 ID 。
     * 创建 PageCache 对象后，如果要更改此属性，
     * 您应该只用缓存对象来分配它。
     * 从版本 2.0.2 开始，这也可以是用于创建对象的配置数组。
     */
    public $cache = 'cache';
    /**
     * @var int 数据在缓存中保持有效的秒数。
     * 使用 `0` 指示缓存的数据永远不会过期。
     */
    public $duration = 60;
    /**
     * @var array|Dependency 缓存内容所依赖的依赖项。
     * 这可以是一个 [[Dependency]] 对象也可以是用于创建依赖项对象的配置数组。
     * 例如，
     *
     * ```php
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(updated_at) FROM post',
     * ]
     * ```
     *
     * 将使输出缓存取决于所有 POST 的上次修改时间。
     * 如果任何帖子的修改时间发生更改，则缓存的内容将无效。
     *
     * 如果启用 [[cacheCookies]] 或者 [[cacheHeaders]]，然后应该启用 [[\yii\caching\Dependency::reusable]] 节省性能。
     * 这是因为 cookies 和 headers 当前是与实际页面内容分开存储的，从而导致对依赖项进行两次计算。
     */
    public $dependency;
    /**
     * @var string[]|string 将导致缓存内容更改的因素列表。
     * 每个因素都是表示变体的字符串（例如语言，一个 GET 参数）。
     * 以下更改设置将根据
     * 当前应用程序语言将内容缓存到不同版本中：
     *
     * ```php
     * [
     *     Yii::$app->language,
     * ]
     * ```
     */
    public $variations;
    /**
     * @var bool 是否启用页面缓存。 您可以使用此属性根据
     * 特定设置打开和关闭页缓存（例如仅对 GET 请求启用页缓存）。
     */
    public $enabled = true;
    /**
     * @var \yii\base\View 用于缓存的视图组件。如果未设置，默认应用程序
     * [[\yii\web\Application::view]] 视图组件。
     */
    public $view;
    /**
     * @var bool|array 指示是否缓存所有 cookies 或数组的布尔值 cookies 名称
     * 该名称指示可缓存哪些 cookies。要非常小心地缓存 cookies，因为它
     * 可能泄漏存储在 cookies 中的敏感或私有数据给不需要的用户。
     * @since 2.0.4
     */
    public $cacheCookies = false;
    /**
     * @var bool|array 指示是否缓存所有 HTTP headers 的布尔值，或者一个
     * HTTP header 名称（大小写不敏感）数组指示可以缓存哪些 HTTP headers
     * 注意：如果您的 HTTP headers 包含敏感信息，你应该列出白名单哪些 headers 可以缓存。
     * @since 2.0.4
     */
    public $cacheHeaders = true;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->view === null) {
            $this->view = Yii::$app->getView();
        }
    }

    /**
     * 在执行操作之前调用此方法（在所有可能的筛选器之后）。
     * 您可以重写此方法来完成该操作的最后一刻做准备。
     * @param Action $action 要执行的行动。
     * @return bool 是否应继续执行这项行动。
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
        if (!is_array($data) || !isset($data['cacheVersion']) || $data['cacheVersion'] !== static::PAGE_CACHE_VERSION) {
            $this->view->pushDynamicContent($this);
            ob_start();
            ob_implicit_flush(false);
            $response->on(Response::EVENT_AFTER_SEND, [$this, 'cacheResponse']);
            Yii::debug('Valid page content is not found in the cache.', __METHOD__);
            return true;
        }

        $this->restoreResponse($response, $data);
        Yii::debug('Valid page content is found in the cache.', __METHOD__);
        return false;
    }

    /**
     * 在启动响应缓存之前调用此方法。
     * 您可以通过返回 `false` 来重写此方法以取消缓存也可以通过返回数组而
     * 不是 `true` 来将其他数据存储在缓存条目中。
     * @return bool|array 无论是否缓存，返回一个数组而不是 `true` 来存储其他数据。
     * @since 2.0.11
     */
    public function beforeCacheResponse()
    {
        return true;
    }

    /**
     * 此方法是在响应恢复完成后（但在响应发送之前）调用的。
     * 您可以重写此方法以便在发送响应之前进行最后一刻的准备。
     * @param array|null $data 存储在缓存条目或 `null` 中的附加数据的数组。
     * @since 2.0.11
     */
    public function afterRestoreResponse($data)
    {
    }

    /**
     * 从给定数据恢复响应属性。
     * @param Response $response 需要恢复的响应。
     * @param array $data 响应属性数据。
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
            $response->content = $this->updateDynamicContent($response->content, $data['dynamicPlaceholders'], true);
        }
        $this->afterRestoreResponse(isset($data['cacheData']) ? $data['cacheData'] : null);
    }

    /**
     * 缓存响应属性。
     * @since 2.0.3
     */
    public function cacheResponse()
    {
        $this->view->popDynamicContent();
        $beforeCacheResponseResult = $this->beforeCacheResponse();
        if ($beforeCacheResponseResult === false) {
            echo $this->updateDynamicContent(ob_get_clean(), $this->getDynamicPlaceholders());
            return;
        }

        $response = Yii::$app->getResponse();
        $data = [
            'cacheVersion' => static::PAGE_CACHE_VERSION,
            'cacheData' => is_array($beforeCacheResponseResult) ? $beforeCacheResponseResult : null,
            'content' => ob_get_clean(),
        ];
        if ($data['content'] === false || $data['content'] === '') {
            return;
        }

        $data['dynamicPlaceholders'] = $this->getDynamicPlaceholders();
        foreach (['format', 'version', 'statusCode', 'statusText'] as $name) {
            $data[$name] = $response->{$name};
        }
        $this->insertResponseCollectionIntoData($response, 'headers', $data);
        $this->insertResponseCollectionIntoData($response, 'cookies', $data);
        $this->cache->set($this->calculateCacheKey(), $data, $this->duration, $this->dependency);
        $data['content'] = $this->updateDynamicContent($data['content'], $this->getDynamicPlaceholders());
        echo $data['content'];
    }

    /**
     * 将响应 headers/cookies 插入（或过滤/根据配置忽略）到缓存数据数组中。
     * @param Response $response 响应。
     * @param string $collectionName 目前，它是 `headers` 或者 `cookies`。
     * @param array $data 缓存数据。
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
     * @return 数组用于缓存响应属性的键。
     * @since 2.0.3
     */
    protected function calculateCacheKey()
    {
        $key = [__CLASS__];
        if ($this->varyByRoute) {
            $key[] = Yii::$app->requestedRoute;
        }
        return array_merge($key, (array)$this->variations);
    }

    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        return $this->view;
    }
}

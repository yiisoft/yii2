<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\base\WidgetEvent;
use yii\caching\CacheInterface;
use yii\caching\Dependency;
use yii\di\Instance;

/**
 * Cacheable widget behavior 自动根据指定的缓存时长和缓存依赖缓存小部件的内容。
 *
 * 如果应用已经配置了 `cache` 组件，这个行为可以不用任何配置就可以直接使用。
 * 默认情况下，小部件内容的缓存时长为一分钟。
 *
 * 下面的例子是，如果没有任何后续 post 更新，那么将会无限时长地缓存 posts 小部件的内容。
 *
 * ```php
 * use yii\behaviors\CacheableWidgetBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => CacheableWidgetBehavior::className(),
 *             'cacheDuration' => 0,
 *             'cacheDependency' => [
 *                 'class' => 'yii\caching\DbDependency',
 *                 'sql' => 'SELECT MAX(updated_at) FROM posts',
 *             ],
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Nikolay Oleynikov <oleynikovny@mail.ru>
 * @since 2.0.14
 */
class CacheableWidgetBehavior extends Behavior
{
    /**
     * @var CacheInterface|string|array 缓存对象或者缓存组件的 ID
     * 或者是一个可以生成缓存对象的配置数组。
     * 默认是 `cache` 应用组件。
     */
    public $cache = 'cache';
    /**
     * @var int 以秒为单位的缓存时长。
     * 设置为 `0` 表名缓存的数据将永不过期。
     * 默认是 60 秒或者 1 分钟。
     */
    public $cacheDuration = 60;
    /**
     * @var Dependency|array|null 一个缓存依赖，或者是
     * 可以生成缓存依赖的配置数组，再或者 `null`，表示不需要缓存依赖。
     *
     * 比如，
     *
     * ```php
     * [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(updated_at) FROM posts',
     * ]
     * ```
     *
     * 上述配置是把小部件的缓存依赖上所有 posts 的最后更新时间。
     * 如果任何 post 的更新时间发生变化，那么之前小部件缓存的内容也就失效了。
     */
    public $cacheDependency;
    /**
     * @var string[]|string 一个字符串数组或者一个单独的字符串，
     * 它用来引起被缓存内容的变化（比如 一种应用语言，一个 GET 参数）。
     *
     * 下面的变化设置将会导致缓存的内容，
     * 可以根据当前应用语言的不同而产生不同版本的缓存：
     *
     * ```php
     * [
     *     Yii::$app->language,
     * ]
     * ```
     */
    public $cacheKeyVariations = [];
    /**
     * @var bool 是否开启缓存。
     * 可以根据指定的条件对小部件缓存进行开关控制。
     * 下面的配置是，如果传递了指定的 GET 参数，那么本次将会禁用小部件缓存。
     *
     * ```php
     * empty(Yii::$app->request->get('disable-caching'))
     * ```
     */
    public $cacheEnabled = true;


    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        parent::attach($owner);

        $this->initializeEventHandlers();
    }

    /**
     * 开始标记片段缓存的起始部分。如果小部件内容能够从缓存中读取，
     * 那么片段缓存将阻止属主小部件执行生成小部件内容的过程。
     *
     * @param WidgetEvent $event `Widget::EVENT_BEFORE_RUN` 事件。
     */
    public function beforeRun($event)
    {
        $cacheKey = $this->getCacheKey();
        $fragmentCacheConfiguration = $this->getFragmentCacheConfiguration();

        if (!$this->owner->view->beginCache($cacheKey, $fragmentCacheConfiguration)) {
            $event->isValid = false;
        }
    }

    /**
     * 输出小部件内容然后标记片段缓存的结束部分。
     *
     * @param WidgetEvent $event `Widget::EVENT_AFTER_RUN` 事件。
     */
    public function afterRun($event)
    {
        echo $event->result;
        $event->result = null;

        $this->owner->view->endCache();
    }

    /**
     * 初始化（绑定）小部件事件处理器
     */
    private function initializeEventHandlers()
    {
        $this->owner->on(Widget::EVENT_BEFORE_RUN, [$this, 'beforeRun']);
        $this->owner->on(Widget::EVENT_AFTER_RUN, [$this, 'afterRun']);
    }

    /**
     * 返回缓存对象
     *
     * @return CacheInterface 缓存对象。
     * @throws InvalidConfigException 如果缓存对象实例化过程发生异常
     */
    private function getCacheInstance()
    {
        $cacheInterface = 'yii\caching\CacheInterface';
        return Instance::ensure($this->cache, $cacheInterface);
    }

    /**
     * 返回缓存小部件内容的 key。
     *
     * @return string[] 一个表示缓存 key 的字符串数组。
     */
    private function getCacheKey()
    {
        // `$cacheKeyVariations` may be a `string` and needs to be cast to an `array`.
        $cacheKey = array_merge(
            (array)get_class($this->owner),
            (array)$this->cacheKeyVariations
        );

        return $cacheKey;
    }

    /**
     * 返回小部件使用片段缓存时的配置数组。
     *
     * @return array 一个片段缓存用到的配置数组。
     */
    private function getFragmentCacheConfiguration()
    {
        $cache = $this->getCacheInstance();
        $fragmentCacheConfiguration = [
            'cache' => $cache,
            'duration' => $this->cacheDuration,
            'dependency' => $this->cacheDependency,
            'enabled' => $this->cacheEnabled,
        ];

        return $fragmentCacheConfiguration;
    }
}

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
use yii\caching\Dependency;

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
     * @var string the application component ID of the [[\yii\caching\Cache|cache]] object.
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
     *     'sql' => 'SELECT MAX(updated_at) FROM post',
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
     * ~~~
     */
    public $variations;
    /**
     * @var boolean whether to enable the fragment cache. You may use this property to turn on and off
     * the fragment cache according to specific setting (e.g. enable fragment cache only for GET requests).
     */
    public $enabled = true;
    /**
     * @var \yii\base\View the view component to use for caching. If not set, the default application view component
     * [[\yii\web\Application::view]] will be used.
     */
    public $view;


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
        $properties = [];
        foreach (['cache', 'duration', 'dependency', 'variations', 'enabled'] as $name) {
            $properties[$name] = $this->$name;
        }
        $id = $this->varyByRoute ? $action->getUniqueId() : __CLASS__;
        ob_start();
        ob_implicit_flush(false);
        if ($this->view->beginCache($id, $properties)) {
            return true;
        } else {
            Yii::$app->getResponse()->content = ob_get_clean();
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        echo $result;
        $this->view->endCache();
        return ob_get_clean();
    }
}

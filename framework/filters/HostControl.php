<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;

/**
 * HostControl 提供对请求的主机名的简单控制。
 *
 * 此筛选器提供针对 ['host header' 攻击] 的保护(https://www.acunetix.com/vulnerabilities/web/host-header-attack),
 * 仅允许对指定的主机名执行操作。
 *
 * 应用程序配置示例：
 *
 * ```php
 * return [
 *     'as hostControl' => [
 *         'class' => 'yii\filters\HostControl',
 *         'allowedHosts' => [
 *             'example.com',
 *             '*.example.com',
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 *
 * 控制器配置示例：
 *
 * ```php
 * use yii\web\Controller;
 * use yii\filters\HostControl;
 *
 * class SiteController extends Controller
 * {
 *     public function behaviors()
 *     {
 *         return [
 *             'hostControl' => [
 *                 'class' => HostControl::className(),
 *                 'allowedHosts' => [
 *                     'example.com',
 *                     '*.example.com',
 *                 ],
 *             ],
 *         ];
 *     }
 *
 *     // ...
 * }
 * ```
 *
 * > Note: 限制允许的主机名的最佳方法是使用 Web 服务器的 “虚拟主机” 配置。
 * 仅当此配置不可用或不安全时才应使用此筛选器。
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.11
 */
class HostControl extends ActionFilter
{
    /**
     * @var array|\Closure|null 主机名列表，哪些是允许的。
     * 每个主机都可以指定为通配符模式。例如：
     *
     * ```php
     * [
     *     'example.com',
     *     '*.example.com',
     * ]
     * ```
     *
     * 此字段可指定为以下签名的 PHP 回调：
     *
     * ```php
     * function (\yii\base\Action $action) {
     *     //return array of strings
     * }
     * ```
     *
     * 其中 `$action` 是当前 [[\yii\base\Action|action]] 对象。
     *
     * 如果未设置此字段 - 将不执行任何主机名检查。
     */
    public $allowedHosts;
    /**
     * @var callable 如果当前主机与 [[allowedHosts]] 不匹配将调用的回调。
     * 如果未设置，将调用[[denyAccess()]]。
     *
     * 回调的签名应如下：
     *
     * ```php
     * function (\yii\base\Action $action)
     * ```
     *
     * 其中 `$action` 是当前 [[\yii\base\Action|action]] 对象。
     *
     * > Note: 在实现自己的主机拒绝处理时， 确保避免使用当前请求的
     * 主机名, 创建绝对 URL 链接、缓存页面部件等。
     */
    public $denyCallback;
    /**
     * @var string|null 回退主机信息 (例如。`http://www.yiiframework.com`) 使用 [[\yii\web\Request::$hostInfo|Request::$hostInfo]] 时无效。
     * 在调用 [[$denyCallback]] 之前，此值将替换 [[\yii\web\Request::$hostInfo|Request::$hostInfo]]
     * 以确保不会使用无效的主机进行进一步处理。您可以将其设置为 `null` 使 [[\yii\web\Request::$hostInfo|Request::$hostInfo]] 保持不变。
     * 默认值为空字符串（这将导致创建相对 URL 而不是绝对 URL ）。
     * @see \yii\web\Request::getHostInfo()
     */
    public $fallbackHostInfo = '';


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $allowedHosts = $this->allowedHosts;
        if ($allowedHosts instanceof \Closure) {
            $allowedHosts = call_user_func($allowedHosts, $action);
        }
        if ($allowedHosts === null) {
            return true;
        }

        if (!is_array($allowedHosts) && !$allowedHosts instanceof \Traversable) {
            $allowedHosts = (array) $allowedHosts;
        }

        $currentHost = Yii::$app->getRequest()->getHostName();

        foreach ($allowedHosts as $allowedHost) {
            if (StringHelper::matchWildcard($allowedHost, $currentHost)) {
                return true;
            }
        }

        // replace invalid host info to prevent using it in further processing
        if ($this->fallbackHostInfo !== null) {
            Yii::$app->getRequest()->setHostInfo($this->fallbackHostInfo);
        }

        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, $action);
        } else {
            $this->denyAccess($action);
        }

        return false;
    }

    /**
     * 拒绝访问。
     * 默认实现将立即显示 404 页，正在终止程序执行。
     * 您可以重写此方法，创建自己的拒绝访问处理程序。在执行此操作时，请确保
     * 避免使用当前请求的主机名，创建绝对 URL 链接，缓存页面部件等。
     * @param \yii\base\Action $action 要执行的操作。
     * @throws NotFoundHttpException
     */
    protected function denyAccess($action)
    {
        $exception = new NotFoundHttpException(Yii::t('yii', 'Page not found.'));

        // use regular error handling if $this->fallbackHostInfo was set
        if (!empty(Yii::$app->getRequest()->hostName)) {
            throw $exception;
        }

        $response = Yii::$app->getResponse();
        $errorHandler = Yii::$app->getErrorHandler();

        $response->setStatusCode($exception->statusCode, $exception->getMessage());
        $response->data = $errorHandler->renderFile($errorHandler->errorView, ['exception' => $exception]);
        $response->send();

        Yii::$app->end();
    }
}

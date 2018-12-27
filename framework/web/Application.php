<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidRouteException;
use yii\helpers\Url;

/**
 * Application 是所有 Web 应用程序类的基类。
 *
 * 关于 Application 的更多使用参考，请查看 [应用主体指南](guide:structure-applications)。
 *
 * @property ErrorHandler $errorHandler 错误处理组件。此属性是只读的。
 * @property string $homeUrl 主页的 URL.
 * @property Request $request 请求组件。此属性是只读的。
 * @property Response $response 响应组件。此属性是只读的。
 * @property Session $session 会话组件。此属性是只读的。
 * @property User $user 用户组件。此属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
    /**
     * @var string 此 Application 的默认路由。默认为 'site'.
     */
    public $defaultRoute = 'site';
    /**
     * @var array 配置：指定控制器来处理所有的用户请求。
     * 这主要在应用程序处于维护模式时使用，
     * 通过一个控制器动作来处理所有传入的请求。
     * 此配置是一个数组，其第一个元素指定控制器动作的路径。
     * 其余的数组元素（键值对）指定此动作要绑定的参数
     * 例如，
     *
     * ```php
     * [
     *     'offline/notice',
     *     'param1' => 'value1',
     *     'param2' => 'value2',
     * ]
     * ```
     *
     * 默认为 null，表示不使用 catch-all 功能。
     */
    public $catchAll;
    /**
     * @var Controller 当前活动的控制器实例
     */
    public $controller;


    /**
     * {@inheritdoc}
     */
    protected function bootstrap()
    {
        $request = $this->getRequest();
        Yii::setAlias('@webroot', dirname($request->getScriptFile()));
        Yii::setAlias('@web', $request->getBaseUrl());

        parent::bootstrap();
    }

    /**
     * 处理特定的请求。
     * @param Request $request 处理的请求
     * @return Response 生成的响应
     * @throws NotFoundHttpException 如果请求的路由无效
     */
    public function handleRequest($request)
    {
        if (empty($this->catchAll)) {
            try {
                list($route, $params) = $request->resolve();
            } catch (UrlNormalizerRedirectException $e) {
                $url = $e->url;
                if (is_array($url)) {
                    if (isset($url[0])) {
                        // ensure the route is absolute
                        $url[0] = '/' . ltrim($url[0], '/');
                    }
                    $url += $request->getQueryParams();
                }

                return $this->getResponse()->redirect(Url::to($url, $e->scheme), $e->statusCode);
            }
        } else {
            $route = $this->catchAll[0];
            $params = $this->catchAll;
            unset($params[0]);
        }
        try {
            Yii::debug("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            }

            $response = $this->getResponse();
            if ($result !== null) {
                $response->data = $result;
            }

            return $response;
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'), $e->getCode(), $e);
        }
    }

    private $_homeUrl;

    /**
     * @return string 返回主页的 URL
     */
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            if ($this->getUrlManager()->showScriptName) {
                return $this->getRequest()->getScriptUrl();
            }

            return $this->getRequest()->getBaseUrl() . '/';
        }

        return $this->_homeUrl;
    }

    /**
     * @param string $value 设置的主页 URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    /**
     * 返回错误处理组件。
     * @return ErrorHandler 错误处理组件。
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * 返回请求组件。
     * @return Request 请求组件。
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * 返回响应组件。
     * @return Response 响应组件。
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * 返回会话组件。
     * @return Session 会话组件。
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * 返回用户组件。
     * @return User 用户组件。
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * {@inheritdoc}
     */
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'yii\web\Request'],
            'response' => ['class' => 'yii\web\Response'],
            'session' => ['class' => 'yii\web\Session'],
            'user' => ['class' => 'yii\web\User'],
            'errorHandler' => ['class' => 'yii\web\ErrorHandler'],
        ]);
    }
}

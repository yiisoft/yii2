<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidRouteException;

/**
 * Application is the base class for all web application classes.
 *
 * @property string $homeUrl The homepage URL.
 * @property Session $session The session component. This property is read-only.
 * @property User $user The user component. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
    /**
     * @var string the default route of this application. Defaults to 'site'.
     */
    public $defaultRoute = 'site';
    /**
     * @var array the configuration specifying a controller action which should handle
     * all user requests. This is mainly used when the application is in maintenance mode
     * and needs to handle all incoming requests via a single action.
     * The configuration is an array whose first element specifies the route of the action.
     * The rest of the array elements (key-value pairs) specify the parameters to be bound
     * to the action. For example,
     *
     * ~~~
     * [
     *     'offline/notice',
     *     'param1' => 'value1',
     *     'param2' => 'value2',
     * ]
     * ~~~
     *
     * Defaults to null, meaning catch-all is not used.
     */
    public $catchAll;
    /**
     * @var Controller the currently active controller instance
     */
    public $controller;


    /**
     * @inheritdoc
     */
    protected function bootstrap()
    {
        $request = $this->getRequest();
        Yii::setAlias('@webroot', dirname($request->getScriptFile()));
        Yii::setAlias('@web', $request->getBaseUrl());

        parent::bootstrap();
    }

    /**
     * Handles the specified request.
     * @param Request $request the request to be handled
     * @return Response the resulting response
     * @throws NotFoundHttpException if the requested route is invalid
     */
    public function handleRequest($request)
    {
        if (empty($this->catchAll)) {
            list ($route, $params) = $request->resolve();
        } else {
            $route = $this->catchAll[0];
            $params = array_splice($this->catchAll, 1);
        }
        try {
            Yii::trace("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            } else {
                $response = $this->getResponse();
                if ($result !== null) {
                    $response->data = $result;
                }

                return $response;
            }
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private $_homeUrl;

    /**
     * @return string the homepage URL
     */
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            if ($this->getUrlManager()->showScriptName) {
                return $this->getRequest()->getScriptUrl();
            } else {
                return $this->getRequest()->getBaseUrl() . '/';
            }
        } else {
            return $this->_homeUrl;
        }
    }

    /**
     * @param string $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    /**
     * Returns the session component.
     * @return Session the session component.
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * Returns the user component.
     * @return User the user component.
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * @inheritdoc
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

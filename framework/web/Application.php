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
 * Application is the base class for all web application classes.
 *
 * For more details and usage information on Application, see the [guide article on applications](guide:structure-applications).
 *
 * @property ErrorHandler $errorHandler The error handler application component. This property is read-only.
 * @property string $homeUrl The homepage URL.
 * @property Request $request The request component. This property is read-only.
 * @property Response $response The response component. This property is read-only.
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
     * ```php
     * [
     *     'offline/notice',
     *     'param1' => 'value1',
     *     'param2' => 'value2',
     * ]
     * ```
     *
     * Defaults to null, meaning catch-all is not used.
     */
    public $catchAll;
    /**
     * @var Controller the currently active controller instance
     */
    public $controller;
    /**
     * Stack of middleware
     * @var array
     */
    protected $middlewareStack = [];

    /**
     * Add middleware to stack
     * @param $middleware callable|string|array
     * Array format
     * ```php
     * [
     *  'middleware' => 'className', //class name or closure
     *  'priority' => 1, //integer value, middleware will be executed in desc order (default null)
     *  'only' => ['index', 'view'], // middleware will be executed for only this actions (default for all actions)
     *  'except' => ['update'], // middleware will not be executed for this actions (default empty array)
     * ]
     * ```
     * @throws MiddlewareException
     */
    public function addMiddleware($middleware)
    {
        if (is_array($middleware)) {
            if (!isset($middleware['middleware'])) {
                throw new MiddlewareException("Param `middleware` must be set");
            }
            $middleware = array_merge([
                'priority' => null,
                'only' => [],
                'except' => []
            ], $middleware);
            $this->middlewareStack[] = $middleware;
        } elseif (is_callable($middleware)) {
            $this->middlewareStack[] = [
                'middleware' => $middleware,
                'priority' => null,
                'only' => [],
                'except' => []
            ];
        } elseif (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new MiddlewareException("Class {$middleware} not found");
            }
            $this->middlewareStack[] = [
                'middleware' => $middleware,
                'priority' => null,
                'only' => [],
                'except' => []
            ];
        } else {
            throw new MiddlewareException("Middleware must be class name or callable or array. See documentation");
        }
    }

    /**
     * Return middleware stack
     * @return array
     */
    protected function getMiddleware()
    {
        return $this->middlewareStack;
    }

    public function __construct(array $config = [])
    {
        if (isset($config['middleware'])) {
            foreach ($config['middleware'] as $middleware) {
                $this->addMiddleware($middleware);
            }
        }
        unset($config['middleware']);
        parent::__construct($config);
    }

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
            try {
                [$route, $params, $middleware] = $request->resolve();
                if ($middleware !== null) {
                    foreach ($middleware as $m) {
                        $this->addMiddleware($m);
                    }
                }
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
            $response = $this->getResponse();
            Yii::debug("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            }

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
     * @param string $route
     * @return array|bool
     */
    public function createController($route)
    {
        $parts = parent::createController($route);
        if (is_array($parts)) {
            [$controller, $action] = $parts;
            if (method_exists($controller, 'middleware')) {
                foreach ($controller->middleware() as $middleware) {
                    $this->addMiddleware($middleware);
                }
            }
            $this->callMiddlewareStack($action);
        }
        return $parts;
    }

    /**
     * Sort middleware by priority and execute
     *
     * @param $action string|null
     * @throws MiddlewareException
     */
    protected function callMiddlewareStack($action)
    {
        usort($this->middlewareStack, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return $a['priority'] > $b['priority'] ? -1 : 1;
        });

        foreach ($this->middlewareStack as $middleware) {

            if (in_array($action, (array)$middleware['except'])) {
                continue;
            }
            if (count($middleware['only'])>0 && !in_array($action, $middleware['only'])) {
                continue;
            }

            if (is_callable($middleware['middleware'])) {
                Yii::debug("Execute middleware closure");
                $middleware['middleware']($this->getRequest(), $this->getResponse());
            } elseif (is_string($middleware['middleware'])) {
                $object = new $middleware['middleware'];
                $className = get_class($object);
                if (!$object instanceof MiddlewareInterface) {
                    throw new MiddlewareException("Class {$className} must be implemented by yii\web\MiddlewareInterface");
                }
                Yii::debug("Execute middleware class: {$className}");
                $object->process($this->getRequest(), $this->getResponse());
            }
        }
    }

    /**
     * @return string the homepage URL
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
     * @param string $value the homepage URL
     */
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    /**
     * Returns the error handler component.
     * @return ErrorHandler the error handler application component.
     */
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    /**
     * Returns the request component.
     * @return Request the request component.
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Returns the response component.
     * @return Response the response component.
     */
    public function getResponse()
    {
        return $this->get('response');
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
            'request' => ['class' => Request::class],
            'response' => ['class' => Response::class],
            'session' => ['class' => Session::class],
            'user' => ['class' => User::class],
            'errorHandler' => ['class' => ErrorHandler::class],
        ]);
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http\server;

use Yii;
use yii\base\Component;
use yii\base\MiddlewareDispatcherInterface;

/**
 * MiddlewareDispatcher handlers the middleware defined by PSR-15 'HTTP Server Request Handlers'.
 * This dispatcher should be used for HTTP processing application, e.g. web application.
 *
 * This dispatcher requires "http-interop/http-server-middleware" library to be installed. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist "http-interop/http-server-middleware:~1.0.0"
 * ```
 *
 * @see https://github.com/php-fig/fig-standards/tree/master/proposed/http-handlers
 * @see \Psr\Http\Server\MiddlewareInterface
 * @see \Psr\Http\Server\RequestHandlerInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class MiddlewareDispatcher extends Component implements MiddlewareDispatcherInterface
{
    /**
     * {@inheritdoc}
     * @return \Psr\Http\Message\ResponseInterface response instance.
     */
    public function dispatch($request, array $middleware, $handler)
    {
        if (empty($middleware)) {
            return call_user_func($handler, $request);
        }

        /* @var $middlewareInstance \Psr\Http\Server\MiddlewareInterface */
        $middlewareInstance = array_shift($middleware);
        if (!is_object($middlewareInstance) || $middlewareInstance instanceof \Closure) {
            $middlewareInstance = Yii::createObject($middlewareInstance);
        }

        $newHandler = new CallbackRequestHandler([
            'callback' => function ($request) use ($middleware, $handler) {
                return $this->dispatch($request, $middleware, $handler);
            }
        ]);

        return $middlewareInstance->process($request, $newHandler);
    }
}
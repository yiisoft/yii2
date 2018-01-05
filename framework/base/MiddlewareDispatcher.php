<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * MiddlewareDispatcher is a general middleware dispatcher, which can be used for any type of application.
 * This dispatcher is not bound to any particular request or response interfaces, using just a notation instead.
 * Each middleware should provide method `process()` of following signature:
 *
 * ```php
 * public function process(object $request, callable $handler) : object
 * {
 *     //return response instance.
 * }
 * ```
 *
 * where `$request` is an application request instance and `$handler` is a PHP callback, which provide default (final)
 * request processing.
 *
 * However, you can use [[MiddlewareInterface]] for middleware class interface definition.
 *
 * This dispatcher is mainly used for the console application or in case you are using old middleware libraries.
 * For the web application you should consider usage of [[\yii\http\server\MiddlewareDispatcher]] instead.
 *
 * @see \yii\http\server\MiddlewareDispatcher
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class MiddlewareDispatcher extends Component implements MiddlewareDispatcherInterface
{
    /**
     * {@inheritdoc}
     */
    public function dispatch($request, array $middleware, $handler)
    {
        if (empty($middleware)) {
            return call_user_func($handler, $request);
        }

        /* @var $middlewareInstance MiddlewareInterface */
        $middlewareInstance = array_shift($middleware);
        if (!is_object($middlewareInstance) || $middlewareInstance instanceof \Closure) {
            $middlewareInstance = Yii::createObject($middlewareInstance);
        }

        $newHandler = function ($request) use ($middleware, $handler) {
            return $this->dispatch($request, $middleware, $handler);
        };

        return $middlewareInstance->process($request, $newHandler);
    }
}
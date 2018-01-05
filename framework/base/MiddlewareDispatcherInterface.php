<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * MiddlewareDispatcherInterface defines the application component, which should handle middleware processing.
 * This component handles multiple middleware composed into a stack.
 *
 * Middleware dispatcher is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->getMiddlewareDispatcher()`.
 *
 * Usage example:
 *
 * ```php
 * $response = Yii::$app->getMiddlewareDispatcher()->dispatch(
 *     Yii::$app->getRequest(),
 *     [
 *         FirstMiddleware::class,
 *         [
 *             'class' => SecondMiddleware::class,
 *             'some' => 'foo',
 *         ],
 *         function () {
 *             $middleware = new ThirdMiddleware();
 *             $middleware->setSome('foo');
 *             return $middleware;
 *         },
 *         // ...
 *     ],
 *     function ($request) {
 *         return Yii::$app->getResponse();
 *     }
 * );
 * ```
 *
 * @see MiddlewareDispatcher
 * @see \yii\http\server\MiddlewareDispatcher
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
interface MiddlewareDispatcherInterface
{
    /**
     * Passes application request instance to request handler via middleware stack.
     * Particular middleware can be specified as an object or its DI compatible configuration.
     * @param object $request application request instance.
     * @param array $middleware middleware stack.
     * @param callable $handler final request handler.
     * @return object response instance.
     */
    public function dispatch($request, array $middleware, $handler);
}
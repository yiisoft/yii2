<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * CallbackMiddleware wraps arbitrary PHP callback into object matching [[MiddlewareInterface]].
 * Usage example:
 *
 * ```php
 * $middleware = new CallbackMiddleware([
 *     'callback' => function ($request, $handler) {
 *         if ($request->getParams() === []) {
 *             return new Response();
 *         }
 *         return $handler($request);
 *     }
 * ]);
 * $response = $middleware->process(Yii::$app->getRequest(), function ($request) {
 *     return Yii::$app->handleRequest($request);
 * });
 * ```
 *
 * @see MiddlewareInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class CallbackMiddleware extends BaseObject implements MiddlewareInterface
{
    /**
     * @var callable a PHP callback matching signature of [[MiddlewareInterface::process()]].
     */
    public $callback;


    /**
     * {@inheritdoc}
     */
    public function process($request, $handler)
    {
        return call_user_func($this->callback, $request, $handler);
    }
}
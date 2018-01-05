<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http\server;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use yii\base\BaseObject;

/**
 * CallbackMiddleware wraps arbitrary PHP callback into object matching [[MiddlewareInterface]].
 * Usage example:
 *
 * ```php
 * $handler = new AnotherPsrCompatibleRequestHandler();
 *
 * $middleware = new CallbackMiddleware([
 *     'callback' => function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
 *         if ($request->getMethod() === 'HEAD') {
 *             return new Response();
 *         }
 *         return $handler->handle($request);
 *     }
 * ]);
 * $response = $middleware->process(Yii::$app->getRequest(), $handler);
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func($this->callback, $request, $handler);
    }
}
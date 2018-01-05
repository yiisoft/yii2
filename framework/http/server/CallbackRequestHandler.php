<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http\server;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use yii\base\BaseObject;

/**
 * CallbackRequestHandler wraps arbitrary PHP callback into object matching [[RequestHandlerInterface]].
 * Usage example:
 *
 * ```php
 * $handler = new CallbackRequestHandler([
 *     'callback' => function (ServerRequestInterface $request) {
 *          return new Response();
 *     }
 * ]);
 *
 * $middleware = new SomePsrCompatibleMiddleware();
 * $response = $middleware->process(Yii::$app->getRequest(), $handler);
 * ```
 *
 * @see RequestHandlerInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class CallbackRequestHandler extends BaseObject implements RequestHandlerInterface
{
    /**
     * @var callable a PHP callback matching signature of [[RequestHandlerInterface::handle()]].
     */
    public $callback;


    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func($this->callback, $request);
    }

    /**
     * Runs the request handler.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$response = $object($request);`.
     *
     * This method duplicates `handle()` providing compatibility with non-PSR 15 middleware.
     * @param ServerRequestInterface $request request instance.
     * @return ResponseInterface response instance.
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->handle($request);
    }
}
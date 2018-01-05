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
use yii\di\Instance;

/**
 * MiddlewareRequestHandler
 *
 * @property MiddlewareInterface $middleware
 * @property RequestHandlerInterface $handler
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
class MiddlewareRequestHandler extends BaseObject implements RequestHandlerInterface
{
    /**
     * @var MiddlewareInterface|array|string
     */
    private $_middleware;
    /**
     * @var RequestHandlerInterface|array|string
     */
    private $_handler;


    /**
     * @return MiddlewareInterface|array|string
     */
    public function getMiddleware()
    {
        if (!$this->_middleware instanceof MiddlewareInterface) {
            $this->_middleware = Instance::ensure($this->_middleware, MiddlewareInterface::class);
        }
        return $this->_middleware;
    }

    /**
     * @param MiddlewareInterface|array|string $middleware
     */
    public function setMiddleware($middleware)
    {
        $this->_middleware = $middleware;
    }

    /**
     * @return RequestHandlerInterface|array|string
     */
    public function getHandler()
    {
        if (!$this->_handler instanceof RequestHandlerInterface) {
            $this->_handler = Instance::ensure($this->_handler, RequestHandlerInterface::class);
        }
        return $this->_handler;
    }

    /**
     * @param RequestHandlerInterface|array|string $handler
     */
    public function setHandler($handler)
    {
        $this->_handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getMiddleware()->process($request, $this->getHandler());
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
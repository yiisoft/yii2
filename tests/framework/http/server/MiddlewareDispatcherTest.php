<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\http\server;

use Psr\Http\Server\RequestHandlerInterface;
use yii\http\server\CallbackMiddleware;
use yii\http\server\MiddlewareDispatcher;
use yii\web\Request;
use yii\web\Response;
use yiiunit\TestCase;

class MiddlewareDispatcherTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testDispatch()
    {
        $dispatcher = new MiddlewareDispatcher();
        $finalHandler = function (Request $request) {
            return (new Response())->withHeader('final', 'callback-final');
        };

        // Handle at first :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return (new Response())
                        ->withHeader('first', 'callback-1');
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return (new Response())->withHeader('second', 'callback-2');
                }]),
            ],
            $finalHandler
        );

        $expectedHeaders = [
            'first' => [
                'callback-1'
            ]
        ];
        $this->assertSame($expectedHeaders, $response->getHeaders());

        // Handle at second :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return (new Response())->withHeader('second', 'callback-2');
                }]),
            ],
            $finalHandler
        );

        $expectedHeaders = [
            'second' => [
                'callback-2'
            ]
        ];
        $this->assertSame($expectedHeaders, $response->getHeaders());

        // Passing to final :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                }]),
            ],
            $finalHandler
        );

        $expectedHeaders = [
            'final' => [
                'callback-final'
            ]
        ];
        $this->assertSame($expectedHeaders, $response->getHeaders());

        // Wrapping :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request)
                        ->withHeader('first', 'callback-1');
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request)
                        ->withHeader('second', 'callback-2');
                }]),
            ],
            $finalHandler
        );

        $expectedHeaders = [
            'final' => [
                'callback-final'
            ],
            'second' => [
                'callback-2'
            ],
            'first' => [
                'callback-1'
            ],
        ];
        $this->assertSame($expectedHeaders, $response->getHeaders());

        // Using callable syntax :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    return $handler($request);
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    return $handler($request);
                }]),
            ],
            $finalHandler
        );

        $expectedHeaders = [
            'final' => [
                'callback-final'
            ]
        ];
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }
}
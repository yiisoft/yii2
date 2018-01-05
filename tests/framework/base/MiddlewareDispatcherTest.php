<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\CallbackMiddleware;
use yii\base\MiddlewareDispatcher;
use yii\console\Request;
use yii\console\Response;
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
            $response = new Response();
            $response->exitStatus = 'final';
            return $response;
        };

        // Handle at first :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    $response = new Response();
                    $response->exitStatus = 'first';
                    return $response;
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    $response = new Response();
                    $response->exitStatus = 'second';
                    return $response;
                }]),
            ],
            $finalHandler
        );
        $this->assertSame('first', $response->exitStatus);

        // Handle at second :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    return $handler($request);
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    $response = new Response();
                    $response->exitStatus = 'second';
                    return $response;
                }]),
            ],
            $finalHandler
        );
        $this->assertSame('second', $response->exitStatus);

        // Passing to final :

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
        $this->assertSame('final', $response->exitStatus);

        // Wrapping :

        $response = $dispatcher->dispatch(
            new Request(),
            [
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    $response = $handler($request);
                    $response->exitStatus .= '.first';
                    return $response;
                }]),
                new CallbackMiddleware(['callback' => function (Request $request, $handler) {
                    $response = $handler($request);
                    $response->exitStatus .= '.second';
                    return $response;
                }]),
            ],
            $finalHandler
        );
        $this->assertSame('final.second.first', $response->exitStatus);
    }
}
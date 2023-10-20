<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use Yii;
use yii\console\controllers\ServeController;
use yiiunit\TestCase;

/**
 * Unit test for [[\yii\console\controllers\ServeController]].
 * @see ServeController
 *
 * @group console
 */
class ServeControllerTest extends TestCase
{
    public function setUp()
    {
        $this->mockApplication();
    }

    public function testActionIndex()
    {
        if (!\function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl_fork() is not available');
        }

        if (!\function_exists('posix_kill')) {
            $this->markTestSkipped('posix_kill() is not available');
        }

        if (!\function_exists('pcntl_waitpid')) {
            $this->markTestSkipped('pcntl_waitpid() is not available');
        }

        $controller = new ServeController('serve', Yii::$app);
        $controller->docroot = __DIR__ . '/stub';
        $controller->port = 8080;

        $pid = \pcntl_fork();

        if ($pid == 0) {
            \ob_start();
            $controller->actionIndex('localhost');
            \ob_get_clean();
            exit();
        }

        \sleep(1);

        $response = \file_get_contents('http://localhost:8080');

        $this->assertEquals('Hello!', $response);

        \posix_kill($pid, \SIGTERM);
        \pcntl_waitpid($pid, $status);
    }

    public function testActionIndexWithRouter()
    {
        if (!\function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl_fork() is not available');
        }

        if (!\function_exists('posix_kill')) {
            $this->markTestSkipped('posix_kill() is not available');
        }

        if (!\function_exists('pcntl_waitpid')) {
            $this->markTestSkipped('pcntl_waitpid() is not available');
        }

        $controller = new ServeController('serve', Yii::$app);
        $controller->docroot = __DIR__ . '/stub';
        $controller->port = 8081;
        $controller->router = __DIR__ . '/stub/index.php';

        $pid = \pcntl_fork();

        if ($pid == 0) {
            \ob_start();
            $controller->actionIndex('localhost');
            \ob_get_clean();
            exit();
        }

        \sleep(1);

        $response = \file_get_contents('http://localhost:8081');

        $this->assertEquals('Hello!', $response);

        \posix_kill($pid, \SIGTERM);
        \pcntl_waitpid($pid, $status);
    }
}

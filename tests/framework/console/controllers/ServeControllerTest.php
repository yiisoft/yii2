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
    protected function setUp(): void
    {
        $this->mockApplication();
    }

    public function testAddressTaken(): void
    {
        $docroot = __DIR__ . '/stub';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['isAddressTaken', 'runCommand'])
            ->getMock();

        $serveController->expects($this->once())->method('isAddressTaken')->willReturn(true);
        $serveController->expects($this->never())->method('runCommand');

        $serveController->docroot = $docroot;
        $serveController->port = 8080;

        ob_start();
        $exitCode = $serveController->actionIndex('localhost:8080');
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertSame(ServeController::EXIT_CODE_ADDRESS_TAKEN_BY_ANOTHER_PROCESS, $exitCode);
        $this->assertStringContainsString('http://localhost:8080 is taken by another process.', $result);
    }

    public function testDefaultValues(): void
    {
        $docroot = __DIR__ . '/stub';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;
        $serveController->port = 8080;

        $serveController->expects($this->once())->method('runCommand')->willReturn(true);

        ob_start();
        $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertStringContainsString('Server started on http://localhost:8080', $result);
        $this->assertStringContainsString("Document root is \"{$docroot}\"", $result);
        $this->assertStringContainsString('Quit the server with CTRL-C or COMMAND-C.', $result);
    }

    public function testDoocRootWithNoExistValue(): void
    {
        $docroot = '/not/exist/path';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;

        $serveController->expects($this->never())->method('runCommand');

        ob_start();
        $exitCode = $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertSame(ServeController::EXIT_CODE_NO_DOCUMENT_ROOT, $exitCode);
        $this->assertStringContainsString("Document root \"{$docroot}\" does not exist.", $result);
    }

    public function testWithRouterNoExistValue(): void
    {
        $docroot = __DIR__ . '/stub';
        $router = '/not/exist/path';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;
        $serveController->port = 8081;
        $serveController->router = $router;

        $serveController->expects($this->never())->method('runCommand');

        ob_start();
        $exitCode = $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertSame(ServeController::EXIT_CODE_NO_ROUTING_FILE, $exitCode);
        $this->assertStringContainsString("Routing file \"$router\" does not exist.", $result);
    }

    public function testWithRouterValue(): void
    {
        $docroot = __DIR__ . '/stub';
        $router = __DIR__ . '/stub/index.php';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;
        $serveController->port = 8081;
        $serveController->router = $router;

        $serveController->expects($this->once())->method('runCommand')->willReturn(true);

        ob_start();
        $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertStringContainsString('Server started on http://localhost:8081', $result);
        $this->assertStringContainsString("Document root is \"{$docroot}\"", $result);
        $this->assertStringContainsString("Routing file is \"{$router}\"", $result);
        $this->assertStringContainsString('Quit the server with CTRL-C or COMMAND-C.', $result);
    }

    public function testOptionsReturnsExpectedKeys(): void
    {
        $controller = new ServeControllerMock('serve', Yii::$app);
        $options = $controller->options('index');

        $this->assertContains('docroot', $options);
        $this->assertContains('router', $options);
        $this->assertContains('port', $options);
        $this->assertContains('interactive', $options);
        $this->assertContains('color', $options);
    }

    public function testOptionAliases(): void
    {
        $controller = new ServeControllerMock('serve', Yii::$app);
        $aliases = $controller->optionAliases();

        $this->assertSame('docroot', $aliases['t']);
        $this->assertSame('port', $aliases['p']);
        $this->assertSame('router', $aliases['r']);
    }

    public function testAddressWithPortUsesPortFromAddress(): void
    {
        $docroot = __DIR__ . '/stub';

        $serveController = $this->getMockBuilder(ServeControllerMock::class)
            ->setConstructorArgs(['serve', Yii::$app])
            ->onlyMethods(['isAddressTaken', 'runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;
        $serveController->port = 8080;

        $serveController->method('isAddressTaken')->willReturn(false);
        $serveController->expects($this->once())->method('runCommand');

        ob_start();
        $serveController->actionIndex('localhost:9090');
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();
        $this->assertStringContainsString('http://localhost:9090/', $result);
        $this->assertStringNotContainsString('8080', $result);
    }
}

/**
 * Mock class for [[\yii\console\controllers\ServeController]].
 */
class ServeControllerMock extends ServeController
{
    use StdOutBufferControllerTrait;
}

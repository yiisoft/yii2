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

    public function testAddressTaken()
    {
        $docroot = __DIR__ . '/stub';

        /** @var ServeController $serveController */
        $serveController = $this->getMockBuilder(ServeControllerMocK::className())
            ->setConstructorArgs(['serve', Yii::$app])
            ->setMethods(['isAddressTaken', 'runCommand'])
            ->getMock();

        $serveController->expects($this->once())->method('isAddressTaken')->willReturn(true);
        $serveController->expects($this->never())->method('runCommand');

        $serveController->docroot = $docroot;
        $serveController->port = 8080;

        ob_start();
        $serveController->actionIndex('localhost:8080');
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertStringContainsString('http://localhost:8080 is taken by another process.', $result);
    }

    public function testDefaultValues()
    {
        $docroot = __DIR__ . '/stub';

        /** @var ServeController $serveController */
        $serveController = $this->getMockBuilder(ServeControllerMock::className())
            ->setConstructorArgs(['serve', Yii::$app])
            ->setMethods(['runCommand'])
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

    public function testDoocRootWithNoExistValue()
    {
        $docroot = '/not/exist/path';

        /** @var ServeController $serveController */
        $serveController = $this->getMockBuilder(ServeControllerMock::className())
            ->setConstructorArgs(['serve', Yii::$app])
            ->setMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;

        $serveController->expects($this->any())->method('runCommand')->willReturn(true);

        ob_start();
        $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertStringContainsString("Document root \"{$docroot}\" does not exist.", $result);
    }

    public function testWithRouterNoExistValue()
    {
        $docroot = __DIR__ . '/stub';
        $router = '/not/exist/path';

        /** @var ServeController $serveController */
        $serveController = $this->getMockBuilder(ServeControllerMock::className())
            ->setConstructorArgs(['serve', Yii::$app])
            ->setMethods(['runCommand'])
            ->getMock();

        $serveController->docroot = $docroot;
        $serveController->port = 8081;
        $serveController->router = $router;

        $serveController->expects($this->any())->method('runCommand')->willReturn(true);

        ob_start();
        $serveController->actionIndex();
        ob_end_clean();

        $result = $serveController->flushStdOutBuffer();

        $this->assertStringContainsString("Routing file \"$router\" does not exist.", $result);
    }

    public function testWithRouterValue()
    {
        $docroot = __DIR__ . '/stub';
        $router = __DIR__ . '/stub/index.php';

        /** @var ServeController $serveController */
        $serveController = $this->getMockBuilder(ServeControllerMock::className())
            ->setConstructorArgs(['serve', Yii::$app])
            ->setMethods(['runCommand'])
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
}

/**
 * Mock class for [[\yii\console\controllers\ServeController]].
 */
class ServeControllerMock extends ServeController
{
    use StdOutBufferControllerTrait;
}

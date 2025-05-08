<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use RuntimeException;
use yii\console\Exception;
use yiiunit\framework\console\stubs\DummyService;
use Yii;
use yii\base\InlineAction;
use yii\base\Module;
use yii\console\Application;
use yii\console\Request;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * @group console
 */
class ControllerTest extends TestCase
{
    /** @var FakeController */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
        Yii::$app->controllerMap = [
            'fake' => 'yiiunit\framework\console\FakeController',
            'fake_witout_output' => 'yiiunit\framework\console\FakeHelpControllerWithoutOutput',
            'help' => 'yiiunit\framework\console\FakeHelpController',
        ];
    }

    public function testBindArrayToActionParams()
    {
        $controller = new FakeController('fake', Yii::$app);

        $params = ['test' => []];
        $this->assertEquals([], $controller->runAction('aksi4', $params));
        $this->assertEquals([], $controller->runAction('aksi4', $params));
    }

    public function testBindActionParams()
    {
        $controller = new FakeController('fake', Yii::$app);

        $params = ['from params'];
        list($fromParam, $other) = $controller->run('aksi1', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('default', $other);

        $params = ['from params', 'notdefault'];
        list($fromParam, $other) = $controller->run('aksi1', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['d426,mdmunir', 'single'];
        $result = $controller->runAction('aksi2', $params);
        $this->assertEquals([['d426', 'mdmunir'], 'single'], $result);

        $params = ['', 'single'];
        $result = $controller->runAction('aksi2', $params);
        $this->assertEquals([[], 'single'], $result);

        $params = ['_aliases' => ['t' => 'test']];
        $result = $controller->runAction('aksi4', $params);
        $this->assertEquals('test', $result);

        $params = ['_aliases' => ['a' => 'testAlias']];
        $result = $controller->runAction('aksi5', $params);
        $this->assertEquals('testAlias', $result);

        $params = ['_aliases' => ['ta' => 'from params,notdefault']];
        list($fromParam, $other) = $controller->runAction('aksi6', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['test-array' => 'from params,notdefault'];
        list($fromParam, $other) = $controller->runAction('aksi6', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['from params', 'notdefault'];
        list($fromParam, $other) = $controller->run('trimargs', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['a', 'b', 'c1', 'c2', 'c3'];
        [$a, $b, $c] = $controller->run('variadic', $params);
        $this->assertEquals('a', $a);
        $this->assertEquals('b', $b);
        $this->assertEquals(['c1', 'c2', 'c3'], $c);

        $params = ['avaliable'];
        $message = Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', ['missing'])]);
        $this->expectException('yii\console\Exception');
        $this->expectExceptionMessage($message);
        $result = $controller->runAction('aksi3', $params);

    }

    public function testNullableInjectedActionParams()
    {
        // Use the PHP71 controller for this test
        $this->controller = new FakePhp71Controller('fake', new Application([
            'id' => 'app',
            'basePath' => __DIR__,
        ]));
        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionNullableInjection');
        $params = [];
        $args = $this->controller->bindActionParams($injectionAction, $params);
        $this->assertEquals(\Yii::$app->request, $args[0]);
        $this->assertNull($args[1]);
    }

    public function testInjectionContainerException()
    {
        // Use the PHP71 controller for this test
        $this->controller = new FakePhp71Controller('fake', new Application([
            'id' => 'app',
            'basePath' => __DIR__,
        ]));
        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        \Yii::$container->set(DummyService::className(), function() { throw new \RuntimeException('uh oh'); });

        $this->expectException(get_class(new RuntimeException()));
        $this->expectExceptionMessage('uh oh');
        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testUnknownInjection()
    {
        // Use the PHP71 controller for this test
        $this->controller = new FakePhp71Controller('fake', new Application([
            'id' => 'app',
            'basePath' => __DIR__,
        ]));
        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        \Yii::$container->clear(DummyService::className());
        $this->expectException(get_class(new Exception()));
        $this->expectExceptionMessage('Could not load required service: dummyService');
        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testInjectedActionParams()
    {
        // Use the PHP71 controller for this test
        $this->controller = new FakePhp71Controller('fake', new Application([
            'id' => 'app',
            'basePath' => __DIR__,
        ]));
        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        \Yii::$container->set(DummyService::className(), DummyService::className());
        $args = $this->controller->bindActionParams($injectionAction, $params);
        $this->assertEquals($params['before'], $args[0]);
        $this->assertEquals(\Yii::$app->request, $args[1]);
        $this->assertEquals('Component: yii\console\Request $request', \Yii::$app->requestedParams['request']);
        $this->assertEquals($params['between'], $args[2]);
        $this->assertInstanceOf(DummyService::className(), $args[3]);
        $this->assertEquals('Container DI: yiiunit\framework\console\stubs\DummyService $dummyService', \Yii::$app->requestedParams['dummyService']);
        $this->assertNull($args[4]);
        $this->assertEquals('Unavailable service: post', \Yii::$app->requestedParams['post']);
        $this->assertEquals($params['after'], $args[5]);
    }

    public function testInjectedActionParamsFromModule()
    {
        $module = new \yii\base\Module('fake', new Application([
            'id' => 'app',
            'basePath' => __DIR__,
        ]));
        $module->set('yii\data\DataProviderInterface', [
            'class' => \yii\data\ArrayDataProvider::className(),
        ]);
        // Use the PHP71 controller for this test
        $this->controller = new FakePhp71Controller('fake', $module);
        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionModuleServiceInjection');
        $args = $this->controller->bindActionParams($injectionAction, []);
        $this->assertInstanceOf(\yii\data\ArrayDataProvider::className(), $args[0]);
        $this->assertEquals('Module yii\base\Module DI: yii\data\DataProviderInterface $dataProvider', \Yii::$app->requestedParams['dataProvider']);
    }

    public function assertResponseStatus($status, $response)
    {
        $this->assertInstanceOf('yii\console\Response', $response);
        $this->assertSame($status, $response->exitStatus);
    }

    public function runRequest($route, $args = 0)
    {
        $request = new Request();
        $request->setParams(func_get_args());
        return Yii::$app->handleRequest($request);
    }

    public function testResponse()
    {
        $status = 123;

        $response = $this->runRequest('fake/status');
        $this->assertResponseStatus(0, $response);

        $response = $this->runRequest('fake/status', (string)$status);
        $this->assertResponseStatus($status, $response);

        $response = $this->runRequest('fake/response');
        $this->assertResponseStatus(0, $response);

        $response = $this->runRequest('fake/response', (string)$status);
        $this->assertResponseStatus($status, $response);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12028
     */
    public function testHelpOptionNotSet()
    {
        $controller = new FakeController('posts', Yii::$app);
        $controller->runAction('index');

        $this->assertTrue(FakeController::getWasActionIndexCalled());
        $this->assertNull(FakeHelpController::getActionIndexLastCallParams());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12028
     */
    public function testHelpOption()
    {
        $controller = new FakeController('posts', Yii::$app);
        $controller->help = true;
        $controller->runAction('index');

        $this->assertFalse(FakeController::getWasActionIndexCalled());
        $this->assertEquals(FakeHelpController::getActionIndexLastCallParams(), ['posts/index']);

        $helpController = new FakeHelpControllerWithoutOutput('help', Yii::$app);
        $helpController->actionIndex('fake/aksi1');
        $this->assertStringContainsString('--test-array, -ta', $helpController->outputString);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13071
     */
    public function testHelpOptionWithModule()
    {
        $controller = new FakeController('posts', new Module('news'));
        $controller->help = true;
        $controller->runAction('index');

        $this->assertFalse(FakeController::getWasActionIndexCalled());
        $this->assertEquals(FakeHelpController::getActionIndexLastCallParams(), ['news/posts/index']);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/19028
     */
    public function testGetActionArgsHelp()
    {
        $controller = new FakeController('fake', Yii::$app);
        $help = $controller->getActionArgsHelp($controller->createAction('aksi2'));

        $this->assertArrayHasKey('values', $help);
        if (PHP_MAJOR_VERSION > 5) {
            // declared type
            $this->assertEquals('array', $help['values']['type']);
        } else {
            $this->markTestSkipped('Can not test declared type of parameter $values on PHP < 7.0');
        }
        $this->assertArrayHasKey('value', $help);
        // PHPDoc type
        $this->assertEquals('string', $help['value']['type']);
    }

    public function testGetActionHelpSummaryOnNull()
    {
        $controller = new FakeController('fake', Yii::$app);

        $controller->color = false;
        $helpSummary = $controller->getActionHelpSummary(null);
        $this->assertEquals('Action not found.', $helpSummary);

        $controller->color = true;
        $helpSummary = $controller->getActionHelpSummary(null);
        $this->assertEquals($controller->ansiFormat('Action not found.', Console::FG_RED), $helpSummary);
    }
}

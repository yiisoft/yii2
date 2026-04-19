<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console;

use PHPUnit\Framework\Attributes\Group;
use ReflectionMethod;
use ReflectionType;
use yii\data\ArrayDataProvider;
use RuntimeException;
use Yii;
use yii\base\InlineAction;
use yii\base\Module;
use yii\console\Application;
use yii\console\Exception;
use yii\console\Request;
use yii\console\Response;
use yii\helpers\Console;
use yiiunit\framework\console\stubs\DummyService;
use yiiunit\TestCase;

/**
 * Unit test for {@see \yii\console\Controller}.
 */
#[Group('console')]
#[Group('controller')]
class ControllerTest extends TestCase
{
    private FakeController|FakeInjectionController $controller;

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

    public function testBindArrayToActionParams(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $params = ['test' => []];
        $this->assertEquals([], $controller->runAction('aksi4', $params));
        $this->assertEquals([], $controller->runAction('aksi4', $params));
    }

    public function testBindActionParams(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $params = ['from params'];
        [$fromParam, $other] = $controller->run('aksi1', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('default', $other);

        $params = ['from params', 'notdefault'];
        [$fromParam, $other] = $controller->run('aksi1', $params);
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
        [$fromParam, $other] = $controller->runAction('aksi6', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['test-array' => 'from params,notdefault'];
        [$fromParam, $other] = $controller->runAction('aksi6', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['from params', 'notdefault'];
        [$fromParam, $other] = $controller->run('trimargs', $params);
        $this->assertEquals('from params', $fromParam);
        $this->assertEquals('notdefault', $other);

        $params = ['a', 'b', 'c1', 'c2', 'c3'];
        [$a, $b, $c] = $controller->run('variadic', $params);
        $this->assertEquals('a', $a);
        $this->assertEquals('b', $b);
        $this->assertEquals(['c1', 'c2', 'c3'], $c);

        $params = ['avaliable'];
        $message = Yii::t('yii', 'Missing required arguments: {params}', ['params' => implode(', ', ['missing'])]);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);
        $result = $controller->runAction('aksi3', $params);
    }

    public function testNullableInjectedActionParams(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                ],
            ),
        );

        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionNullableInjection');
        $params = [];
        $args = $this->controller->bindActionParams($injectionAction, $params);
        $this->assertEquals(Yii::$app->request, $args[0]);
        $this->assertNull($args[1]);
    }

    public function testInjectionContainerException(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                ],
            ),
        );

        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        Yii::$container->set(DummyService::class, function () {
            throw new RuntimeException('uh oh');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('uh oh');
        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testUnknownInjection(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                ],
            ),
        );

        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        Yii::$container->clear(DummyService::class);
        $this->expectException(get_class(new Exception()));
        $this->expectExceptionMessage('Could not load required service: dummyService');
        $this->controller->bindActionParams($injectionAction, $params);
    }

    public function testInjectedActionParams(): void
    {
        $this->controller = new FakeInjectionController(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                ],
            ),
        );

        $this->mockApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        Yii::$container->set(DummyService::class, DummyService::class);
        $args = $this->controller->bindActionParams($injectionAction, $params);
        $this->assertEquals($params['before'], $args[0]);
        $this->assertEquals(Yii::$app->request, $args[1]);
        $this->assertEquals('Component: yii\console\Request $request', Yii::$app->requestedParams['request']);
        $this->assertEquals($params['between'], $args[2]);
        $this->assertInstanceOf(DummyService::class, $args[3]);
        $this->assertEquals('Container DI: yiiunit\framework\console\stubs\DummyService $dummyService', Yii::$app->requestedParams['dummyService']);
        $this->assertNull($args[4]);
        $this->assertEquals('Unavailable service: post', Yii::$app->requestedParams['post']);
        $this->assertEquals($params['after'], $args[5]);
    }

    public function testInjectedActionParamsFromModule(): void
    {
        $module = new Module(
            'fake',
            new Application(
                [
                    'id' => 'app',
                    'basePath' => __DIR__,
                ],
            ),
        );

        $module->set(
            'yii\data\DataProviderInterface',
            ['class' => ArrayDataProvider::class],
        );

        $this->controller = new FakeInjectionController('fake', $module);
        $this->mockWebApplication(['controller' => $this->controller]);

        $injectionAction = new InlineAction('injection', $this->controller, 'actionModuleServiceInjection');
        $args = $this->controller->bindActionParams($injectionAction, []);
        $this->assertInstanceOf(ArrayDataProvider::class, $args[0]);
        $this->assertEquals('Module yii\base\Module DI: yii\data\DataProviderInterface $dataProvider', Yii::$app->requestedParams['dataProvider']);
    }

    public function assertResponseStatus($status, $response): void
    {
        $this->assertInstanceOf('yii\console\Response', $response);
        $this->assertSame($status, $response->exitStatus);
    }

    public function runRequest($route, $args = 0): Response
    {
        $request = new Request();
        $request->setParams(func_get_args());
        return Yii::$app->handleRequest($request);
    }

    public function testResponse(): void
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
    public function testHelpOptionNotSet(): void
    {
        $controller = new FakeController('posts', Yii::$app);
        $controller->runAction('index');

        $this->assertTrue(FakeController::getWasActionIndexCalled());
        $this->assertNull(FakeHelpController::getActionIndexLastCallParams());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/12028
     */
    public function testHelpOption(): void
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
    public function testHelpOptionWithModule(): void
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
    public function testGetActionArgsHelp(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('aksi2'));

        self::assertArrayHasKey(
            'values',
            $help,
            "Expected help to expose the 'values' argument.",
        );
        self::assertSame(
            'array',
            $help['values']['type'],
            "Declared 'array' type should be reported for the 'values' argument.",
        );
        self::assertArrayHasKey(
            'value',
            $help,
            "Expected help to expose the 'value' argument.",
        );
        // PHPDoc type
        self::assertSame(
            'string',
            $help['value']['type'],
            "PHPDoc 'string' type should be reported for the 'value' argument.",
        );
    }

    public function testGetActionArgsHelpWithUnionType(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('union-type'));

        self::assertArrayHasKey(
            'param',
            $help,
            "Expected help to expose the 'param' argument for the union-typed action.",
        );
        self::assertSame(
            'string|int',
            $help['param']['type'],
            "Union type 'string|int' should be rendered for the 'param' argument.",
        );
        self::assertTrue(
            $help['param']['required'],
            'Argument without a default value must be reported as required.',
        );
    }

    public function testGetActionArgsHelpWithIntersectionType(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('intersection-type'));

        self::assertArrayHasKey(
            'param',
            $help,
            "Expected help to expose the 'param' argument for the intersection-typed action.",
        );
        self::assertSame(
            'Countable&Iterator',
            $help['param']['type'],
            "Intersection type 'Countable&Iterator' should be rendered for the 'param' argument.",
        );
        self::assertTrue(
            $help['param']['required'],
            'Argument without a default value must be reported as required.',
        );
    }

    public function testGetActionArgsHelpWithDnfType(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('dnf-type'));

        self::assertArrayHasKey(
            'param',
            $help,
            "Expected help to expose the 'param' argument for the DNF-typed action.",
        );
        self::assertSame(
            '(Countable&Iterator)|null',
            $help['param']['type'],
            "DNF type '(Countable&Iterator)|null' should be rendered with parenthesized intersection.",
        );
        self::assertFalse(
            $help['param']['required'],
            'Argument with a default value must be reported as optional.',
        );
    }

    public function testGetActionArgsHelpWithNullableType(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('nullable-type'));

        self::assertArrayHasKey(
            'param',
            $help,
            "Expected help to expose the 'param' argument for the nullable-typed action.",
        );
        self::assertSame(
            'int',
            $help['param']['type'],
            "Nullable named type '?int' should render as 'int' (matching 'ReflectionNamedType::getName()').",
        );
        self::assertFalse(
            $help['param']['required'],
            'Argument with a default value must be reported as optional.',
        );
    }

    public function testGetActionArgsHelpWithUnionNullableType(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $help = $controller->getActionArgsHelp($controller->createAction('union-nullable-type'));

        self::assertArrayHasKey(
            'param',
            $help,
            "Expected help to expose the 'param' argument for the union-nullable-typed action.",
        );
        self::assertSame(
            'string|int|null',
            $help['param']['type'],
            "Union type with 'null' should render every member including 'null'.",
        );
        self::assertFalse(
            $help['param']['required'],
            'Argument with a default value must be reported as optional.',
        );
    }

    public function testStringifyReflectionTypeWithUnknownSubclass(): void
    {
        $controller = new FakeController('fake', Yii::$app);

        $mockType = $this->createMock(ReflectionType::class);

        $mockType->method('__toString')->willReturn('unknown');

        $method = new ReflectionMethod($controller, 'stringifyReflectionType');

        self::assertSame(
            'unknown',
            $method->invoke($controller, $mockType),
            "Unknown 'ReflectionType' subclasses should fall back to string casting.",
        );
    }

    public function testGetActionHelpSummaryOnNull(): void
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

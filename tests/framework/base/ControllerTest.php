<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\Controller;
use yii\base\Exception;
use yii\base\InlineAction;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yii\data\ArrayDataProvider;
use yii\data\DataProviderInterface;
use yii\web\Cookie;
use yiiunit\framework\base\provider\ControllerProvider;
use yiiunit\framework\base\stub\controller\ExternalAction;
use yiiunit\framework\base\stub\controller\InjectingController;
use yiiunit\framework\base\stub\controller\MappedActionController;
use yiiunit\framework\base\stub\controller\RoutableModule;
use yiiunit\framework\base\stub\controller\ServiceStub;
use yiiunit\framework\base\stub\controller\TestController;
use yiiunit\framework\base\stub\controller\VetoingModule;
use yiiunit\TestCase;

/**
 * Unit test for {@see Controller}.
 *
 * {@see ControllerProvider} for test case data providers.
 */
#[Group('base')]
class ControllerTest extends TestCase
{
    public static $actionRuns = [];

    public function testRunAction(): void
    {
        $this->mockApplication();

        static::$actionRuns = [];

        $controller = new TestController('test-controller', Yii::$app);

        self::assertNull($controller->action, 'Controller must start without an active action.');

        $result = $controller->runAction('test1');

        self::assertSame(
            'test1',
            $result,
            'Action result must propagate.',
        );
        self::assertSame(
            ['test-controller/test1'],
            static::$actionRuns,
            'Action body must run once.',
        );
        self::assertNotNull(
            $controller->action,
            'Active action must be retained after dispatch.',
        );
        self::assertSame(
            'test1',
            $controller->action->id,
            'Active action ID must reflect last invocation.',
        );
        self::assertSame(
            'test-controller/test1',
            $controller->action->uniqueId,
            'Active action unique ID must include the controller segment.',
        );

        $result = $controller->runAction('test2');

        self::assertSame(
            'test2',
            $result,
            'Second action result must propagate.',
        );
        self::assertSame(
            [
                'test-controller/test1',
                'test-controller/test2',
            ],
            static::$actionRuns,
            'Both action bodies must be recorded in order.',
        );
        self::assertNotNull(
            $controller->action,
            'Active action must be retained after second dispatch.',
        );
        self::assertSame(
            'test1',
            $controller->action->id,
            'Active action ID must roll back to the outer action.',
        );
        self::assertSame(
            'test-controller/test1',
            $controller->action->uniqueId,
            'Active unique ID must roll back to the outer action.',
        );
    }

    #[DataProviderExternal(ControllerProvider::class, 'inlineAction')]
    public function testCreateInlineAction(
        string $controllerClass,
        string $actionId,
        string|null $expectedActionMethod = null,
    ): void {
        $this->mockApplication();

        /** @var Controller $controller */
        $controller = new $controllerClass('test-controller', Yii::$app);

        /** @var InlineAction|null $action */
        $action = $controller->createAction($actionId);

        $actionMethod = $action !== null ? $action->actionMethod : null;

        self::assertSame(
            $expectedActionMethod,
            $actionMethod,
            'Resolved action method must match expectation.',
        );
    }

    #[DataProviderExternal(ControllerProvider::class, 'actionIdMatcher')]
    public function testActionIdMethod(string $input, int $expected): void
    {
        self::assertSame(
            $expected,
            preg_match('/^(?:[a-z0-9_]+-)*[a-z0-9_]+$/', $input),
            'Action ID regex must match expectation.',
        );
    }

    public function testCreateActionFallsBackToDefaultActionWhenIdIsEmpty(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $controller->defaultAction = 'test1';

        $action = $controller->createAction('');

        self::assertNotNull(
            $action,
            "'defaultAction' must produce an action.",
        );
        self::assertSame(
            'test1',
            $action->id,
            'ID must match defaultAction.',
        );
    }

    public function testCreateActionResolvesExternalActionFromActionMap(): void
    {
        $this->mockApplication();

        $controller = new MappedActionController('mapped', Yii::$app);

        $action = $controller->createAction('external');

        self::assertInstanceOf(
            ExternalAction::class,
            $action,
            'Mapped class must be instantiated.',
        );
    }

    public function testRunActionShortCircuitsWhenAncestorModuleVetoesBeforeAction(): void
    {
        $this->mockApplication();

        $module = new VetoingModule('veto', Yii::$app);
        $controller = new TestController('test-controller', $module);

        $result = $controller->runAction('test1');

        self::assertNull(
            $result,
            'Veto must short-circuit dispatch.',
        );
        self::assertSame(
            [],
            $module->afterActionsCalled,
            "'afterAction' must not fire after veto.",
        );
    }

    public function testRunRoutesPlainIdToRunAction(): void
    {
        $this->mockApplication();

        static::$actionRuns = [];

        $controller = new TestController('test-controller', Yii::$app);

        $result = $controller->run('test1');

        self::assertSame(
            'test1',
            $result,
            'Local action result must propagate.',
        );
    }

    public function testRunRoutesNestedRouteThroughModule(): void
    {
        $this->mockApplication();

        static::$actionRuns = [];

        $module = new RoutableModule('routable', Yii::$app);
        $controller = new TestController('test-controller', $module);

        $result = $controller->run('test-controller/test1');

        self::assertSame(
            'test1',
            $result,
            'Module-level action result must propagate.',
        );
    }

    public function testRunRoutesAbsoluteRouteThroughApplication(): void
    {
        $this->mockApplication(
            [
                'controllerMap' => [
                    'test-controller' => TestController::class,
                ],
            ],
        );

        static::$actionRuns = [];

        $module = new RoutableModule('routable', Yii::$app);
        $controller = new TestController('test-controller', $module);

        $controller->run('/test-controller/test1');

        self::assertSame(
            ['test-controller/test1'],
            static::$actionRuns,
            'Application-level action must execute.',
        );
    }

    public function testGetModulesReturnsAncestorChainOutermostFirst(): void
    {
        $this->mockApplication();

        $outer = new Module('outer', Yii::$app);
        $inner = new Module('inner', $outer);
        $controller = new TestController('test-controller', $inner);

        $modules = $controller->getModules();

        self::assertSame(
            [
                Yii::$app,
                $outer,
                $inner,
            ],
            $modules,
            'Order: outermost first.',
        );
    }

    public function testGetRouteIncludesActionUniqueIdWhenActionIsSet(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);
        $controller->action = new InlineAction('test1', $controller, 'actionTest1');

        self::assertSame(
            'test-controller/test1',
            $controller->getRoute(),
            'Route must include the action segment.',
        );
    }

    public function testRenderPartialDelegatesToView(): void
    {
        $this->mockApplication(
            [
                'aliases' => [
                    '@viewsBase' => __DIR__ . '/stub/views',
                ],
            ],
        );

        $controller = new TestController('test-controller', Yii::$app);

        $output = $controller->renderPartial('@viewsBase/hello.php', ['name' => 'World']);

        self::assertSame(
            'Hello World',
            $output,
            'Output must reflect the supplied parameters.',
        );
    }

    public function testRenderFileDelegatesToView(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $output = $controller->renderFile(
            __DIR__ . '/stub/views/hello.php',
            ['name' => 'Yii'],
        );

        self::assertSame(
            'Hello Yii',
            $output,
            'Output must reflect the supplied parameters.',
        );
    }

    public function testSetViewReplacesViewInstance(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);
        $newView = new \yii\base\View();

        $controller->setView($newView);

        self::assertSame(
            $newView,
            $controller->getView(),
            'View instance must persist.',
        );
    }

    public function testSetViewPathResolvesAlias(): void
    {
        $this->mockApplication(
            [
                'aliases' => [
                    '@customViews' => __DIR__ . '/stub/views',
                ],
            ],
        );

        $controller = new TestController('test-controller', Yii::$app);

        $controller->setViewPath('@customViews');

        self::assertSame(
            __DIR__ . '/stub/views',
            $controller->getViewPath(),
            'Alias must be resolved.',
        );
    }

    public function testFindLayoutFileWalksAncestorsForLayout(): void
    {
        $this->mockApplication();

        Yii::$app->layout = null;

        $outer = new Module('outer', Yii::$app);

        $outer->setBasePath(__DIR__ . '/stub');

        $outer->layout = 'main';

        $inner = new Module('inner', $outer);
        $controller = new TestController('test-controller', $inner);

        $controller->layout = null;

        $file = $controller->findLayoutFile($controller->getView());

        self::assertSame(
            __DIR__ . '/stub/views/layouts/main.php',
            $file,
            'Ancestor layout must resolve to a path.',
        );
    }

    public function testFindLayoutFileResolvesAliasLayout(): void
    {
        $this->mockApplication(
            [
                'aliases' => [
                    '@layoutDir' => __DIR__ . '/stub/views/layouts',
                ],
            ],
        );

        $controller = new TestController('test-controller', Yii::$app);

        $controller->layout = '@layoutDir/main';

        $file = $controller->findLayoutFile($controller->getView());

        self::assertSame(
            __DIR__ . '/stub/views/layouts/main.php',
            $file,
            'Alias-based layout must resolve.',
        );
    }

    public function testFindLayoutFileResolvesAbsoluteLayoutAgainstApplicationLayoutPath(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $controller->layout = '/main';

        $file = $controller->findLayoutFile($controller->getView());

        self::assertStringEndsWith(
            '/views/layouts/main.php',
            $file,
            'Absolute layout must resolve under the application path.',
        );
    }

    public function testFindLayoutFileKeepsExplicitExtension(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $controller->layout = '/main.tpl';

        $file = $controller->findLayoutFile($controller->getView());

        self::assertStringEndsWith(
            'main.tpl',
            $file,
            'Explicit extension must be preserved.',
        );
    }

    public function testFindLayoutFileFallsBackToPhpExtensionWhenDefaultExtensionFileMissing(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $controller->layout = '/main';

        $view = $controller->getView();

        $view->defaultExtension = 'tpl';

        $file = $controller->findLayoutFile($view);

        self::assertStringEndsWith(
            'main.php',
            $file,
            "Fallback extension must be '.php'.",
        );
    }

    public function testFindLayoutFileReturnsFalseWhenNoAncestorDeclaresLayout(): void
    {
        $this->mockApplication();

        Yii::$app->layout = null;

        $controller = new TestController('test-controller', Yii::$app);

        $controller->layout = null;

        self::assertFalse(
            $controller->findLayoutFile($controller->getView()),
            "No layout source means 'false'.",
        );
    }

    public function testBindInjectedParamsResolvesComponentByName(): void
    {
        $this->mockApplication();

        $controller = new InjectingController('inject', Yii::$app);
        $cookie = new Cookie(['name' => 'session', 'value' => 'opaque']);

        Yii::$app->set('cookie', $cookie);

        $args = [];
        $requestedParams = [];

        $controller->bindInjectedFor('actionComponent', 'cookie', $args, $requestedParams);

        self::assertSame(
            $cookie,
            $args[0],
            'Component instance must be bound.',
        );
        self::assertStringStartsWith(
            'Component:',
            $requestedParams['cookie'],
            "Resolution path must be tagged 'Component'.",
        );
    }

    public function testBindInjectedParamsResolvesModuleDependency(): void
    {
        $this->mockApplication();

        $module = new Module('inner', Yii::$app);
        $module->set(DataProviderInterface::class, ['class' => ArrayDataProvider::class]);
        $controller = new InjectingController('inject', $module);

        $args = [];
        $requestedParams = [];

        $controller->bindInjectedFor('actionModuleService', 'dataProvider', $args, $requestedParams);

        self::assertInstanceOf(
            ArrayDataProvider::class,
            $args[0],
            'Module DI must resolve the parameter.',
        );
        self::assertStringStartsWith(
            'Module ',
            $requestedParams['dataProvider'],
            "Resolution path must be tagged 'Module'.",
        );
    }

    public function testBindInjectedParamsResolvesContainerDependency(): void
    {
        $this->mockApplication();

        $controller = new InjectingController('inject', Yii::$app);

        Yii::$container->set(ServiceStub::class);

        $args = [];
        $requestedParams = [];

        $controller->bindInjectedFor('actionContainerService', 'service', $args, $requestedParams);

        self::assertInstanceOf(
            ServiceStub::class,
            $args[0],
            'Container must resolve the parameter.',
        );
        self::assertStringStartsWith(
            'Container DI:',
            $requestedParams['service'],
            "Resolution path must be tagged 'Container DI'.",
        );

        Yii::$container->clear(ServiceStub::class);
    }

    public function testBindInjectedParamsAssignsNullForNullableServiceWhenUnavailable(): void
    {
        $this->mockApplication();

        $controller = new InjectingController('inject', Yii::$app);

        Yii::$container->clear(ServiceStub::class);

        $args = [];
        $requestedParams = [];

        $controller->bindInjectedFor('actionNullableService', 'service', $args, $requestedParams);

        self::assertNull(
            $args[0],
            'Missing service must yield `null`.',
        );
        self::assertSame(
            'Unavailable service: service',
            $requestedParams['service'],
            'Resolution path must be tagged `Unavailable`.',
        );
    }

    public function testThrowExceptionWhenBindInjectedParamsCannotResolveRequiredService(): void
    {
        $this->mockApplication();

        $controller = new InjectingController('inject', Yii::$app);

        Yii::$container->clear(ServiceStub::class);

        $args = [];
        $requestedParams = [];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not load required service: service',
        );

        $controller->bindInjectedFor('actionContainerService', 'service', $args, $requestedParams);
    }

    public function testThrowInvalidRouteExceptionWhenRunActionCannotResolveTheRoute(): void
    {
        $this->mockApplication();

        $controller = new TestController('test-controller', Yii::$app);

        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(
            'Unable to resolve the request: test-controller/missing',
        );

        $controller->runAction('missing');
    }
}

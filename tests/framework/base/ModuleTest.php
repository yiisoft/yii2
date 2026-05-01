<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\ActionEvent;
use yii\base\BaseObject;
use yii\base\Controller;
use yii\base\Event;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yiiunit\framework\base\stub\module\ModuleTestController;
use yiiunit\framework\base\stub\module\TestModule;
use yiiunit\framework\base\stub\module\VeryComplexNameTestController;
use yiiunit\framework\base\stub\standalone\GreetAction;
use yiiunit\TestCase;

/**
 * Unit test for {@see Module}.
 */
#[Group('base')]
class ModuleTest extends TestCase
{
    public static $actionRuns = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testTrueParentModule(): void
    {
        $parent = new Module('parent');
        $child = new Module('child');
        $child2 = new Module('child2');

        $parent->setModule('child', $child);
        $parent->setModules(['child2' => $child2]);

        self::assertSame(
            'parent',
            $child->module->id,
            'Child must reference its parent.',
        );
        self::assertSame(
            'parent',
            $child2->module->id,
            'Mass-assigned child must reference its parent.',
        );
    }

    public function testGetControllerPath(): void
    {
        $module = new TestModule('test');

        $controllerPath = __DIR__ . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . 'controllers';

        self::assertSame(
            'yiiunit\framework\base\stub\module\controllers',
            $module->controllerNamespace,
            'Default namespace must be derived from the class file location.',
        );
        self::assertSame(
            $controllerPath,
            str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $module->getControllerPath()),
            'Default path must be derived from the class file location.',
        );
    }

    public function testSetControllerPath(): void
    {
        $module = new TestModule('test');

        $controllerPath = __DIR__ . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . 'controllers';

        $module->setControllerPath($controllerPath);

        self::assertSame(
            $controllerPath,
            $module->getControllerPath(),
            'Path must match the value just set.',
        );
    }

    public function testSetupVersion(): void
    {
        $module = new TestModule('test');

        $version = '1.0.1';

        $module->setVersion($version);

        self::assertSame(
            $version,
            $module->getVersion(),
            'String version must round-trip.',
        );

        $module->setVersion(static fn(TestModule $module): string => 'version.' . $module->getUniqueId());

        self::assertSame(
            'version.test',
            $module->getVersion(),
            'Callable version must be invoked with the module instance.',
        );
    }

    #[Depends('testSetupVersion')]
    public function testDefaultVersion(): void
    {
        $module = new TestModule('test');

        self::assertSame(
            '1.0',
            $module->getVersion(),
            "Default top-level version must be '1.0'.",
        );
    }

    public function testRunControllerAction(): void
    {
        $module = new TestModule('test');

        self::assertNull(Yii::$app->controller, 'Precondition: no active controller.');

        static::$actionRuns = [];

        $module->runAction('test-controller1/test1');

        self::assertSame(
            ['test/test-controller1/test1'],
            static::$actionRuns,
            'First action must record one run.',
        );
        self::assertNotNull(
            Yii::$app->controller,
            'Active controller must be retained after first dispatch.',
        );
        self::assertSame(
            'test-controller1',
            Yii::$app->controller->id,
            'Active controller ID must match the dispatched controller.',
        );
        self::assertSame(
            'test/test-controller1',
            Yii::$app->controller->uniqueId,
            'Active unique ID must include the module segment.',
        );
        self::assertNotNull(
            Yii::$app->controller->action,
            'Active action must be retained after first dispatch.',
        );
        self::assertSame(
            'test/test-controller1/test1',
            Yii::$app->controller->action->uniqueId,
            'Active action unique ID must match the dispatched route.',
        );

        $module->runAction('test-controller2/test2');

        self::assertSame(
            [
                'test/test-controller1/test1',
                'test/test-controller2/test2',
            ],
            static::$actionRuns,
            'Second dispatch must record both runs in order.',
        );
        self::assertNotNull(
            Yii::$app->controller,
            'Active controller must persist after second dispatch.',
        );
        self::assertSame(
            'test-controller1',
            Yii::$app->controller->id,
            'Active controller ID must roll back to the outer controller.',
        );
        self::assertSame(
            'test/test-controller1',
            Yii::$app->controller->uniqueId,
            'Active unique ID must roll back to the outer controller.',
        );
        self::assertNotNull(
            Yii::$app->controller->action,
            'Active action must roll back to the outer action.',
        );
        self::assertSame(
            'test/test-controller1/test1',
            Yii::$app->controller->action->uniqueId,
            'Active action unique ID must roll back to the outer action.',
        );
    }

    public function testServiceLocatorTraversal(): void
    {
        $parent = new Module('parent');
        $child = new Module('child', $parent);
        $grandchild = new Module('grandchild', $child);
        $parentObject = new BaseObject();
        $childObject = new BaseObject();

        $parent->set('test', $parentObject);

        self::assertTrue(
            $grandchild->has('test'),
            'Grandchild must see the parent service.',
        );
        self::assertTrue(
            $child->has('test'),
            'Child must see the parent service.',
        );
        self::assertTrue(
            $parent->has('test'),
            'Parent must see its own service.',
        );
        self::assertSame(
            $parentObject,
            $grandchild->get('test'),
            'Grandchild must resolve to the parent instance.',
        );
        self::assertSame(
            $parentObject,
            $child->get('test'),
            'Child must resolve to the parent instance.',
        );
        self::assertSame(
            $parentObject,
            $parent->get('test'),
            'Parent must resolve to its own instance.',
        );

        $child->set('test', $childObject);

        self::assertSame(
            $childObject,
            $grandchild->get('test'),
            'Grandchild must resolve through the closest ancestor.',
        );
        self::assertSame(
            $childObject,
            $child->get('test'),
            'Child override must take precedence.',
        );
        self::assertSame(
            $parentObject,
            $parent->get('test'),
            'Parent service must remain unchanged.',
        );
        self::assertTrue(
            $grandchild->has('test'),
            'Grandchild visibility must persist after override.',
        );
        self::assertTrue(
            $child->has('test'),
            'Child visibility must persist after override.',
        );
        self::assertTrue(
            $parent->has('test'),
            'Parent visibility must persist after override.',
        );

        $parent->clear('test');

        self::assertSame(
            $childObject,
            $grandchild->get('test'),
            'Grandchild must still resolve to the child override.',
        );
        self::assertSame(
            $childObject,
            $child->get('test'),
            'Child override must persist after parent is cleared.',
        );
        self::assertTrue(
            $grandchild->has('test'),
            'Grandchild visibility must persist after parent is cleared.',
        );
        self::assertTrue(
            $child->has('test'),
            'Child visibility must persist after parent is cleared.',
        );
        self::assertFalse(
            $parent->has('test'),
            'Parent service must be removed.',
        );
    }

    public function testCreateControllerByID(): void
    {
        $module = new TestModule('test');

        $module->controllerNamespace = 'yiiunit\framework\base\stub\module';

        self::assertInstanceOf(
            ModuleTestController::class,
            $module->createControllerByID('module-test'),
            'Hyphen-separated ID must map to CamelCase class.',
        );
        self::assertNull(
            $module->createControllerByID('module-test-'),
            'Trailing hyphen must invalidate the route.',
        );
        self::assertNull(
            $module->createControllerByID('-module-test'),
            'Leading hyphen must invalidate the route.',
        );
        self::assertInstanceOf(
            VeryComplexNameTestController::class,
            $module->createControllerByID('very-complex-name-test'),
            'Long hyphenated ID must map to CamelCase class.',
        );
        self::assertNull(
            $module->createControllerByID('very-complex-name-test--'),
            'Trailing double-hyphen must invalidate the route.',
        );
        self::assertNull(
            $module->createControllerByID('--very-complex-name-test'),
            'Leading double-hyphen must invalidate the route.',
        );
        self::assertNull(
            $module->createControllerByID('very---complex---name---test'),
            'Multiple consecutive hyphens must invalidate the route.',
        );
    }

    public function testCreateController(): void
    {
        // app module has a submodule "base" which has two controllers: "default" and "other"
        $module = new Module('app');

        $module->setModule('base', new Module('base'));

        $defaultController = ['class' => 'yii\web\Controller'];
        $otherController = ['class' => 'yii\web\Controller'];

        $module->getModule('base')->controllerMap = [
            'default' => $defaultController,
            'other' => $otherController,
        ];

        [$controller, $action] = $module->createController('base');

        self::assertSame(
            '',
            $action,
            'Module-only route must yield empty action.',
        );
        self::assertSame(
            'app/base/default',
            $controller->uniqueId,
            "Module-only route must resolve through 'defaultRoute'.",
        );

        [$controller, $action] = $module->createController('base/default');

        self::assertSame(
            '',
            $action,
            'Controller route must yield empty action.',
        );
        self::assertSame(
            'app/base/default',
            $controller->uniqueId,
            'Controller route must resolve to default controller.',
        );

        [$controller, $action] = $module->createController('base/other');

        self::assertSame(
            '',
            $action,
            'Sibling controller route must yield empty action.',
        );
        self::assertSame(
            'app/base/other',
            $controller->uniqueId,
            'Sibling controller route must resolve to the named controller.',
        );

        [$controller, $action] = $module->createController('base/default/index');

        self::assertSame(
            'index',
            $action,
            'Action segment must be returned for default controller.',
        );
        self::assertSame(
            'app/base/default',
            $controller->uniqueId,
            'Default controller must remain selected.',
        );

        [$controller, $action] = $module->createController('base/other/index');

        self::assertSame(
            'index',
            $action,
            'Action segment must be returned for sibling controller.',
        );
        self::assertSame(
            'app/base/other',
            $controller->uniqueId,
            'Sibling controller must remain selected.',
        );

        [$controller, $action] = $module->createController('base/other/someaction');

        self::assertSame(
            'someaction',
            $action,
            'Free-form action segment must be preserved.',
        );
        self::assertSame(
            'app/base/other',
            $controller->uniqueId,
            'Sibling controller must remain selected for free-form actions.',
        );
        self::assertFalse(
            $module->createController('bases/default/index'),
            "Unknown sub-module must yield 'false'.",
        );
        self::assertFalse(
            $module->createController('nocontroller'),
            "Unknown controller must yield 'false'.",
        );
    }

    public function testGetInstanceReturnsLoadedModuleByClassName(): void
    {
        $module = new TestModule('test', Yii::$app);

        Yii::$app->loadedModules[TestModule::class] = $module;

        self::assertSame(
            $module,
            TestModule::getInstance(),
            'Loaded instance must be returned.',
        );

        unset(Yii::$app->loadedModules[TestModule::class]);

        self::assertNull(
            TestModule::getInstance(),
            "Missing instance must yield 'null'.",
        );
    }

    public function testSetInstanceRemovesLoadedModuleWhenNullIsPassed(): void
    {
        $module = new TestModule('test', Yii::$app);

        TestModule::setInstance($module);
        TestModule::setInstance(null);

        self::assertArrayNotHasKey(
            TestModule::class,
            Yii::$app->loadedModules,
            'Loaded instance must be dropped.',
        );
    }

    public function testGetBasePathFallsBackToReflectionDirectory(): void
    {
        $module = new TestModule('test');

        self::assertSame(
            __DIR__ . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'module',
            $module->getBasePath(),
            'Default must be the class file directory.',
        );
    }

    public function testSetViewPathResolvesAlias(): void
    {
        Yii::setAlias('@moduleViews', __DIR__ . '/stub/views');

        $module = new TestModule('test');

        $module->setViewPath('@moduleViews');

        self::assertSame(
            __DIR__ . '/stub/views',
            $module->getViewPath(),
            'Alias must be resolved.',
        );
    }

    public function testSetLayoutPathResolvesAlias(): void
    {
        Yii::setAlias('@moduleLayouts', __DIR__ . '/stub/views/layouts');

        $module = new TestModule('test');

        $module->setLayoutPath('@moduleLayouts');

        self::assertSame(
            __DIR__ . '/stub/views/layouts',
            $module->getLayoutPath(),
            'Alias must be resolved.',
        );
    }

    public function testDefaultVersionDelegatesToParentModule(): void
    {
        $parent = new TestModule('parent');

        $parent->setVersion('9.9');

        $child = new TestModule('child', $parent);

        self::assertSame(
            '9.9',
            $child->getVersion(),
            'Parent version must be inherited.',
        );
    }

    public function testHasModuleTraversesNestedRoutes(): void
    {
        $parent = new TestModule('parent');
        $child = new TestModule('child', $parent);

        $parent->setModule('child', $child);

        $grand = new TestModule('grand', $child);

        $child->setModule('grand', $grand);

        self::assertTrue(
            $parent->hasModule('child/grand'),
            'Nested route must resolve.',
        );
        self::assertFalse(
            $parent->hasModule('child/missing'),
            "Unknown leaf must yield 'false'.",
        );
        self::assertFalse(
            $parent->hasModule('missing/grand'),
            "Unknown root must yield 'false'.",
        );
    }

    public function testGetModuleTraversesNestedRoutes(): void
    {
        $parent = new TestModule('parent');
        $child = new TestModule('child', $parent);

        $parent->setModule('child', $child);

        $grand = new TestModule('grand', $child);

        $child->setModule('grand', $grand);

        self::assertSame(
            $grand,
            $parent->getModule('child/grand'),
            'Leaf instance must be returned.',
        );
        self::assertNull(
            $parent->getModule('missing/grand'),
            "Unknown root must yield 'null'.",
        );
    }

    public function testSetModuleRemovesEntryWhenNullIsPassed(): void
    {
        $parent = new TestModule('parent');

        $parent->setModule('child', new TestModule('child', $parent));

        self::assertTrue(
            $parent->hasModule('child'),
            'Precondition: child must be registered.',
        );

        $parent->setModule('child', null);

        self::assertFalse(
            $parent->hasModule('child'),
            'Sub-module must be removed.',
        );
    }

    public function testGetModulesFiltersToLoadedOnlyWhenRequested(): void
    {
        $parent = new TestModule('parent');
        $loaded = new TestModule('loaded', $parent);

        $parent->setModule('loaded', $loaded);
        $parent->setModule('lazy', ['class' => TestModule::class]);
        $loadedModules = $parent->getModules(true);
        $allModules = $parent->getModules(false);

        self::assertSame(
            [$loaded],
            array_values($loadedModules),
            'Loaded-only filter must drop config arrays.',
        );
        self::assertCount(
            2,
            $allModules,
            'Unfiltered call must include configs.',
        );
    }

    public function testCreateStandaloneActionFallsBackToDefaultRouteWhenRouteIsEmpty(): void
    {
        $module = new TestModule('test');

        $module->actionNamespace = 'yiiunit\framework\base\stub\standalone';
        $module->defaultRoute = 'greet';

        $module->runAction('', ['name' => 'world']);

        self::assertSame(
            'greet',
            Yii::$app->requestedAction->id,
            "'defaultRoute' must drive dispatch.",
        );
    }

    public function testRunStandaloneActionShortCircuitsWhenBeforeActionEventIsInvalidated(): void
    {
        $module = new TestModule('test');
        $module->actionMap = [
            'noop' => GreetAction::class,
        ];

        Event::on(
            GreetAction::class,
            Controller::EVENT_BEFORE_ACTION,
            static function (ActionEvent $event): void {
                $event->isValid = false;
            },
        );

        try {
            $result = $module->runAction('noop', ['name' => 'world']);

            self::assertNull(
                $result,
                'Invalid event must short-circuit dispatch.',
            );
        } finally {
            Event::off(GreetAction::class, Controller::EVENT_BEFORE_ACTION);
        }
    }

    public function testCreateControllerReturnsFalseForRouteWithDoubleSlash(): void
    {
        $module = new TestModule('test');

        self::assertFalse(
            $module->createController('foo//bar'),
            "Double-slash route must yield 'false'.",
        );
    }

    #[Group('prod')]
    public function testCreateControllerByIdReturnsNullForNonControllerClassOutsideDebugMode(): void
    {
        if (YII_DEBUG) {
            self::markTestSkipped('Run with --bootstrap tests/bootstrap-prod.php to exercise the YII_DEBUG=false branch.');
        }

        $module = new TestModule('test');

        $module->controllerNamespace = 'yiiunit\framework\base\stub\module';

        self::assertNull(
            $module->createControllerByID('plain-class'),
            "Non-Controller class must yield 'null'.",
        );
    }

    public function testThrowInvalidArgumentExceptionWhenSetBasePathDirectoryDoesNotExist(): void
    {
        $module = new TestModule('test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The directory does not exist: /no/such/dir/__phantom__',
        );

        $module->setBasePath('/no/such/dir/__phantom__');
    }

    public function testThrowInvalidConfigExceptionWhenCreateControllerByIdResolvesNonControllerClassUnderDebug(): void
    {
        if (!YII_DEBUG) {
            self::markTestSkipped("Run with the default bootstrap to exercise the 'YII_DEBUG=true' branch.");
        }

        $module = new TestModule('test');

        $module->controllerNamespace = 'yiiunit\framework\base\stub\module';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Controller class must extend from \yii\base\Controller.',
        );

        $module->createControllerByID('plain-class');
    }

    public function testThrowInvalidRouteExceptionForRouteWithDoubleSlash(): void
    {
        $module = new TestModule('test');

        $module->actionNamespace = 'yiiunit\framework\base\stub\standalone';

        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(
            'Unable to resolve the request "test/foo//bar".',
        );

        $module->runAction('foo//bar');
    }

    public function testThrowInvalidRouteExceptionWhenRunActionResolvesControllerWithoutAction(): void
    {
        $module = new TestModule('test');

        $module->controllerNamespace = 'yiiunit\framework\base\stub\module';

        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(
            'Unable to resolve the request: test/module-test/missing-action',
        );

        $module->runAction('module-test/missing-action');
    }
}

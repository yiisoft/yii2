<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\ActionEvent;
use yii\base\InvalidRouteException;
use yii\base\Module;
use yiiunit\framework\base\stub\actionmap\PingAction;
use yiiunit\framework\base\stub\actionmap\PingController;
use yiiunit\framework\base\stub\actions\HealthAction;
use yiiunit\framework\base\stub\standalone\ActionTestService;
use yiiunit\framework\base\stub\standalone\LegacyConstructorAction;
use yiiunit\framework\base\stub\standalone\LegacyConstructorBehaviorAction;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\base\Module} dispatch of standalone actions via {@see Module::$actionMap}.
 *
 * Verifies that registering a standalone {@see \yii\base\Action} subclass under {@see Module::$actionMap} causes
 * {@see \yii\base\Module::runAction()} to invoke it without a hosting controller, that lifecycle events on the module
 * chain still fire in order, and that legacy controller-based dispatch remains unaffected for unmapped IDs.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('base')]
final class ModuleActionMapTest extends TestCase
{
    public static array $events = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();

        self::$events = [];
    }

    public function testActionMapResolvesAndRuns(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['ping' => PingAction::class];

        $result = $module->runAction('ping', ['name' => 'world']);

        self::assertSame(
            'pong-world',
            $result,
            'Mapped action must execute and receive params from the route.',
        );
    }

    public function testActionMapTakesPrecedenceOverControllerMap(): void
    {
        $module = new Module('mymod');

        $module->controllerMap = ['ping' => PingController::class];
        $module->actionMap = ['ping' => PingAction::class];

        $result = $module->runAction('ping', ['name' => 'first']);

        self::assertSame(
            'pong-first',
            $result,
            "'actionMap' lookup must run before 'controllerMap' resolution.",
        );
    }

    public function testActionNotInMapFallsThroughToControllerPipeline(): void
    {
        $module = new Module('mymod');

        $module->controllerMap = ['legacy' => PingController::class];
        $module->actionMap = ['ping' => PingAction::class];

        $result = $module->runAction('legacy/echo');

        self::assertSame(
            'legacy:hello',
            $result,
            "Routes outside 'actionMap' must reach the controller pipeline.",
        );
    }

    public function testModuleEventBeforeActionFiresForMappedAction(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['ping' => PingAction::class];

        $module->on(
            Module::EVENT_BEFORE_ACTION,
            static function (ActionEvent $e): void {
                ModuleActionMapTest::$events[] = 'module-before:' . $e->action->getUniqueId();
            },
        );

        $module->runAction('ping', ['name' => 'evt']);

        self::assertContains(
            'module-before:mymod/ping',
            self::$events,
            'Before-action should observe the action.',
        );
    }

    public function testModuleEventAfterActionFiresForMappedAction(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['ping' => PingAction::class];

        $module->on(
            Module::EVENT_AFTER_ACTION,
            static function (ActionEvent $e): void {
                ModuleActionMapTest::$events[] = "module-after:{$e->result}";
            },
        );

        $module->runAction('ping', ['name' => 'evt']);

        self::assertContains(
            'module-after:pong-evt',
            self::$events,
            'After-action event should observe the result.',
        );
    }

    public function testNestedModuleEventsFireForMappedActionInOrder(): void
    {
        $parent = new Module('parent');
        $child = new Module('child', $parent);

        $child->actionMap = ['ping' => PingAction::class];

        $parent->on(
            Module::EVENT_BEFORE_ACTION,
            static function (): void {
                ModuleActionMapTest::$events[] = 'parent-before';
            },
        );
        $child->on(
            Module::EVENT_BEFORE_ACTION,
            static function (): void {
                ModuleActionMapTest::$events[] = 'child-before';
            },
        );
        $parent->on(
            Module::EVENT_AFTER_ACTION,
            static function (): void {
                ModuleActionMapTest::$events[] = 'parent-after';
            },
        );
        $child->on(
            Module::EVENT_AFTER_ACTION,
            static function (): void {
                ModuleActionMapTest::$events[] = 'child-after';
            },
        );

        $child->runAction('ping', ['name' => 'nest']);

        self::assertSame(
            ['parent-before', 'child-before', 'child-after', 'parent-after'],
            self::$events,
            'Module ancestors fire before-actions outer-to-inner and after-actions inner-to-outer.',
        );
    }

    public function testActionEventCanCancelMappedActionExecution(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['ping' => PingAction::class];

        $module->on(
            Module::EVENT_BEFORE_ACTION,
            static function (ActionEvent $e): void {
                $e->isValid = false;
            },
        );

        $result = $module->runAction('ping', ['name' => 'blocked']);

        self::assertNull(
            $result,
            "Setting 'isValid' to 'false' in module before-action must cancel the action.",
        );
    }

    public function testMappedActionSetsRequestedActionOnApplication(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['ping' => PingAction::class];

        Yii::$app->requestedAction = null;

        $module->runAction('ping', ['name' => 'tracker']);

        self::assertNotNull(
            Yii::$app->requestedAction,
            'Action must populate \'Yii::$app->requestedAction\' once.',
        );

        $requested = Yii::$app->requestedAction;

        self::assertSame(
            'mymod/ping',
            $requested->getUniqueId(),
            'Requested action must be the mapped action.',
        );
    }

    public function testLegacyConstructorActionRunsViaUnifiedDispatchPath(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['legacy' => LegacyConstructorAction::class];

        $result = $module->runAction('legacy');

        self::assertSame(
            'legacy',
            $result,
            'Legacy-shaped action must execute through the same dispatch path as a modern action.',
        );
    }

    public function testLegacyConstructorActionReceivesNullIdFromDispatcher(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['legacy' => LegacyConstructorAction::class];

        $module->runAction('legacy');

        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyConstructorAction::class,
            $action,
            'Resolved action must be the legacy stub.',
        );
        self::assertNull(
            $action->idSeenInConstructor,
            "ID must be 'null' during construction.",
        );
        self::assertNull(
            $action->controllerSeenInConstructor,
            "Controller must be 'null' for standalone dispatch.",
        );
    }

    public function testLegacyConstructorActionInitObservesNullIdAndDispatcherAssignsAfterwards(): void
    {
        $module = new Module('mymod');

        $module->actionMap = ['legacy' => LegacyConstructorAction::class];

        $module->runAction('legacy');

        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyConstructorAction::class,
            $action,
            'Resolved action must be the legacy stub.',
        );
        self::assertNull(
            $action->idSeenInInit,
            "Identity must be 'null' during 'init'.",
        );
        self::assertSame(
            'legacy',
            $action->id,
            'Dispatcher must assign the route ID post-construction.',
        );
        self::assertSame(
            'mymod/legacy',
            $action->getUniqueId(),
            'Unique ID must combine the module ID and the route segment.',
        );
    }

    public function testLegacyConstructorActionBehaviorObservesRealIdDuringEventDispatch(): void
    {
        LegacyConstructorBehaviorAction::$events = [];

        $module = new Module('mymod');

        $module->actionMap = ['legacy-behavior' => LegacyConstructorBehaviorAction::class];

        $module->runAction('legacy-behavior');

        self::assertSame(
            [
                ['beforeAction', 'legacy-behavior'],
                ['afterAction', 'legacy-behavior'],
            ],
            LegacyConstructorBehaviorAction::$events,
            'Event-time ID must equal the route segment.',
        );
    }

    public function testLegacyConstructorActionUnderNestedModuleProducesCompositeUniqueId(): void
    {
        $parent = new Module('parent', Yii::$app);
        $child = new Module('child', $parent);

        $child->actionMap = ['legacy' => LegacyConstructorAction::class];

        $child->runAction('legacy');

        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyConstructorAction::class,
            $action,
            'Resolved action must be the legacy stub.',
        );
        self::assertNull(
            $action->idSeenInInit,
            "Identity must be 'null' during 'init' even under nested dispatch.",
        );
        self::assertSame(
            'parent/child/legacy',
            $action->getUniqueId(),
            'Unique ID must walk the full module ancestry plus the route segment.',
        );
    }

    public function testThrowInvalidRouteExceptionWhenRouteResolvesToNeitherMap(): void
    {
        $module = new Module('mymod');

        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(
            'Unable to resolve the request "mymod/missing".',
        );

        $module->runAction('missing');
    }

    public function testActionMapResolvesPromotedConstructorAction(): void
    {
        Yii::$container->setSingleton(ActionTestService::class, ActionTestService::class);

        $module = new Module('mymod');

        $module->actionMap = ['promoted' => HealthAction::class];

        $result = $module->runAction('promoted');

        Yii::$container->clear(ActionTestService::class);

        self::assertSame(
            'health-svc',
            $result,
            'Action with promoted constructor must receive its dependency through the DI container.',
        );
    }
}

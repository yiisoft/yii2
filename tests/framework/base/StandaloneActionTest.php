<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use Yii;
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yiiunit\framework\base\stub\standalone\ActionTestService;
use yiiunit\framework\base\stub\standalone\BehaviorAction;
use yiiunit\framework\base\stub\standalone\CancelingAction;
use yiiunit\framework\base\stub\standalone\GreetAction;
use yiiunit\framework\base\stub\standalone\InjectingAction;
use yiiunit\framework\base\stub\standalone\MethodInjectingAction;
use yiiunit\framework\base\stub\standalone\NoRunAction;
use yiiunit\framework\base\stub\standalone\StubController;
use yiiunit\framework\base\stub\standalone\TrackingFilter;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\base\Action} when invoked standalone (without a hosting controller).
 *
 * Covers the nullable `$controller` contract, module-aware {@see \yii\base\Action::getUniqueId()} fallback,
 * DI-based parameter resolution on `run()` via {@see \yii\di\Container::resolveCallableDependencies()}, the
 * `beforeRun()` cancellation hook, and behavior attachment via the action's own event triggers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 *
 * @group base
 */
final class StandaloneActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();

        Yii::$container->setSingleton(ActionTestService::class, ActionTestService::class);

        TrackingFilter::$beforeCalls = [];
    }

    protected function tearDown(): void
    {
        Yii::$container->clear(ActionTestService::class);

        parent::tearDown();
    }

    public function testRunWithoutController(): void
    {
        $action = new GreetAction('greet', null);

        $result = $action->runWithParams(['name' => 'world']);

        self::assertSame(
            'hello world',
            $result,
            "Standalone action should execute 'run()' with provided params.",
        );
    }

    public function testGetUniqueIdFallsBackToModuleWhenControllerIsNull(): void
    {
        $module = new Module('mymod');

        $action = new GreetAction('greet', null);

        $action->setModule($module);

        self::assertSame(
            'mymod/greet',
            $action->getUniqueId(),
            'Unique ID should combine module ID and action ID.',
        );
    }

    public function testGetUniqueIdReturnsIdWhenNoControllerAndNoModule(): void
    {
        $action = new GreetAction('orphan', null);

        self::assertSame(
            'orphan',
            $action->getUniqueId(),
            'Unique ID should fall back to action ID alone.',
        );
    }

    public function testGetUniqueIdStillWorksWithController(): void
    {
        $controller = new StubController('stub', Yii::$app);

        $action = new GreetAction('greet', $controller);

        self::assertSame(
            'stub/greet',
            $action->getUniqueId(),
            'Existing controller-bound path must remain intact.',
        );
    }

    public function testTypedParamInjectionViaContainer(): void
    {
        $action = new MethodInjectingAction('inject', null);

        $result = $action->runWithParams(['id' => 7]);

        self::assertSame(
            'svc-7',
            $result,
            'Service should be autowired and scalar param taken from request.',
        );
    }

    public function testConstructorParamInjectionViaContainer(): void
    {
        $action = Yii::createObject(InjectingAction::class, ['inject', null]);

        self::assertInstanceOf(
            InjectingAction::class,
            $action,
            'Container must instantiate action with deps.',
        );
        self::assertSame(
            'svc',
            $action->runWithParams([]),
            "Constructor injected service should reach 'run()'.",
        );
    }

    public function testBeforeRunReturningFalseCancelsExecution(): void
    {
        $action = new CancelingAction('cancel', null);

        $result = $action->runWithParams([]);

        self::assertNull(
            $result,
            "Before run returning 'false' must short-circuit execution.",
        );
        self::assertFalse(
            $action->ran,
            "run() must not be invoked when beforeRun returns 'false'.",
        );
    }

    public function testBehaviorAttachesAndFiresOnAction(): void
    {
        $action = new BehaviorAction('tracked', null);

        $event = new ActionEvent($action);

        $action->trigger(Controller::EVENT_BEFORE_ACTION, $event);

        self::assertTrue(
            $event->isValid,
            'Tracking filter must keep the action valid by default.',
        );
        self::assertSame(
            ['tracked'],
            TrackingFilter::$beforeCalls,
            'Behavior must observe the standalone action ID.',
        );
    }

    public function testStandaloneRunResolvesParamsViaContainer(): void
    {
        $action = new MethodInjectingAction('inject', null);

        $result = $action->runWithParams(['id' => 42]);

        self::assertSame(
            'svc-42',
            $result,
            'Method level DI must combine container service and route params.',
        );
    }

    public function testThrowInvalidConfigExceptionWhenActionHasNoRunMethod(): void
    {
        $action = new NoRunAction('broken', null);

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'must define a "run()" method',
        );

        $action->runWithParams([]);
    }
}

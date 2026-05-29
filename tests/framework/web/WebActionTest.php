<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\Module;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\ServerErrorHttpException;
use yiiunit\framework\base\stub\standalone\ActionTestService;
use yiiunit\framework\web\stub\standalone\CoerceIntAction;
use yiiunit\framework\web\stub\standalone\GuardedAction;
use yiiunit\framework\web\stub\standalone\LegacyConstructorWebAction;
use yiiunit\framework\web\stub\standalone\ModuleServiceAction;
use yiiunit\framework\web\stub\standalone\NullableServiceAction;
use yiiunit\framework\web\stub\standalone\RequiredServiceAction;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\web\Action} HTTP-aware standalone parameter binding.
 *
 * Verifies that standalone HTTP actions get the same scalar coercion, type validation, and missing-parameter handling
 * as controller-bound actions, mirroring {@see \yii\web\Controller::bindActionParams()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('web')]
final class WebActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    public function testWebActionCoercesScalarParam(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $result = $action->runWithParams(['id' => '7']);

        self::assertSame(
            7,
            $result,
            "Scalar string '7' should be coerced to int 7 by HTTP-aware binder.",
        );
    }

    public function testThrowBadRequestHttpExceptionWhenScalarParamFailsTypeCoercion(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid data received for parameter "id".');

        $action->runWithParams(['id' => 'abc']);
    }

    public function testThrowBadRequestHttpExceptionWhenRequiredParamMissing(): void
    {
        $action = new CoerceIntAction('coerce', null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Missing required parameters: id');

        $action->runWithParams([]);
    }

    public function testLegacyConstructorWebActionRunsViaUnifiedDispatchPath(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['legacy' => LegacyConstructorWebAction::class];

        $result = $module->runAction('legacy', ['id' => '7']);

        self::assertSame(
            7,
            $result,
            "Legacy-shaped 'web\\Action' must execute and apply HTTP-aware scalar coercion.",
        );
    }

    public function testLegacyConstructorWebActionReceivesNullIdFromDispatcher(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['legacy' => LegacyConstructorWebAction::class];

        $module->runAction('legacy', ['id' => '7']);

        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyConstructorWebAction::class,
            $action,
            'Resolved action must be the legacy web stub.',
        );
        self::assertNull(
            $action->idSeenInConstructor,
            "ID must be 'null' during construction.",
        );
    }

    public function testLegacyConstructorWebActionObservesNullIdInInit(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['legacy' => LegacyConstructorWebAction::class];

        $module->runAction('legacy', ['id' => '7']);

        $action = Yii::$app->requestedAction;

        self::assertInstanceOf(
            LegacyConstructorWebAction::class,
            $action,
            'Resolved action must be the legacy web stub.',
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

    public function testWebActionInjectsNullForUnresolvableNullableService(): void
    {
        $action = new NullableServiceAction('optional', null);

        $result = $action->runWithParams([]);

        self::assertSame(
            'null',
            $result,
            "Unresolvable nullable service must inject 'null'.",
        );
    }

    public function testThrowServerErrorHttpExceptionWhenStandaloneDependencyIsUnresolvable(): void
    {
        $action = new RequiredServiceAction('required', null);

        $this->expectException(ServerErrorHttpException::class);
        $this->expectExceptionMessage(
            'Could not load required service: dependency',
        );

        $action->runWithParams([]);
    }

    public function testWebActionResolvesServiceViaModuleComponent(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['svc' => ModuleServiceAction::class];

        $module->set('service', ActionTestService::class);

        $result = $module->runAction('svc');

        self::assertSame(
            'svc',
            $result,
            'Module component must satisfy the typed parameter.',
        );
        self::assertStringStartsWith(
            'Component:',
            Yii::$app->requestedParams['service'],
            'Resolution path must be tagged Component.',
        );
    }

    public function testWebActionResolvesServiceViaModuleDiDefinition(): void
    {
        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['svc' => ModuleServiceAction::class];

        $module->set(ActionTestService::class, ActionTestService::class);

        $result = $module->runAction('svc');

        self::assertSame(
            'svc',
            $result,
            'Module DI definition must satisfy the typed parameter.',
        );
        self::assertStringContainsString(
            'DI:',
            Yii::$app->requestedParams['service'],
            'Resolution path must be tagged as module DI.',
        );
    }

    public function testStandaloneActionRunsWhenVerbFilterAllowsMethod(): void
    {
        $originalMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        try {
            $module = new Module('mymod', Yii::$app);

            $module->actionMap = ['guarded' => GuardedAction::class];

            self::assertSame(
                'ok',
                $module->runAction('guarded'),
                'Allowed verb must let the standalone action run.',
            );
        } finally {
            $this->restoreRequestMethod($originalMethod);
        }
    }

    public function testThrowMethodNotAllowedHttpExceptionWhenVerbFilterRejectsMethod(): void
    {
        $originalMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $module = new Module('mymod', Yii::$app);

        $module->actionMap = ['guarded' => GuardedAction::class];

        try {
            $this->expectException(MethodNotAllowedHttpException::class);

            $module->runAction('guarded');
        } finally {
            $this->restoreRequestMethod($originalMethod);
        }
    }

    private function restoreRequestMethod(string|null $method): void
    {
        if ($method === null) {
            unset($_SERVER['REQUEST_METHOD']);

            return;
        }

        $_SERVER['REQUEST_METHOD'] = $method;
    }
}

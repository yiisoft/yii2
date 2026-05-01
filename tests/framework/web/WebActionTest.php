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
use yiiunit\framework\web\stub\standalone\CoerceIntAction;
use yiiunit\framework\web\stub\standalone\LegacyConstructorWebAction;
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

    public function testLegacyConstructorWebActionReceivesRouteIdInsideTheConstructor(): void
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
        self::assertSame(
            'legacy',
            $action->idSeenInConstructor,
            'Route segment ID must arrive positionally.',
        );
    }

    public function testLegacyConstructorWebActionInitObservesRouteId(): void
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
        self::assertSame(
            'legacy',
            $action->idSeenInInit,
            'Identity must be set before lifecycle hooks run.',
        );
        self::assertSame(
            'mymod/legacy',
            $action->getUniqueId(),
            'Unique ID must combine the module ID and the route segment.',
        );
    }
}

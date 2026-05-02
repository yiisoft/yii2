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
use yiiunit\framework\base\stub\inline\CancelingInlineAction;
use yiiunit\framework\base\stub\inline\PingController;
use yiiunit\framework\base\stub\inline\TrackingInlineAction;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\base\InlineAction} lifecycle wrapper around the controller method invocation.
 *
 * Verifies that since version 22.0, {@see \yii\base\InlineAction::runWithParams()} honors
 * {@see \yii\base\Action::beforeRun()} and {@see \yii\base\Action::afterRun()} hooks, mirroring the behavior of
 * standalone {@see \yii\base\Action::runWithParams()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('base')]
final class InlineActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testBeforeRunReturningFalseShortCircuitsControllerMethod(): void
    {
        $controller = new PingController('ping', Yii::$app);

        $action = new CancelingInlineAction('ping', $controller, 'actionPing');

        $result = $action->runWithParams([]);

        self::assertNull(
            $result,
            "Inline action must return 'null' when 'beforeRun' returns 'false'.",
        );
        self::assertFalse(
            $controller->methodInvoked,
            "Controller method must not be invoked when 'beforeRun' returns 'false'.",
        );
        self::assertFalse(
            $action->afterRunCalled,
            "afterRun must not be invoked when 'beforeRun' returns 'false'.",
        );
    }

    public function testBeforeRunAndAfterRunWrapTheControllerMethod(): void
    {
        $controller = new PingController('ping', Yii::$app);

        $action = new TrackingInlineAction('ping', $controller, 'actionPing');

        $result = $action->runWithParams([]);

        self::assertSame(
            'pong',
            $result,
            "Controller method result must propagate when beforeRun returns 'true'.",
        );
        self::assertTrue(
            $controller->methodInvoked,
            'Controller method must be invoked.',
        );
        self::assertTrue(
            $action->beforeRunCalled,
            'beforeRun hook must be invoked before the controller method.',
        );
        self::assertTrue(
            $action->afterRunCalled,
            'afterRun hook must be invoked after the controller method.',
        );
    }
}

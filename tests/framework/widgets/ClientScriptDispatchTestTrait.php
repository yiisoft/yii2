<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\widgets;

use yii\base\BaseObject;
use yii\web\client\ClientScriptInterface;
use yii\web\View;
use yiiunit\framework\widgets\stub\FakeClientScript;

/**
 * Provides reusable assertions for the `clientScript` strategy pattern in widget and grid column tests.
 *
 * Exercises three materialization branches in `init()` plus the register dispatch path invoked when the widget runs:
 *
 * - {@see testClientScriptIsNullByDefault}
 * - {@see testClientScriptIsMaterializedFromArrayConfig}
 * - {@see testClientScriptInstanceIsPreserved}
 * - {@see testClientScriptRegisterIsNotInvokedWhenNotConfigured}
 * - {@see testClientScriptRegisterIsInvokedWhenConfigured}
 *
 * Consumers must implement two helpers:
 *
 * - {@see createWidgetInstance()} — builds the widget with the given configuration merged on top of the class-specific
 *   defaults (for example, providing `dataProvider` for `GridView`).
 * - {@see triggerClientScriptDispatch()} — invokes whatever method actually triggers `$clientScript->register()`
 *   (typically `run()` or `registerClientScript()`). May be a no-op for components that dispatch during `init()`
 *   (for example, {@see \yii\grid\CheckboxColumn}).
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
trait ClientScriptDispatchTestTrait
{
    /**
     * Creates a widget instance with the given configuration merged on top of the class-specific defaults needed for
     * the widget's `init()` method to succeed (for example, providing `dataProvider` for `GridView`).
     *
     * @param array<string, mixed> $config Extra configuration to merge into the widget constructor.
     */
    abstract protected function createWidgetInstance(array $config = []): BaseObject;

    /**
     * Invokes the method that triggers `$clientScript->register()` on the given widget instance (typically `run()` or
     * `registerClientScript()`). May be a no-op for components whose dispatch happens during `init()`.
     */
    abstract protected function triggerClientScriptDispatch(BaseObject $widget): void;

    public function testClientScriptIsNullByDefault(): void
    {
        $widget = $this->createWidgetInstance();

        $this->assertNull(
            $widget->clientScript,
            "'\$clientScript' should default to `null` when the attribute is not configured.",
        );
    }

    public function testClientScriptIsMaterializedFromArrayConfig(): void
    {
        $widget = $this->createWidgetInstance(
            ['clientScript' => ['class' => FakeClientScript::class]],
        );

        $this->assertInstanceOf(
            FakeClientScript::class,
            $widget->clientScript,
            "Array '\$clientScript' config should be materialized via 'Yii::createObject()' during 'init()'.",
        );
        $this->assertInstanceOf(
            ClientScriptInterface::class,
            $widget->clientScript,
            "Materialized '\$clientScript' should implement 'ClientScriptInterface'.",
        );
    }

    public function testClientScriptInstanceIsPreserved(): void
    {
        $script = new FakeClientScript();
        $widget = $this->createWidgetInstance(['clientScript' => $script]);

        $this->assertSame(
            $script,
            $widget->clientScript,
            "An already-instantiated '\$clientScript' should be preserved without re-creating it.",
        );
    }

    public function testClientScriptRegisterIsNotInvokedWhenNotConfigured(): void
    {
        $widget = $this->createWidgetInstance();

        $this->triggerClientScriptDispatch($widget);

        $this->assertNull(
            $widget->clientScript,
            "'\$clientScript' should remain `null` after dispatch when the attribute is not configured.",
        );
    }

    public function testClientScriptRegisterIsInvokedWhenConfigured(): void
    {
        $script = new FakeClientScript();
        $widget = $this->createWidgetInstance(['clientScript' => $script]);

        $this->triggerClientScriptDispatch($widget);

        $this->assertNotNull(
            $script->lastRegisterCall,
            "'\$clientScript->register()' should be invoked when the widget is dispatched with a configured strategy.",
        );
        $this->assertSame(
            $widget,
            $script->lastRegisterCall[0],
            "'\$clientScript->register()' should receive the widget instance as the first argument.",
        );
        $this->assertInstanceOf(
            View::class,
            $script->lastRegisterCall[1],
            "'\$clientScript->register()' should receive a 'View' instance as the second argument.",
        );
    }
}

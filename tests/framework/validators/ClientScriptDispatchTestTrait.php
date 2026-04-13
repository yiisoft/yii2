<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\DynamicModel;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\Validator;
use yii\web\View;
use yiiunit\framework\validators\stub\FakeClientValidatorScript;

/**
 * Provides reusable assertions for the `clientScript` strategy pattern in validator tests.
 *
 * Exercises three materialization branches in {@see Validator::init()} and four dispatch branches in
 * {@see Validator::clientValidateAttribute()} / {@see Validator::getClientOptions()}:
 *
 * - {@see testClientScriptIsNullByDefault}
 * - {@see testClientScriptIsMaterializedFromArrayConfig}
 * - {@see testClientScriptInstanceIsPreserved}
 * - {@see testClientValidateAttributeReturnsNullWithoutClientScript}
 * - {@see testClientValidateAttributeDelegatesToClientScript}
 * - {@see testGetClientOptionsReturnsEmptyArrayWithoutClientScript}
 * - {@see testGetClientOptionsDelegatesToClientScript}
 *
 * Consumers must implement {@see createValidatorInstance()} returning a minimally configured validator instance.
 *
 * Validators with a native (non-delegating) `getClientOptions()` implementation (for example,
 * {@see \yii\validators\FilterValidator}) should override {@see testGetClientOptionsDelegatesToClientScript()} in the
 * consumer class and mark it skipped with an explanatory message.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
trait ClientScriptDispatchTestTrait
{
    /**
     * Creates a validator instance with the given configuration merged on top of the class-specific defaults needed
     * for the validator's `init()` method to succeed (for example, providing `compareValue` for `CompareValidator`).
     *
     * @param array<string, mixed> $config Extra configuration to merge into the validator constructor.
     */
    abstract protected function createValidatorInstance(array $config = []): Validator;

    public function testClientScriptIsNullByDefault(): void
    {
        $validator = $this->createValidatorInstance();

        $this->assertNull(
            $validator->clientScript,
            "'\$clientScript' should default to `null` when the attribute is not configured.",
        );
    }

    public function testClientScriptIsMaterializedFromArrayConfig(): void
    {
        $validator = $this->createValidatorInstance(
            ['clientScript' => ['class' => FakeClientValidatorScript::class]],
        );

        $this->assertInstanceOf(
            FakeClientValidatorScript::class,
            $validator->clientScript,
            "Array '\$clientScript' config should be materialized via 'Yii::createObject()' during 'init()'.",
        );
        $this->assertInstanceOf(
            ClientValidatorScriptInterface::class,
            $validator->clientScript,
            "Materialized '\$clientScript' should implement 'ClientValidatorScriptInterface'.",
        );
    }

    public function testClientScriptInstanceIsPreserved(): void
    {
        $script = new FakeClientValidatorScript();
        $validator = $this->createValidatorInstance(['clientScript' => $script]);

        $this->assertSame(
            $script,
            $validator->clientScript,
            "An already-instantiated '\$clientScript' should be preserved without re-creating it.",
        );
    }

    public function testClientValidateAttributeReturnsNullWithoutClientScript(): void
    {
        $validator = $this->createValidatorInstance();
        $model = new DynamicModel(['attr' => 'value']);

        $this->assertNull(
            $validator->clientValidateAttribute($model, 'attr', new View()),
            "'clientValidateAttribute()' should return `null` when '\$clientScript' is not configured.",
        );
    }

    public function testClientValidateAttributeDelegatesToClientScript(): void
    {
        $script = new FakeClientValidatorScript();
        $script->registerReturnValue = '/* registered */';

        $validator = $this->createValidatorInstance(['clientScript' => $script]);
        $model = new DynamicModel(['attr' => 'value']);
        $view = new View();

        $result = $validator->clientValidateAttribute($model, 'attr', $view);

        $this->assertSame(
            '/* registered */',
            $result,
            "'clientValidateAttribute()' should forward the '\$clientScript->register()' return value.",
        );
        $this->assertNotNull(
            $script->lastRegisterCall,
            "'\$clientScript->register()' should be invoked by 'clientValidateAttribute()'.",
        );
        $this->assertSame(
            [$validator, $model, 'attr', $view],
            $script->lastRegisterCall,
            "'\$clientScript->register()' should receive '\$this', '\$model', '\$attribute', '\$view' in that order.",
        );
    }

    public function testGetClientOptionsReturnsEmptyArrayWithoutClientScript(): void
    {
        $validator = $this->createValidatorInstance();
        $model = new DynamicModel(['attr' => 'value']);

        $this->assertSame(
            [],
            $validator->getClientOptions($model, 'attr'),
            "'getClientOptions()' should return '[]' when '\$clientScript' is not configured.",
        );
    }

    public function testGetClientOptionsDelegatesToClientScript(): void
    {
        $script = new FakeClientValidatorScript();
        $script->clientOptionsReturnValue = ['delegated' => true];

        $validator = $this->createValidatorInstance(['clientScript' => $script]);
        $model = new DynamicModel(['attr' => 'value']);

        $result = $validator->getClientOptions($model, 'attr');

        $this->assertSame(
            ['delegated' => true],
            $result,
            "'getClientOptions()' should forward the '\$clientScript->getClientOptions()' return value.",
        );
        $this->assertNotNull(
            $script->lastGetClientOptionsCall,
            "'\$clientScript->getClientOptions()' should be invoked by the validator's 'getClientOptions()'.",
        );
        $this->assertSame(
            [$validator, $model, 'attr'],
            $script->lastGetClientOptionsCall,
            "'\$clientScript->getClientOptions()' should receive '\$this', '\$model', '\$attribute' in that order.",
        );
    }
}

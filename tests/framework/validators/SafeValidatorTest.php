<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\DynamicModel;
use yii\validators\SafeValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class SafeValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->destroyApplication();
    }

    public function testValidateAttributeDoesNothing(): void
    {
        $model = new DynamicModel(['name' => 'original']);
        $validator = new SafeValidator();

        $validator->validateAttribute($model, 'name');

        $this->assertSame('original', $model->name);
        $this->assertFalse($model->hasErrors('name'));
    }

    public function testValidateAttributesDoesNothing(): void
    {
        $model = new DynamicModel(['name' => 'original', 'email' => null]);
        $validator = new SafeValidator();

        $validator->validateAttributes($model, ['name', 'email']);

        $this->assertSame('original', $model->name);
        $this->assertNull($model->email);
        $this->assertFalse($model->hasErrors());
    }

    public function testValidateAttributeNullValue(): void
    {
        $model = new DynamicModel(['field' => null]);
        $validator = new SafeValidator();

        $validator->validateAttribute($model, 'field');

        $this->assertNull($model->field);
        $this->assertFalse($model->hasErrors('field'));
    }

    public function testSafeAttributesMarkedForMassAssignment(): void
    {
        $model = new DynamicModel(['username', 'role']);
        $model->addRule('username', 'safe');

        $model->load(['username' => 'admin', 'role' => 'superuser'], '');

        $this->assertSame('admin', $model->username);
        $this->assertNull($model->role);
    }

    public function testValidationAlwaysPasses(): void
    {
        $model = new DynamicModel(['field' => '']);
        $model->addRule('field', 'safe');

        $model->validate();

        $this->assertFalse($model->hasErrors());
    }

    public function testValidationPassesWithAnyValue(): void
    {
        $values = [0, '', null, false, [], 'string', 123, 1.5];

        foreach ($values as $value) {
            $model = new DynamicModel(['attr' => $value]);
            $validator = new SafeValidator();
            $validator->validateAttribute($model, 'attr');

            $this->assertFalse($model->hasErrors('attr'), 'Failed for value: ' . var_export($value, true));
            $this->assertSame($value, $model->attr);
        }
    }
}

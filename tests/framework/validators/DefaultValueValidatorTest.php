<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use stdclass;
use yii\base\DynamicModel;
use yii\validators\DefaultValueValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class DefaultValueValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateAttribute(): void
    {
        $val = new DefaultValueValidator();
        $val->value = 'test_value';
        $obj = new stdclass();
        $obj->attrA = 'attrA';
        $obj->attrB = null;
        $obj->attrC = '';
        // original values to chek which attritubes where modified
        $objB = clone $obj;
        $val->validateAttribute($obj, 'attrB');
        $this->assertEquals($val->value, $obj->attrB);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->value = 'new_test_value';
        $obj = clone $objB; // get clean object
        $val->validateAttribute($obj, 'attrC');
        $this->assertEquals('new_test_value', $obj->attrC);
        $this->assertEquals($objB->attrA, $obj->attrA);
        $val->validateAttribute($obj, 'attrA');
        $this->assertEquals($objB->attrA, $obj->attrA);
    }

    public function testValidateAttributeWithClosure(): void
    {
        $val = new DefaultValueValidator();
        $val->value = function ($model, $attribute) {
            return $attribute . '_default';
        };
        $obj = new stdclass();
        $obj->attrA = null;
        $obj->attrB = 'existing';
        $val->validateAttribute($obj, 'attrA');
        $this->assertSame('attrA_default', $obj->attrA);
        $val->validateAttribute($obj, 'attrB');
        $this->assertSame('existing', $obj->attrB);
    }

    public function testValidateAssignsDefaultOnlyToEmptyValues(): void
    {
        $model = new DynamicModel([
            'null' => null,
            'emptyString' => '',
            'emptyArray' => [],
            'zero' => 0,
            'zeroString' => '0',
            'false' => false,
        ]);

        $model->addRule(
            ['null', 'emptyString', 'emptyArray', 'zero', 'zeroString', 'false'],
            'default',
            ['value' => 'fallback'],
        );

        $this->assertTrue(
            $model->validate(),
            'Default rule must never add errors.',
        );
        $this->assertSame(
            [
                'null' => 'fallback',
                'emptyString' => 'fallback',
                'emptyArray' => 'fallback',
                'zero' => 0,
                'zeroString' => '0',
                'false' => false,
            ],
            $model->getAttributes(),
            'Only empty values must be replaced; falsy non-empty values must be preserved.',
        );
    }

    public function testValidateAttributePassesModelToClosure(): void
    {
        $model = new DynamicModel(['attr' => null]);

        $received = [];

        $val = new DefaultValueValidator([
            'value' => function ($model, $attribute) use (&$received) {
                $received = [$model, $attribute];

                return 'computed';
            },
        ]);

        $val->validateAttribute($model, 'attr');

        $this->assertSame(
            [$model, 'attr'],
            $received,
            'Closure must receive the model and attribute name.',
        );
        $this->assertSame(
            'computed',
            $model['attr'],
            'Closure result must be assigned.',
        );
    }
}

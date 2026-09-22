<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\BooleanValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stubs\ViewStub;
use yiiunit\TestCase;

/**
 * @group validators
 */
class BooleanValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValue(): void
    {
        $val = new BooleanValidator();
        $this->assertTrue($val->validate(true));
        $this->assertTrue($val->validate(false));
        $this->assertTrue($val->validate('0'));
        $this->assertTrue($val->validate('1'));
        $this->assertFalse($val->validate('5'));
        $this->assertFalse($val->validate(null));
        $this->assertFalse($val->validate([]));
        $val->strict = true;
        $this->assertTrue($val->validate('0'));
        $this->assertTrue($val->validate('1'));
        $this->assertFalse($val->validate(true));
        $this->assertFalse($val->validate(false));
        $val->trueValue = true;
        $val->falseValue = false;
        $this->assertFalse($val->validate('0'));
        $this->assertFalse($val->validate([]));
        $this->assertTrue($val->validate(true));
        $this->assertTrue($val->validate(false));
    }

    public function testValidateAttributeAndError(): void
    {
        $obj = new FakedValidationModel();
        $obj->attrA = true;
        $obj->attrB = '1';
        $obj->attrC = '0';
        $obj->attrD = [];
        $val = new BooleanValidator();
        $val->validateAttribute($obj, 'attrA');
        $this->assertFalse($obj->hasErrors('attrA'));
        $val->validateAttribute($obj, 'attrC');
        $this->assertFalse($obj->hasErrors('attrC'));
        $val->strict = true;
        $val->validateAttribute($obj, 'attrB');
        $this->assertFalse($obj->hasErrors('attrB'));
        $val->validateAttribute($obj, 'attrD');
        $this->assertTrue($obj->hasErrors('attrD'));
    }

    public function testErrorMessage(): void
    {
        $validator = new BooleanValidator([
            'trueValue' => true,
            'falseValue' => false,
            'strict' => true,
        ]);
        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertEquals('the input value must be either "true" or "false".', $errorMessage);

        $obj = new FakedValidationModel();
        $obj->attrA = true;
        $obj->attrB = '1';
        $obj->attrC = '0';
        $obj->attrD = [];

        $this->assertEquals(
            'yii.validation.boolean(value, messages, {"trueValue":true,"falseValue":false,"message":"attrB must be either \u0022true\u0022 or \u0022false\u0022.","skipOnEmpty":1,"strict":1});',
            $validator->clientValidateAttribute($obj, 'attrB', new ViewStub())
        );
    }

    public function testErrorMessageWithCustomValues(): void
    {
        $validator = new BooleanValidator([
            'trueValue' => 'YES',
            'falseValue' => 'NO',
            'strict' => true,
        ]);

        $error = null;

        $result = $validator->validate('someIncorrectValue', $error);

        $this->assertFalse(
            $result,
            'A value outside the custom pair must be rejected.',
        );
        $this->assertSame(
            'the input value must be either "YES" or "NO".',
            $error,
            'Message must interpolate the custom values.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testGetClientOptionsWithCustomValues(): void
    {
        $validator = new BooleanValidator([
            'trueValue' => 'YES',
            'falseValue' => 'NO',
            'strict' => true,
            'skipOnEmpty' => true,
        ]);
        $model = new FakedValidationModel();

        $this->assertSame(
            [
                'trueValue' => 'YES',
                'falseValue' => 'NO',
                'message' => 'attrB must be either "YES" or "NO".',
                'skipOnEmpty' => 1,
                'strict' => 1,
            ],
            $validator->getClientOptions($model, 'attrB'),
            'Options must carry the custom values and the interpolated message.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testClientValidateAttributeWithDefaults(): void
    {
        $validator = new BooleanValidator();
        $model = new FakedValidationModel();

        $this->assertSame(
            'yii.validation.boolean(value, messages, {"trueValue":"1","falseValue":"0","message":"attrB must be either \u00221\u0022 or \u00220\u0022.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($model, 'attrB', new ViewStub()),
            'Default configuration must emit string values and omit `strict`.',
        );
    }
}

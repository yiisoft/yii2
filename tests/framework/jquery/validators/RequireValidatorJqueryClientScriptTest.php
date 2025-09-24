<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\RequiredValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class RequireValidatorJqueryClientScriptTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RequiredValidator();

        $modelValidator->attrA = 'test_value';

        $this->assertSame(
            'yii.validation.required(value, messages, {"message":"attrA cannot be blank."});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            ['message' => 'attrA cannot be blank.'],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('', $errorMessage);

        $this->assertSame(
            'the input value cannot be blank.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithRequiredValue(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RequiredValidator(['requiredValue' => 'expected_value']);

        $modelValidator->attrA = 'expected_value';

        $this->assertSame(
            'yii.validation.required(value, messages, {"message":"attrA must be \u0022expected_value\u0022.",' .
            '"requiredValue":"expected_value"});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'attrA must be "expected_value".',
                'requiredValue' => 'expected_value',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be "expected_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithStrictMode(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RequiredValidator(['strict' => true]);

        $modelValidator->attrA = 'test_value';

        $this->assertSame(
            'yii.validation.required(value, messages, {"message":"attrA cannot be blank.","strict":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'attrA cannot be blank.',
                'strict' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate(null, $errorMessage);

        $this->assertSame(
            'the input value cannot be blank.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new RequiredValidator(
            [
                'requiredValue' => 'test_value',
                'strict' => true,
            ],
        );

        $modelValidator->attrA = 'test_value';

        $this->assertNull(
            $validator->clientScript,
            "'ClientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}

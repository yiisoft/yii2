<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yii\tests\jquery\validators;

use Yii;
use yii\validators\CompareValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class CompareValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be equal to "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test_value';

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":"test_value","skipOnEmpty":1,"message":"attrB must be equal to \u0022test_value\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareValue' => 'test_value',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "test_value".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testClientValidateAttributeWithClosureCompareValue(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): string => 'closure_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be equal to "closure_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );

        $modelValidator = new FakedValidationModel();

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":"closure_value","skipOnEmpty":1,"message":"attrA must be equal to \u0022closure_value\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareValue' => 'closure_value',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "closure_value".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testClientValidateAttributeWithNullCompareAttribute(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test';
        $modelValidator->attrA_repeat = 'test';

        $validator = new CompareValidator(
            [
                'compareAttribute' => 'attrA_repeat',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareAttribute":"fakedvalidationmodel-attra_repeat","compareAttributeName":"FakedValidationModel[attrA_repeat]","skipOnEmpty":1,"message":"attrA must be equal to \u0022attrA_repeat\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareAttribute' => 'fakedvalidationmodel-attra_repeat',
                'compareAttributeName' => 'FakedValidationModel[attrA_repeat]',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "attrA_repeat".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be equal to "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test_value';

        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );
    }
}

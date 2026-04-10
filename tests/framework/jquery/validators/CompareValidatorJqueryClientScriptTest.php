<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\validators\CompareValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see CompareValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class CompareValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 'test_value';

        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":"test_value","skipOnEmpty":1,"message":"attrA must be equal to \u0022test_value\u0022."}, \$form);
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareValue' => 'test_value',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "test_value".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be equal to "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithClosureCompareValue(): void
    {
        $modelValidator = new FakedValidationModel();

        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): string => 'closure_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":"closure_value","skipOnEmpty":1,"message":"attrA must be equal to \u0022closure_value\u0022."}, \$form);
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareValue' => 'closure_value',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "closure_value".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be equal to "closure_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
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

        self::assertSame(
            <<<JS
            yii.validation.compare(value, messages, {"operator":"==","type":"string","compareAttribute":"fakedvalidationmodel-attra_repeat","compareAttributeName":"FakedValidationModel[attrA_repeat]","skipOnEmpty":1,"message":"attrA must be equal to \u0022attrA_repeat\u0022."}, \$form);
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'operator' => '==',
                'type' => 'string',
                'compareAttribute' => 'fakedvalidationmodel-attra_repeat',
                'compareAttributeName' => 'FakedValidationModel[attrA_repeat]',
                'skipOnEmpty' => 1,
                'message' => 'attrA must be equal to "attrA_repeat".',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test_value';

        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        self::assertNull(
            $validator->clientScript,
            "Should be 'null' when 'useJquery' is 'false'.",
        );
        self::assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            "Should return 'null' value.",
        );
        self::assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return an empty array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be equal to "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}

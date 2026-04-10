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
use yii\validators\RequiredValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see RequiredValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class RequiredValidatorJqueryClientScriptTest extends TestCase
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

        $validator = new RequiredValidator();

        self::assertSame(
            <<<JS
            yii.validation.required(value, messages, {"message":"attrA cannot be blank."});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            ['message' => 'attrA cannot be blank.'],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('', $errorMessage);

        self::assertSame(
            'the input value cannot be blank.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithRequiredValue(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'expected_value';

        $validator = new RequiredValidator(['requiredValue' => 'expected_value']);

        self::assertSame(
            <<<JS
            yii.validation.required(value, messages, {"message":"attrA must be \u0022expected_value\u0022.","requiredValue":"expected_value"});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'message' => 'attrA must be "expected_value".',
                'requiredValue' => 'expected_value',
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be "expected_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithStrictMode(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test_value';

        $validator = new RequiredValidator(['strict' => true]);

        self::assertSame(
            <<<JS
            yii.validation.required(value, messages, {"message":"attrA cannot be blank.","strict":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'message' => 'attrA cannot be blank.',
                'strict' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate(null, $errorMessage);

        self::assertSame(
            'the input value cannot be blank.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test_value';

        $validator = new RequiredValidator(
            [
                'requiredValue' => 'test_value',
                'strict' => true,
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
            'the input value must be "test_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}

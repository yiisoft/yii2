<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};
use Yii;
use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yii\validators\Validator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\providers\CompareValidatorProvider;
use yiiunit\TestCase;

/**
 * Unit test for {@see CompareValidator}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('validators')]
final class CompareValidatorTest extends TestCase
{
    use ClientScriptDispatchTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function createValidatorInstance(array $config = []): Validator
    {
        return new CompareValidator(array_merge(['compareValue' => 'foo'], $config));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testValidateValueException(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('CompareValidator::compareValue must be set.');

        $validator = new CompareValidator();

        $validator->validate('val');
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'validateValue')]
    public function testValidateValue(
        array $validatorConfig,
        mixed $inputValue,
        bool $expectedResult,
        string $expectedMessage,
    ): void {
        $validator = new CompareValidator($validatorConfig);

        self::assertSame(
            $expectedResult,
            $validator->validate($inputValue),
            $expectedMessage,
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'validateAttribute')]
    public function testValidateAttribute(
        array $validatorConfig,
        array $attributes,
        string $validateAttribute,
        bool $expectedHasErrors,
        string $expectedMessage,
        array $additionalErrors = [],
    ): void {
        $validator = new CompareValidator($validatorConfig);

        $model = FakedValidationModel::createWithAttributes($attributes);

        foreach ($additionalErrors as $attr => $error) {
            $model->addError($attr, $error);
        }

        $validator->validateAttribute($model, $validateAttribute);

        self::assertSame(
            $expectedHasErrors,
            $model->hasErrors($validateAttribute),
            $expectedMessage,
        );
    }

    public function testValidateAttributeWithArrayValue(): void
    {
        $validator = new CompareValidator();

        $model = new FakedValidationModel();

        $model->attr = ['test_val'];

        $validator->validateAttribute($model, 'attr');

        self::assertTrue(
            $model->hasErrors('attr'),
            'Validation should fail when attribute value is an array.',
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'attributeErrorMessages')]
    public function testAttributeErrorMessages(
        string $attribute,
        string $operator,
        int|string $compareTarget,
        string $expectedError,
        string $compareProperty,
    ): void {
        $validator = new CompareValidator();

        $validator->operator = $operator;
        $validator->message = null;

        $model = FakedValidationModel::createWithAttributes(
            [
                'attr1' => 1,
                'attr2' => 2,
                'attrN' => 2,
            ],
        );

        $validator->init();

        $validator->{$compareProperty} = $compareTarget;

        $validator->validateAttribute($model, $attribute);

        $errors = $model->getErrors($attribute);

        self::assertNotEmpty(
            $errors,
            "Validation should produce an error for operator '{$operator}'.",
        );
        self::assertSame(
            $expectedError,
            $errors[0],
            "Error message should match expected format for operator '{$operator}'.",
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'validateAttributeOperators')]
    public function testValidateAttributeOperators(
        string $operator,
        mixed $inputValue,
        int $compareValue,
        bool $expectedValid,
    ): void {
        $validator = new CompareValidator(
            [
                'operator' => $operator,
                'compareValue' => $compareValue,
            ],
        );

        $model = new FakedValidationModel();

        $model->attr_test = $inputValue;

        $validator->validateAttribute($model, 'attr_test');

        self::assertSame(
            $expectedValid,
            !$model->hasErrors('attr_test'),
            "Operator '{$operator}' with value '{$inputValue}' against '{$compareValue}'.",
        );
    }

    public function testEnsureMessageSetOnInit(): void
    {
        $operators = ['===', '!=', '!==', '>', '>=', '<', '<='];

        foreach ($operators as $operator) {
            $validator = new CompareValidator(['operator' => $operator]);

            self::assertTrue(
                strlen($validator->message) > 1,
                "Message should be set on 'init' for operator '{$operator}'.",
            );
        }

        $this->expectException(InvalidConfigException::class);

        new CompareValidator(['operator' => '<>']);
    }

    public function testValidateValueWithTypeNumber(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => 10,
                'type' => CompareValidator::TYPE_NUMBER,
            ],
        );

        self::assertTrue(
            $validator->validate(10),
            "Integer '10' should equal compareValue '10'.",
        );
        self::assertTrue(
            $validator->validate('10'),
            "String '10' should equal compareValue '10'.",
        );
        self::assertTrue(
            $validator->validate(10.0),
            "Float '10.0' should equal compareValue '10'.",
        );
        self::assertFalse(
            $validator->validate(11),
            "Integer '11' should not equal compareValue '10'.",
        );
    }

    public function testCompareValuesWithTypeNumber(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => 100,
                'type' => CompareValidator::TYPE_NUMBER,
                'operator' => '>',
            ],
        );

        self::assertTrue(
            $validator->validate(101),
            "Integer '101' should be greater than '100'.",
        );
        self::assertFalse(
            $validator->validate(100),
            "Integer '100' should not be greater than '100'.",
        );
        self::assertFalse(
            $validator->validate(99),
            "Integer '99' should not be greater than '100'.",
        );

        $validator->operator = '<';
        $validator->message = null;

        $validator->init();

        self::assertTrue(
            $validator->validate(99),
            "'99' should be less than '100'.",
        );
        self::assertFalse(
            $validator->validate(100),
            "'100' should not be less than '100'.",
        );
        self::assertFalse(
            $validator->validate(101),
            "'101' should not be less than '100'.",
        );
    }

    public function testValidateAttributeWithClosure(): void
    {
        $expectedValue = 42;
        $closureExecuted = false;
        $receivedModel = null;
        $receivedAttribute = null;

        $closure = static function (
            FakedValidationModel $model,
            string $attribute,
        ) use (
            $expectedValue,
            &$closureExecuted,
            &$receivedModel,
            &$receivedAttribute,
        ): int {
            $closureExecuted = true;
            $receivedModel = $model;
            $receivedAttribute = $attribute;

            return $expectedValue;
        };

        $validator = new CompareValidator(
            [
                'compareValue' => $closure,
            ],
        );

        $model = new FakedValidationModel();

        $model->attr_test = $expectedValue;

        $validator->validateAttribute($model, 'attr_test');

        self::assertTrue(
            $closureExecuted,
            'Closure should be executed during validation.',
        );
        self::assertSame(
            $model,
            $receivedModel,
            'Closure should receive the model as first parameter.',
        );
        self::assertSame(
            'attr_test',
            $receivedAttribute,
            'Closure should receive the attribute as second parameter.',
        );
        self::assertFalse(
            $model->hasErrors('attr_test'),
            'Validation should pass when values match.',
        );
    }

    public function testClientValidateAttributeReturnsNullWithoutClientScript(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $model = new FakedValidationModel();

        $model->attrA = 'test_value';

        self::assertNull(
            $validator->clientValidateAttribute($model, 'attrA', Yii::$app->getView()),
            "'clientValidateAttribute()' should return 'null' when no client script is set.",
        );
        self::assertSame(
            [],
            $validator->getClientOptions($model, 'attrA'),
            "'getClientOptions()' should return an empty array when no client script is set.",
        );

        $errorMessage = null;

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be equal to "test_value".',
            $errorMessage,
            'Error message should match expected format.',
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'defaultMessagePerOperator')]
    public function testDefaultMessagePerOperator(string $operator, string $expectedSubstring): void
    {
        $validator = new CompareValidator(
            [
                'operator' => $operator,
                'compareValue' => 1,
            ],
        );

        self::assertStringContainsString(
            $expectedSubstring,
            $validator->message,
            "Default message for operator '{$operator}' should contain '{$expectedSubstring}'.",
        );
    }

    public function testValidateAttributeCompareAttributeHasErrorMessage(): void
    {
        $validator = new CompareValidator(
            [
                'compareAttribute' => 'attr_x',
                'skipOnError' => false,
            ],
        );

        $model = FakedValidationModel::createWithAttributes(
            [
                'attr_x' => 10,
                'attr_y' => 10,
            ],
        );

        $model->addError('attr_x', 'invalid');

        $validator->validateAttribute($model, 'attr_y');

        $errors = $model->getErrors('attr_y');

        self::assertStringContainsString(
            'attr_x',
            $errors[0],
            'Error message should reference the compare attribute.',
        );
        self::assertStringContainsString(
            'is invalid',
            $errors[0],
            'Error message should indicate the compare attribute is invalid.',
        );
    }

    public function testValidateValueErrorContainsCompareValue(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => 'expected',
            ],
        );

        $error = null;

        $validator->validate('wrong', $error);

        self::assertStringContainsString(
            'expected',
            $error,
            'Validation error should contain the compare value.',
        );
    }

    public function testTypeNumberCastsToFloat(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => '10',
                'type' => CompareValidator::TYPE_NUMBER,
                'operator' => '===',
            ],
        );

        self::assertTrue(
            $validator->validate('10'),
            "String '10' should equal after float cast.",
        );
        self::assertTrue(
            $validator->validate(10),
            "Integer '10' should equal after float cast.",
        );
        self::assertTrue(
            $validator->validate(10.0),
            "Float '10.0' should equal after float cast.",
        );
    }

    public function testCompareAttributeErrorEarlyReturn(): void
    {
        $validator = new CompareValidator(
            [
                'compareAttribute' => 'attr_x',
                'skipOnError' => false,
            ],
        );

        $model = FakedValidationModel::createWithAttributes(
            [
                'attr_x' => 5,
                'attr_y' => 99,
            ],
        );

        $model->addError('attr_x', 'bad value');
        $validator->validateAttribute($model, 'attr_y');
        $errors = $model->getErrors('attr_y');

        self::assertCount(
            1,
            $errors,
            'Should produce exactly one error on early return.',
        );
        self::assertStringContainsString(
            'is invalid',
            $errors[0] ?? '',
            'Error should indicate the compare attribute is invalid.',
        );
    }

    public function testDefaultOperatorFallsThrough(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => 5,
            ],
        );

        $validator->operator = '<>';

        self::assertFalse(
            $validator->validate(5),
            "Unknown operator should always return 'false'.",
        );
        self::assertFalse(
            $validator->validate(999),
            "Unknown operator should always return 'false'.",
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'numericTypeConversionProvider')]
    public function testValidateAttributeWithNumericTypeConversion(
        array $validatorConfig,
        int|string $attributeValue,
        string|null $compareAttributeValue,
        bool $expectedValidation,
        string $expectedMessage,
    ): void {
        $validator = new CompareValidator($validatorConfig);

        $model = new FakedValidationModel();

        $model->attr_test = $attributeValue;

        if ($compareAttributeValue !== null) {
            $model->attr_compare = $compareAttributeValue;
        }

        $validator->validateAttribute($model, 'attr_test');

        self::assertSame(
            $expectedValidation,
            $model->hasErrors('attr_test'),
            $expectedMessage,
        );
    }

    #[DataProviderExternal(CompareValidatorProvider::class, 'numericValueConversionProvider')]
    public function testValidateValueWithNumericTypeConversion(
        array $validatorConfig,
        float|int|string $attributeValue,
        bool $expectedResult,
        string $expectedMessage,
    ): void {
        $validator = new CompareValidator($validatorConfig);

        $result = $validator->validate($attributeValue);

        self::assertSame(
            $expectedResult,
            $result,
            $expectedMessage,
        );
    }

    public function testValidateAttributeWithClosureFailure(): void
    {
        $expectedValue = 100;
        $actualValue = 50;

        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): int => $expectedValue,
            ],
        );

        $model = new FakedValidationModel();

        $model->attr_test = $actualValue;

        $validator->validateAttribute($model, 'attr_test');

        self::assertTrue(
            $model->hasErrors('attr_test'),
            'Validation should fail when values do not match.',
        );
    }

    public function testValidateAttributeWithClosureAndOperator(): void
    {
        $compareValue = 75;

        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): int => $compareValue,
                'operator' => '>',
            ],
        );

        $model = new FakedValidationModel();

        $model->attr_test = 100;

        $validator->validateAttribute($model, 'attr_test');

        self::assertFalse(
            $model->hasErrors('attr_test'),
            "Validation should pass when '100' > '75'.",
        );

        $model2 = new FakedValidationModel();

        $model2->attr_test = 50;

        $validator->validateAttribute($model2, 'attr_test');

        self::assertTrue(
            $model2->hasErrors('attr_test'),
            "Validation should fail when '50' is not > '75'.",
        );
    }

    public function testValidateAttributeWithClosureCompareValueServerSide(): void
    {
        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): string => 'closure_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $errorMessage = null;

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be equal to "closure_value".',
            $errorMessage,
            'Error message should match expected format.',
        );
    }
}

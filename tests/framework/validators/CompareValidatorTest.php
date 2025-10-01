<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use Yii;
use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class CompareValidatorTest extends TestCase
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

    public function testValidateValueException(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $val = new CompareValidator();
        $val->validate('val');
    }

    public function testValidateValue(): void
    {
        $value = 18449;
        // default config
        $val = new CompareValidator(['compareValue' => $value]);
        $this->assertTrue($val->validate($value));
        $this->assertTrue($val->validate((string) $value));
        $this->assertFalse($val->validate($value + 1));

        // Using a closure for compareValue
        $val = new CompareValidator(['compareValue' => fn() => $value]);
        $this->assertTrue($val->validate($value));
        $this->assertTrue($val->validate((string) $value));
        $this->assertFalse($val->validate($value + 1));

        foreach ($this->getOperationTestData($value) as $op => $tests) {
            $val = new CompareValidator(['compareValue' => $value]);
            $val->operator = $op;
            foreach ($tests as $test) {
                $this->assertEquals($test[1], $val->validate($test[0]), "Testing $op");
            }
        }
    }

    /**
     * @phpstan-return array<string, array<int, array{mixed, bool}>>
     */
    protected function getOperationTestData(int $value): array
    {
        return [
            '===' => [
                [$value, true],
                [(string) $value, true],
                [(float) $value, true],
                [$value + 1, false],
            ],
            '!=' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [$value + 0.00001, true],
                [false, true],
            ],
            '!==' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [false, true],
            ],
            '>' => [
                [$value, false],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '>=' => [
                [$value, true],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '<' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, true],
            ],
            '<=' => [
                [$value, true],
                [$value + 1, false],
                [$value - 1, true],
            ],
            /*'non-op' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, false],
            ],*/
        ];
    }

    public function testValidateAttribute(): void
    {
        // invalid-array
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr = ['test_val'];
        $val->validateAttribute($model, 'attr');
        $this->assertTrue($model->hasErrors('attr'));
        $val = new CompareValidator(['compareValue' => 'test-string']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string-false';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        // assume: _repeat
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string2';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        // not existing op
        $val = new CompareValidator();
        $val->operator = '<>';
        $model = FakedValidationModel::createWithAttributes(['attr_o' => 5, 'attr_o_repeat' => 5]);
        $val->validateAttribute($model, 'attr_o');
        $this->assertTrue($model->hasErrors('attr_o'));
        // compareAttribute has validation error
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => false]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 10, 'attr_y' => 10]);
        $model->addError('attr_x', 'invalid value');
        $val->validateAttribute($model, 'attr_y');
        $this->assertTrue($model->hasErrors('attr_x'));
        $this->assertTrue($model->hasErrors('attr_y'));
        // compareAttribute has validation error but rule has skipOnError
        $val = new CompareValidator(['compareAttribute' => 'attr_x', 'skipOnError' => true]);
        $model = FakedValidationModel::createWithAttributes(['attr_x' => 10, 'attr_y' => 10]);
        $model->addError('attr_x', 'invalid value');
        $val->validateAttribute($model, 'attr_y');
        $this->assertTrue($model->hasErrors('attr_x'));
        $this->assertFalse($model->hasErrors('attr_y'));
    }

    public function testAttributeErrorMessages(): void
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr1' => 1,
            'attr2' => 2,
            'attrN' => 2,
        ]);

        foreach ($this->getTestDataForMessages() as $data) {
            $model->clearErrors($data[0]);
            $model->clearErrors($data[2]);
            $validator = new CompareValidator();
            $validator->operator = $data[1];
            $validator->message = null;
            $validator->init(); // reload messages
            $validator->{$data[4]} = $data[2];
            $validator->validateAttribute($model, $data[0]);
            $error = $model->getErrors($data[0])[0];
            $this->assertEquals($data[3], $error);
        }
    }

    /**
     * @phpstan-return array<array-key, array{string, string, int|string, string, string}>
     */
    protected function getTestDataForMessages(): array
    {
        return [
            ['attr1', '==', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attr1', '===', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attrN', '!=', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attrN', '!==', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attr1', '>', 2, 'attr1 must be greater than "2".', 'compareValue'],
            ['attr1', '>=', 2, 'attr1 must be greater than or equal to "2".', 'compareValue'],
            ['attr2', '<', 1, 'attr2 must be less than "1".', 'compareValue'],
            ['attr2', '<=', 1, 'attr2 must be less than or equal to "1".', 'compareValue'],

            ['attr1', '==', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attr1', '===', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!=', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!==', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attr1', '>', 'attr2', 'attr1 must be greater than "attr2".', 'compareAttribute'],
            ['attr1', '>=', 'attr2', 'attr1 must be greater than or equal to "attr2".', 'compareAttribute'],
            ['attr2', '<', 'attr1', 'attr2 must be less than "attr1".', 'compareAttribute'],
            ['attr2', '<=', 'attr1', 'attr2 must be less than or equal to "attr1".', 'compareAttribute'],
        ];
    }

    public function testValidateAttributeOperators(): void
    {
        $value = 55;
        foreach ($this->getOperationTestData($value) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator, 'compareValue' => $value]);
            foreach ($tests as $test) {
                $model = new FakedValidationModel();
                $model->attr_test = $test[0];
                $val->validateAttribute($model, 'attr_test');
                $this->assertEquals($test[1], !$model->hasErrors('attr_test'));
            }
        }
    }

    public function testEnsureMessageSetOnInit(): void
    {
        foreach ($this->getOperationTestData(1337) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator]);
            $this->assertTrue(strlen($val->message) > 1);
        }
        try {
            new CompareValidator(['operator' => '<>']);
        } catch (InvalidConfigException) {
            return;
        } catch (\Exception $e) {
            $this->fail('InvalidConfigException expected' . $e::class . 'received');

            return;
        }
        $this->fail('InvalidConfigException expected none received');
    }

    /**
     * @dataProvider \yiiunit\framework\validators\providers\CompareValidatorProvider::numericTypeConversionProvider
     *
     * @phpstan-param array<string, mixed> $validatorConfig
     */
    public function testValidateAttributeWithNumericTypeConversion(
        array $validatorConfig,
        int|string $attributeValue,
        string|null $compareAttributeValue,
        bool $expectedValidation,
        string $expectedMessage,
    ): void {
        $model = new FakedValidationModel();

        $model->attr_test = $attributeValue;

        $validator = new CompareValidator($validatorConfig);

        if ($compareAttributeValue !== null) {
            $model->attr_compare = $compareAttributeValue;
        }

        $validator->validateAttribute($model, 'attr_test');

        $this->assertSame($expectedValidation, $model->hasErrors('attr_test'), $expectedMessage);
    }

    /**
     * @dataProvider \yiiunit\framework\validators\providers\CompareValidatorProvider::numericValueConversionProvider
     */
    public function testValidateValueWithNumericTypeConversion(
        array $validatorConfig,
        float|int|string $attributeValue,
        bool $expectedResult,
        string $expectedMessage,
    ): void {
        $validator = new CompareValidator($validatorConfig);

        $result = $validator->validate($attributeValue);

        $this->assertSame($expectedResult, $result, $expectedMessage);
    }

    public function testValidateAttributeWithClosure(): void
    {
        $expectedValue = 42;
        $closureExecuted = false;

        $closure = static function() use ($expectedValue, &$closureExecuted): int {
            $closureExecuted = true;

            return $expectedValue;
        };

        $model = new FakedValidationModel();

        $model->attr_test = $expectedValue;

        $validator = new CompareValidator(['compareValue' => $closure]);

        $validator->validateAttribute($model, 'attr_test');

        $this->assertTrue(
            $closureExecuted,
            'Closure should be executed during validation',
        );
        $this->assertFalse(
            $model->hasErrors('attr_test'),
            'Validation should pass when values match',
        );
    }

    public function testValidateAttributeWithClosureFailure(): void
    {
        $expectedValue = 100;
        $actualValue = 50;

        $model = new FakedValidationModel();

        $model->attr_test = $actualValue;

        $validator = new CompareValidator(['compareValue' => static fn(): int => $expectedValue]);

        $validator->validateAttribute($model, 'attr_test');

        $this->assertTrue(
            $model->hasErrors('attr_test'),
            'Validation should fail when values do not match',
        );
    }

    public function testValidateAttributeWithClosureAndOperator(): void
    {
        $compareValue = 75;

        $model = new FakedValidationModel();

        $model->attr_test = 100;

        $validator = new CompareValidator(
            [
                'compareValue' => static fn(): int => $compareValue,
                'operator' => '>',
            ],
        );

        $validator->validateAttribute($model, 'attr_test');

        $this->assertFalse(
            $model->hasErrors('attr_test'),
            "Validation should pass when '100' > '75'.",
        );

        $model2 = new FakedValidationModel();

        $model2->attr_test = 50;

        $validator->validateAttribute($model2, 'attr_test');

        $this->assertTrue(
            $model2->hasErrors('attr_test'),
            "Validation should fail when '50' is not > '75'."
        );
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new CompareValidator(
            [
                'compareValue' => 'test_value',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $modelValidator->attrA = 'test_value';

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":' .
            '"test_value","skipOnEmpty":1,"message":"attrA must be equal to \u0022test_value\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->getView()),
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

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
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

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareValue":' .
            '"closure_value","skipOnEmpty":1,"message":"attrA must be equal to \u0022closure_value\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->getView()),
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

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value must be equal to "closure_value".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithNullCompareAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new CompareValidator(
            [
                'compareAttribute' => 'attrA_repeat',
                'operator' => '==',
                'type' => CompareValidator::TYPE_STRING,
            ],
        );

        $modelValidator->attrA = 'test';
        $modelValidator->attrA_repeat = 'test';

        $this->assertSame(
            'yii.validation.compare(value, messages, {"operator":"==","type":"string","compareAttribute":' .
            '"fakedvalidationmodel-attra_repeat","compareAttributeName":"FakedValidationModel[attrA_repeat]",' .
            '"skipOnEmpty":1,"message":"attrA must be equal to \u0022attrA_repeat\u0022."}, $form);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->getView()),
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
}

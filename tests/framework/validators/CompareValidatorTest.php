<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

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

    protected function getOperationTestData($value)
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

    protected function getTestDataForMessages()
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
     * @dataProvider numericTypeConversionProvider
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
     * @dataProvider numericValueConversionProvider
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

    public static function numericTypeConversionProvider(): array
    {
        return [
            'invalid numeric greater than failure case' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 100,
                    'operator' => '>',
                ],
                '50',
                null,
                true,
                'Numeric comparison should fail when condition is not met.',
            ],
            'valid closure with numeric type conversion' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => static fn(): string => '42.5',
                    'operator' => '==',
                ],
                '42.5',
                null,
                false,
                'Closure returning string should be converted to float for numeric comparison.',
            ],
            'valid compare attribute numeric type' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareAttribute' => 'attr_compare',
                    'operator' => '==',
                ],
                '42.0',
                '42',
                false,
                'Numeric type conversion should work with compareAttribute.',
            ],
            'valid decimal string comparison' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => '42.5',
                    'operator' => '==',
                ],
                '42.5',
                null,
                false,
                'Decimal values should be properly converted and compared.',
            ],
            'valid integer to float strict equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 42,
                    'operator' => '===',
                ],
                42,
                null,
                false,
                'Integer values should be converted to float for strict comparison.',
            ],
            'valid less than or equal operator' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 100,
                    'operator' => '<=',
                ],
                '100',
                null,
                false,
                'Less than or equal operator should work with numeric conversion.'
            ],
            'valid negative numbers comparison' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => -10.5,
                    'operator' => '==',
                ],
                '-10.5',
                null,
                false,
                'Negative numbers should be properly converted and compared.',
            ],
            'valid not equal operator success' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 50,
                    'operator' => '!=',
                ],
                '25',
                null,
                false,
                'Not equal operator should work with different numeric values.',
            ],
            'valid numeric greater than with strings' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => '10',
                    'operator' => '>',
                ],
                '20',
                null,
                false,
                'Numeric comparison with > operator should work with string numbers.',
            ],
            'valid string numeric to float equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 42.0,
                    'operator' => '==',
                ],
                '42',
                null,
                false,
                'String numeric value should be converted to float for comparison.',
            ],
            'valid zero values comparison' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 0,
                    'operator' => '==',
                ],
                '0.0',
                null,
                false,
                'Zero values should be properly converted and compared.',
            ],
        ];
    }

    public static function numericValueConversionProvider(): array
    {
        return [
            'invalid closure less than or equal' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => static fn(): int => 100,
                    'operator' => '<=',
                ],
                '150',
                false,
                "String '150' should not be less than or equal to '100'.",
            ],
            'invalid different numeric values' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 123.45,
                    'operator' => '==',
                ],
                '123.46',
                false,
                'Different numeric values should not be equal.',
            ],
            'invalid greater than' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => '10.5',
                    'operator' => '>',
                ],
                '5',
                false,
                "String '5' should not be greater than '10.5' after conversion.",
            ],
            'valid closure less than or equal (equal)' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => static fn(): int => 100,
                    'operator' => '<=',
                ],
                '100',
                true,
                "String '100' should be less than or equal to '100'.",
            ],
            'valid closure less than or equal (less)' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => static fn(): int => 100,
                    'operator' => '<=',
                ],
                '50',
                true,
                "String '50' should be less than or equal to '100'.",
            ],
            'valid float equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 123.45,
                    'operator' => '==',
                ],
                123.45,
                true,
                'Float values should be equal.',
            ],
            'valid greater than' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => '10.5',
                    'operator' => '>',
                ],
                '20',
                true,
                "String '20' should be greater than '10.5' after conversion.",
            ],
            'valid integer strict equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 42,
                    'operator' => '===',
                ],
                42,
                true,
                'Integer should be converted to float for strict comparison.',
            ],
            'valid negative number comparison' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => -5.5,
                    'operator' => '<',
                ],
                '-10',
                true,
                'Negative string should be converted and compared correctly.',
            ],
            'valid not equal operator' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 100,
                    'operator' => '!=',
                ],
                '99.9',
                true,
                'Not equal operator should work with numeric conversion.',
            ],
            'valid string to float equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 123.45,
                    'operator' => '==',
                ],
                '123.45',
                true,
                'String should be converted to float for comparison.',
            ],
            'valid string strict equality' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 42,
                    'operator' => '===',
                ],
                '42',
                true,
                'String should be converted to float for strict comparison.',
            ],
            'valid zero comparison' => [
                [
                    'type' => CompareValidator::TYPE_NUMBER,
                    'compareValue' => 0,
                    'operator' => '>=',
                ],
                '0.0',
                true,
                'Zero values should be properly converted and compared.',
            ],
        ];
    }
}

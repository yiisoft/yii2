<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators\providers;

use yii\validators\CompareValidator;

/**
 * Data provider for {@see \yiiunit\framework\validators\CompareValidatorTest} test cases.
 *
 * Provides representative input/output pairs for validation, attribute comparison, error messages, operator behavior,
 * and numeric type conversion scenarios.
 *
 * @copyright Copyright (c) 2008 Yii Software LLC.
 * @license https://www.yiiframework.com/license/
 */
final class CompareValidatorProvider
{
    public static function validateValue(): array
    {
        $value = 18449;

        return [
            'closure equal different value' => [
                ['compareValue' => static fn(): int => $value],
                $value + 1,
                false,
                'Closure returning different value should not validate as equal.',
            ],
            'closure equal same int' => [
                ['compareValue' => static fn(): int => $value],
                $value, true,
                'Closure returning same value should validate as equal.',
            ],
            'closure equal same string' => [
                ['compareValue' => static fn(): int => $value],
                (string) $value, true,
                'Closure returning same value as string should validate as equal.',
            ],
            'default equal different value' => [
                ['compareValue' => $value],
                $value + 1, false,
                'Different value should not validate as equal.',
            ],
            'default equal same int' => [
                ['compareValue' => $value],
                $value, true,
                'Same integer value should validate as equal.',
            ],
            'default equal same string' => [
                ['compareValue' => $value],
                (string) $value, true,
                'String cast of integer should validate as equal.',
            ],
            'greater than higher value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>',
                ],
                $value + 1, true,
                'Greater than with higher value should pass.',
            ],
            'greater than lower value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>',
                ],
                $value - 1, false,
                'Greater than with lower value should fail.',
            ],
            'greater than or equal higher value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>=',
                ],
                $value + 1,
                true,
                'Greater than or equal with higher value should pass.',
            ],
            'greater than or equal lower value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>=',
                ],
                $value - 1,
                false,
                'Greater than or equal with lower value should fail.',
            ],
            'greater than or equal same value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>=',
                ],
                $value,
                true,
                'Greater than or equal with same value should pass.',
            ],
            'greater than same value' => [
                [
                    'compareValue' => $value,
                    'operator' => '>',
                ],
                $value,
                false,
                'Greater than with same value should fail.',
            ],
            'less than higher value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<',
                ],
                $value + 1,
                false,
                'Less than with higher value should fail.',
            ],
            'less than lower value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<',
                ],
                $value - 1,
                true,
                'Less than with lower value should pass.',
            ],
            'less than or equal higher value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<=',
                ],
                $value + 1,
                false,
                'Less than or equal with higher value should fail.',
            ],
            'less than or equal lower value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<=',
                ],
                $value - 1,
                true,
                'Less than or equal with lower value should pass.',
            ],
            'less than or equal same value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<=',
                ],
                $value,
                true,
                'Less than or equal with same value should pass.',
            ],
            'less than same value' => [
                [
                    'compareValue' => $value,
                    'operator' => '<',
                ],
                $value,
                false,
                'Less than with same value should fail.',
            ],
            'not equal same float' => [
                [
                    'compareValue' => $value,
                    'operator' => '!=',
                ],
                (float) $value,
                false,
                'Not equal with float cast should fail.',
            ],
            'not equal same int' => [
                [
                    'compareValue' => $value,
                    'operator' => '!=',
                ],
                $value,
                false,
                'Not equal with same value should fail.',
            ],
            'not equal same string' => [
                [
                    'compareValue' => $value,
                    'operator' => '!=',
                ],
                (string) $value,
                false,
                'Not equal with string cast should fail.',
            ],
            'not equal slightly different' => [
                [
                    'compareValue' => $value,
                    'operator' => '!=',
                ],
                $value + 0.00001,
                true,
                'Not equal with slightly different value should pass.',
            ],
            'not equal with false' => [
                [
                    'compareValue' => $value,
                    'operator' => '!=',
                ],
                false,
                true,
                'Not equal with `false` should pass.',
            ],
            'strict equal different value' => [
                [
                    'compareValue' => $value,
                    'operator' => '===',
                ],
                $value + 1,
                false,
                'Strict equal with different value should fail.',
            ],
            'strict equal same float' => [
                [
                    'compareValue' => $value,
                    'operator' => '===',
                ],
                (float) $value,
                true,
                'Strict equal with float cast should pass (both cast to string).',
            ],
            'strict equal same int' => [
                [
                    'compareValue' => $value,
                    'operator' => '===',
                ],
                $value,
                true,
                'Strict equal with same integer should pass.',
            ],
            'strict equal same string' => [
                [
                    'compareValue' => $value,
                    'operator' => '===',
                ],
                (string) $value,
                true,
                'Strict equal with string cast should pass (both cast to string).',
            ],
            'strict not equal same float' => [
                [
                    'compareValue' => $value,
                    'operator' => '!==',
                ],
                (float) $value,
                false,
                'Strict not equal with float cast should fail.',
            ],
            'strict not equal same int' => [
                [
                    'compareValue' => $value,
                    'operator' => '!==',
                ],
                $value,
                false,
                'Strict not equal with same value should fail.',
            ],
            'strict not equal same string' => [
                [
                    'compareValue' => $value,
                    'operator' => '!==',
                ],
                (string) $value,
                false,
                'Strict not equal with string cast should fail.',
            ],
            'strict not equal with false' => [
                [
                    'compareValue' => $value,
                    'operator' => '!==',
                ],
                false,
                true,
                'Strict not equal with `false` should pass.',
            ],
        ];
    }

    public static function validateAttribute(): array
    {
        return [
            'compareAttribute equal match' => [
                ['compareAttribute' => 'attr_test_val'],
                [
                    'attr_test' => 'test-string',
                    'attr_test_val' => 'test-string',
                ],
                'attr_test',
                false,
                'Validation should pass when attribute matches compareAttribute.',
            ],
            'compareAttribute not equal' => [
                ['compareAttribute' => 'attr_test_val'],
                [
                    'attr_test' => 'test-string',
                    'attr_test_val' => 'test-string-false',
                ],
                'attr_test',
                true,
                'Validation should fail when attribute does not match compareAttribute.',
            ],
            'compareAttribute with error and skipOnError false' => [
                ['compareAttribute' => 'attr_x', 'skipOnError' => false],
                ['attr_x' => 10, 'attr_y' => 10],
                'attr_y',
                true,
                'Validation should fail when compareAttribute has errors and skipOnError is `false`.',
                ['attr_x' => 'invalid value']
            ],
            'compareAttribute with error and skipOnError true' => [
                ['compareAttribute' => 'attr_x', 'skipOnError' => true],
                ['attr_x' => 10, 'attr_y' => 10],
                'attr_y',
                false,
                'Validation should be skipped when compareAttribute has errors and skipOnError is `true`.',
                ['attr_x' => 'invalid value']
            ],
            'compareValue equal match' => [
                ['compareValue' => 'test-string'],
                ['attr_test' => 'test-string'],
                'attr_test',
                false,
                'Validation should pass when attribute matches compareValue.'
            ],
            'default _repeat attribute match' => [
                [],
                ['attr_test' => 'test-string', 'attr_test_repeat' => 'test-string'],
                'attr_test',
                false,
                'Validation should pass when attribute matches default _repeat attribute.'
            ],
            'default _repeat attribute mismatch' => [
                [],
                ['attr_test' => 'test-string', 'attr_test_repeat' => 'test-string2'],
                'attr_test',
                true,
                'Validation should fail when attribute does not match _repeat attribute.'
            ],
        ];
    }

    public static function attributeErrorMessages(): array
    {
        return [
            'equal compareAttribute' => [
                'attr1',
                '==',
                'attr2',
                'attr1 must be equal to "attr2".', 'compareAttribute',
            ],
            'equal compareValue' => [
                'attr1',
                '==',
                2,
                'attr1 must be equal to "2".',
                'compareValue',
            ],
            'greater than compareAttribute' => [
                'attr1',
                '>',
                'attr2',
                'attr1 must be greater than "attr2".',
                'compareAttribute',
            ],
            'greater than compareValue' => [
                'attr1',
                '>',
                2,
                'attr1 must be greater than "2".',
                'compareValue',
            ],
            'greater than or equal compareAttribute' => [
                'attr1',
                '>=',
                'attr2',
                'attr1 must be greater than or equal to "attr2".',
                'compareAttribute',
            ],
            'greater than or equal compareValue' => [
                'attr1',
                '>=',
                2,
                'attr1 must be greater than or equal to "2".',
                'compareValue',
            ],
            'less than compareAttribute' => [
                'attr2',
                '<',
                'attr1',
                'attr2 must be less than "attr1".',
                'compareAttribute',
            ],
            'less than compareValue' => [
                'attr2',
                '<',
                1,
                'attr2 must be less than "1".', 'compareValue',
            ],
            'less than or equal compareAttribute' => [
                'attr2',
                '<=',
                'attr1',
                'attr2 must be less than or equal to "attr1".', 'compareAttribute',
            ],
            'less than or equal compareValue' => [
                'attr2',
                '<=',
                1,
                'attr2 must be less than or equal to "1".', 'compareValue',
            ],
            'not equal compareAttribute' => [
                'attrN',
                '!=',
                'attr2',
                'attrN must not be equal to "attr2".', 'compareAttribute',
            ],
            'not equal compareValue' => [
                'attrN',
                '!=',
                2,
                'attrN must not be equal to "2".', 'compareValue',
            ],
            'strict equal compareAttribute' => [
                'attr1',
                '===',
                'attr2',
                'attr1 must be equal to "attr2".', 'compareAttribute',
            ],
            'strict equal compareValue' => [
                'attr1',
                '===',
                2,
                'attr1 must be equal to "2".', 'compareValue',
            ],
            'strict not equal compareAttribute' => [
                'attrN',
                '!==',
                'attr2',
                'attrN must not be equal to "attr2".', 'compareAttribute',
            ],
            'strict not equal compareValue' => [
                'attrN',
                '!==',
                2,
                'attrN must not be equal to "2".', 'compareValue',
            ],
        ];
    }

    public static function validateAttributeOperators(): array
    {
        $value = 55;

        return [
            'greater than higher' => [
                '>',
                $value + 1,
                $value,
                true,
            ],
            'greater than lower' => [
                '>',
                $value - 1,
                $value,
                false,
            ],
            'greater than or equal higher' => [
                '>=',
                $value + 1,
                $value,
                true,
            ],
            'greater than or equal lower' => [
                '>=',
                $value - 1,
                $value,
                false,
            ],
            'greater than or equal same' => [
                '>=',
                $value,
                $value,
                true,
            ],
            'greater than same' => [
                '>',
                $value,
                $value,
                false,
            ],
            'less than higher' => [
                '<',
                $value + 1,
                $value,
                false,
            ],
            'less than lower' => [
                '<',
                $value - 1,
                $value,
                true,
            ],
            'less than or equal higher' => [
                '<=',
                $value + 1,
                $value,
                false,
            ],
            'less than or equal lower' => [
                '<=',
                $value - 1,
                $value,
                true,
            ],
            'less than or equal same' => [
                '<=',
                $value,
                $value,
                true,
            ],
            'less than same' => [
                '<',
                $value,
                $value,
                false,
            ],
            'not equal same float' => [
                '!=',
                (float) $value,
                $value,
                false,
            ],
            'not equal same int' => [
                '!=',
                $value,
                $value,
                false,
            ],
            'not equal same string' => [
                '!=',
                (string) $value,
                $value,
                false,
            ],
            'not equal slightly different' => [
                '!=',
                $value + 0.00001,
                $value,
                true,
            ],
            'not equal with false' => [
                '!=',
                false,
                $value,
                true,
            ],
            'strict equal different' => [
                '===',
                $value + 1,
                $value,
                false,
            ],
            'strict equal same float' => [
                '===',
                (float) $value,
                $value,
                true,
            ],
            'strict equal same int' => [
                '===',
                $value,
                $value,
                true,
            ],
            'strict equal same string' => [
                '===',
                (string) $value,
                $value,
                true,
            ],
            'strict not equal same float' => [
                '!==',
                (float) $value,
                $value,
                false,
            ],
            'strict not equal same int' => [
                '!==',
                $value,
                $value,
                false,
            ],
            'strict not equal same string' => [
                '!==',
                (string) $value,
                $value,
                false,
            ],
            'strict not equal with false' => [
                '!==',
                false,
                $value,
                true,
            ],
        ];
    }

    public static function defaultMessagePerOperator(): array
    {
        return [
            'equal' => [
                '==',
                'equal to',
            ],
            'greater than or equal' => [
                '>=',
                'greater than or equal',
            ],
            'greater than' => [
                '>',
                'greater than',
            ],
            'less than or equal' => [
                '<=',
                'less than or equal',
            ],
            'less than' => [
                '<',
                'less than',
            ],
            'not equal' => [
                '!=',
                'not be equal',
            ],
            'strict equal' => [
                '===',
                'equal to',
            ],
            'strict not equal' => [
                '!==',
                'not be equal',
            ],
        ];
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
                'Less than or equal operator should work with numeric conversion.',
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

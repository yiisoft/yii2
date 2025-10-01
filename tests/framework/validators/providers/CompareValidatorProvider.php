<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\providers;

use yii\validators\CompareValidator;

/**
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 *
 * @since 2.2.0
 */
final class CompareValidatorProvider
{
    /**
     * Provides test cases for numeric type conversion and comparison.
     *
     * This provider supplies scenarios for validating numeric comparisons, including type coercion between strings,
     * integers, and floats, strict and loose operators, closure-based compare values, and edge cases such as zero and
     * negative numbers. It ensures that CompareValidator correctly handles numeric type conversion and operator logic.
     *
     * @return array test data for numeric type conversion and comparison.
     *
     * @phpstan-return array<string, array{array<string, mixed>, int|string, string|null, bool, string}>
     */
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

    /**
     * Provides test cases for numeric value conversion and comparison.
     *
     * This data provider covers scenarios involving numeric type coercion, strict and loose comparison operators,
     * closure-based compare values, string-to-float conversion, negative numbers, zero, and edge cases for
     * CompareValidator with TYPE_NUMBER. It ensures that numeric values are properly converted and compared according
     * to the specified operator, including strict equality, inequality, and relational operators.
     *
     * @return array test data for numeric value conversion and comparison.
     *
     * @phpstan-return array<string, array{array<string, mixed>, float|int|string, bool, string}>
     */
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

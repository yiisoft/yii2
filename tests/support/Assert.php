<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\support;

use yii\db\Constraint;
use yiiunit\framework\db\AnyCaseValue;
use yiiunit\framework\db\AnyValue;

use function array_keys;
use function is_array;
use function is_object;
use function json_encode;
use function ksort;
use function strtolower;

/**
 * Common PHPUnit assertions for test support code.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class Assert extends \PHPUnit\Framework\Assert
{
    /**
     * Asserts that schema metadata matches expected constraint metadata.
     *
     * @param mixed $expected Expected metadata, which can be an `object`, `array`, or `null`.
     * @param mixed $actual Actual metadata to compare against expected value.
     */
    public static function metadataEquals(mixed $expected, mixed $actual): void
    {
        match (true) {
            is_object($expected) => self::assertIsObject(
                $actual,
                'Failed asserting that actual value is an object.',
            ),
            is_array($expected) => self::assertIsArray(
                $actual,
                'Failed asserting that actual value is an array.',
            ),
            $expected === null => self::assertNull(
                $actual,
                "Failed asserting that actual value is 'null'.",
            ),
            default => null,
        };

        if (is_array($expected)) {
            $expected = self::sortConstraintList($expected, false);
            $actual = self::sortConstraintList($actual, false);
        }

        self::applyExpectedWildcards($expected, $actual);

        if (is_array($expected)) {
            $expected = self::sortConstraintList($expected, true);
            $actual = self::sortConstraintList($actual, true);
        }

        self::assertEquals(
            $expected,
            $actual,
            'Failed asserting that actual metadata matches expected metadata.',
        );
    }

    /**
     * Sorts constraint metadata by stable normalized keys.
     *
     * @param array $constraints Constraint metadata to sort.
     * @param bool $caseSensitive Whether generated keys preserve case.
     *
     * @return array Sorted constraint metadata.
     */
    private static function sortConstraintList(array $constraints, bool $caseSensitive): array
    {
        $sorted = [];

        foreach ($constraints as $constraint) {
            if (!$constraint instanceof Constraint) {
                $sorted[] = $constraint;

                continue;
            }

            $sorted[self::constraintSortKey($constraint, $caseSensitive)] = $constraint;
        }

        ksort($sorted, SORT_STRING);

        return $sorted;
    }

    /**
     * Builds a stable sort key for a constraint.
     *
     * @param Constraint $constraint Constraint metadata to key.
     * @param bool $caseSensitive Whether the generated key preserves case.
     */
    private static function constraintSortKey(Constraint $constraint, bool $caseSensitive): string
    {
        $values = (array) $constraint;

        unset($values['name'], $values['foreignSchemaName']);

        foreach ($values as $name => $value) {
            $values[$name] = match (true) {
                $value instanceof AnyCaseValue => $value->value,
                $value instanceof AnyValue => '[AnyValue]',
                default => $value,
            };
        }

        ksort($values, SORT_STRING);

        $key = json_encode($values, JSON_THROW_ON_ERROR);

        return $caseSensitive ? $key : strtolower($key);
    }

    /**
     * Applies wildcard values from expected metadata to actual metadata.
     *
     * @param mixed $expected Expected metadata containing wildcard markers.
     * @param mixed $actual Actual metadata to normalize before comparison.
     */
    private static function applyExpectedWildcards(mixed $expected, mixed $actual): void
    {
        if ($expected instanceof Constraint && $actual instanceof Constraint) {
            self::applyConstraintWildcards($expected, $actual);

            return;
        }

        if (!is_array($expected) || !is_array($actual)) {
            return;
        }

        foreach ($expected as $key => $constraint) {
            if ($constraint instanceof Constraint && ($actual[$key] ?? null) instanceof Constraint) {
                self::applyConstraintWildcards($constraint, $actual[$key]);
            }
        }
    }

    /**
     * Copies wildcard marker values from an expected constraint to an actual constraint.
     *
     * @param Constraint $expectedConstraint Expected constraint metadata.
     * @param Constraint $actualConstraint Actual constraint metadata.
     */
    private static function applyConstraintWildcards(Constraint $expectedConstraint, Constraint $actualConstraint): void
    {
        if ($expectedConstraint::class !== $actualConstraint::class) {
            return;
        }

        foreach (array_keys((array) $expectedConstraint) as $name) {
            if ($expectedConstraint->$name instanceof AnyValue) {
                $actualConstraint->$name = $expectedConstraint->$name;
            } elseif ($expectedConstraint->$name instanceof AnyCaseValue) {
                $actualConstraint->$name = new AnyCaseValue($actualConstraint->$name);
            }
        }
    }
}

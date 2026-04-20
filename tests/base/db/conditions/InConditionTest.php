<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\conditions;

use Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use yii\db\ArrayExpression;
use yii\db\conditions\InCondition;

/**
 * Unit tests for {@see InCondition}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('condition')]
final class InConditionTest extends TestCase
{
    public function testGetColumnPreservesArrayExpression(): void
    {
        $arrayExpression = new ArrayExpression([1, 2, 3], 'integer');
        $condition = new InCondition($arrayExpression, 'IN', [1, 2, 3]);

        $column = $condition->getColumn();

        self::assertInstanceOf(
            ArrayExpression::class,
            $column,
            'Unexpected column expression type.',
        );
        self::assertSame(
            [1, 2, 3],
            $column->getValue(),
            'Unexpected column expression value.',
        );
        self::assertSame(
            'integer',
            $column->getType(),
            'Unexpected column expression type name.',
        );
        self::assertSame(
            1,
            $column->getDimension(),
            'Unexpected column expression dimension.',
        );
    }

    public function testGetValuesPreservesArrayExpression(): void
    {
        $arrayExpression = new ArrayExpression([1, 2, 3], 'integer');
        $condition = new InCondition('id', 'IN', $arrayExpression);

        $values = $condition->getValues();

        self::assertInstanceOf(
            ArrayExpression::class,
            $values,
            'Unexpected values expression type.',
        );
        self::assertSame(
            [1, 2, 3],
            $values->getValue(),
            'Unexpected values expression value.',
        );
        self::assertSame(
            'integer',
            $values->getType(),
            'Unexpected values expression type name.',
        );
        self::assertSame(
            1,
            $values->getDimension(),
            'Unexpected values expression dimension.',
        );
    }

    public function testGetColumnConvertsGeneratorToArray(): void
    {
        $condition = new InCondition(self::generatorFrom(['id', 'name']), 'IN', [1, 2]);

        $column = $condition->getColumn();
        $columnAgain = $condition->getColumn();

        self::assertSame(
            ['id', 'name'],
            $column,
            'Column traversable should normalize to array.',
        );
        self::assertSame(
            ['id', 'name'],
            $columnAgain,
            'Column normalization should be stable across calls.',
        );
    }

    public function testGetValuesConvertsGeneratorToArray(): void
    {
        $condition = new InCondition('id', 'IN', self::generatorFrom([1, 2, 3]));

        $values = $condition->getValues();
        $valuesAgain = $condition->getValues();

        self::assertSame(
            [1, 2, 3],
            $values,
            'Values traversable should normalize to array.',
        );
        self::assertSame(
            [1, 2, 3],
            $valuesAgain,
            'Values normalization should be stable across calls.',
        );
    }

    private static function generatorFrom(array $items): Generator
    {
        yield from $items;
    }
}

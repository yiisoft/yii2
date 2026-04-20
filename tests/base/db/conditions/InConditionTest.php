<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\conditions;

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
}

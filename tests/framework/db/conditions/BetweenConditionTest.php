<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\BetweenCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class BetweenConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $condition = new BetweenCondition('age', 'BETWEEN', 18, 65);

        $this->assertSame('age', $condition->getColumn());
        $this->assertSame('BETWEEN', $condition->getOperator());
        $this->assertSame(18, $condition->getIntervalStart());
        $this->assertSame(65, $condition->getIntervalEnd());
    }

    public function testConstructorWithNotBetween(): void
    {
        $condition = new BetweenCondition('price', 'NOT BETWEEN', 100, 500);

        $this->assertSame('price', $condition->getColumn());
        $this->assertSame('NOT BETWEEN', $condition->getOperator());
        $this->assertSame(100, $condition->getIntervalStart());
        $this->assertSame(500, $condition->getIntervalEnd());
    }

    public function testConstructorWithStringValues(): void
    {
        $condition = new BetweenCondition('name', 'BETWEEN', 'A', 'M');

        $this->assertSame('A', $condition->getIntervalStart());
        $this->assertSame('M', $condition->getIntervalEnd());
    }

    public function testConstructorWithNullValues(): void
    {
        $condition = new BetweenCondition('col', 'BETWEEN', null, null);

        $this->assertNull($condition->getIntervalStart());
        $this->assertNull($condition->getIntervalEnd());
    }

    public function testConstructorWithExpression(): void
    {
        $start = new Expression('NOW()');
        $end = new Expression('NOW() + INTERVAL 1 DAY');
        $condition = new BetweenCondition('created_at', 'BETWEEN', $start, $end);

        $this->assertSame($start, $condition->getIntervalStart());
        $this->assertSame($end, $condition->getIntervalEnd());
    }

    public function testConstructorWithExpressionColumn(): void
    {
        $column = new Expression('YEAR(created_at)');
        $condition = new BetweenCondition($column, 'BETWEEN', 2020, 2025);

        $this->assertSame($column, $condition->getColumn());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = BetweenCondition::fromArrayDefinition('BETWEEN', ['age', 18, 65]);

        $this->assertInstanceOf(BetweenCondition::class, $condition);
        $this->assertSame('age', $condition->getColumn());
        $this->assertSame('BETWEEN', $condition->getOperator());
        $this->assertSame(18, $condition->getIntervalStart());
        $this->assertSame(65, $condition->getIntervalEnd());
    }

    public function testFromArrayDefinitionWithNotBetween(): void
    {
        $condition = BetweenCondition::fromArrayDefinition('NOT BETWEEN', ['price', 0, 100]);

        $this->assertSame('NOT BETWEEN', $condition->getOperator());
        $this->assertSame('price', $condition->getColumn());
        $this->assertSame(0, $condition->getIntervalStart());
        $this->assertSame(100, $condition->getIntervalEnd());
    }

    public function testFromArrayDefinitionThrowsOnMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'BETWEEN' requires three operands.");

        BetweenCondition::fromArrayDefinition('BETWEEN', ['age', 18]);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenCondition::fromArrayDefinition('BETWEEN', []);
    }

    public function testFromArrayDefinitionThrowsOnSingleOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenCondition::fromArrayDefinition('BETWEEN', ['age']);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new BetweenCondition('col', 'BETWEEN', 1, 10);

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testFromArrayDefinitionThrowsWhenFirstOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenCondition::fromArrayDefinition('BETWEEN', [null, 1, 10]);
    }

    public function testFromArrayDefinitionThrowsWhenSecondOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenCondition::fromArrayDefinition('BETWEEN', ['col', null, 10]);
    }

    public function testFromArrayDefinitionThrowsWhenThirdOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenCondition::fromArrayDefinition('BETWEEN', ['col', 1, null]);
    }

    public function testConstructorWithArrayColumn(): void
    {
        $condition = new BetweenCondition(['table', 'column'], 'BETWEEN', 1, 10);

        $this->assertSame(['table', 'column'], $condition->getColumn());
    }

    public function testFromArrayDefinitionPreservesTypes(): void
    {
        $condition = BetweenCondition::fromArrayDefinition('BETWEEN', ['col', 1.5, '100']);

        $this->assertSame(1.5, $condition->getIntervalStart());
        $this->assertSame('100', $condition->getIntervalEnd());
    }

    public function testConstructorWithZeroValues(): void
    {
        $condition = new BetweenCondition('col', 'BETWEEN', 0, 0);

        $this->assertSame(0, $condition->getIntervalStart());
        $this->assertSame(0, $condition->getIntervalEnd());
    }

    public function testConstructorWithEmptyStringColumn(): void
    {
        $condition = new BetweenCondition('', 'BETWEEN', 1, 10);

        $this->assertSame('', $condition->getColumn());
    }
}

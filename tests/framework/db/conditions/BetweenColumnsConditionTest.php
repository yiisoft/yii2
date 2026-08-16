<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\BetweenColumnsCondition;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class BetweenColumnsConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $condition = new BetweenColumnsCondition(42, 'BETWEEN', 'min_value', 'max_value');

        $this->assertSame(42, $condition->getValue());
        $this->assertSame('BETWEEN', $condition->getOperator());
        $this->assertSame('min_value', $condition->getIntervalStartColumn());
        $this->assertSame('max_value', $condition->getIntervalEndColumn());
    }

    public function testConstructorWithNotBetween(): void
    {
        $condition = new BetweenColumnsCondition(100, 'NOT BETWEEN', 'low', 'high');

        $this->assertSame('NOT BETWEEN', $condition->getOperator());
        $this->assertSame(100, $condition->getValue());
        $this->assertSame('low', $condition->getIntervalStartColumn());
        $this->assertSame('high', $condition->getIntervalEndColumn());
    }

    public function testConstructorWithStringValue(): void
    {
        $condition = new BetweenColumnsCondition('test', 'BETWEEN', 'col_a', 'col_b');

        $this->assertSame('test', $condition->getValue());
    }

    public function testConstructorWithNullValue(): void
    {
        $condition = new BetweenColumnsCondition(null, 'BETWEEN', 'col_a', 'col_b');

        $this->assertNull($condition->getValue());
    }

    public function testConstructorWithExpressionColumns(): void
    {
        $start = new Expression('MIN(price)');
        $end = new Expression('MAX(price)');
        $condition = new BetweenColumnsCondition(50, 'BETWEEN', $start, $end);

        $this->assertSame($start, $condition->getIntervalStartColumn());
        $this->assertSame($end, $condition->getIntervalEndColumn());
    }

    public function testConstructorWithQueryColumn(): void
    {
        $subQuery = (new Query())->select('time')->from('log')->orderBy('id ASC')->limit(1);
        $condition = new BetweenColumnsCondition(
            new Expression('NOW()'),
            'NOT BETWEEN',
            $subQuery,
            'update_time'
        );

        $this->assertInstanceOf(Expression::class, $condition->getValue());
        $this->assertSame('NOT BETWEEN', $condition->getOperator());
        $this->assertInstanceOf(Query::class, $condition->getIntervalStartColumn());
        $this->assertSame('update_time', $condition->getIntervalEndColumn());
    }

    public function testConstructorWithExpressionValue(): void
    {
        $value = new Expression('NOW()');
        $condition = new BetweenColumnsCondition($value, 'BETWEEN', 'start_col', 'end_col');

        $this->assertSame($value, $condition->getValue());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [42, 'min_value', 'max_value']);

        $this->assertInstanceOf(BetweenColumnsCondition::class, $condition);
        $this->assertSame(42, $condition->getValue());
        $this->assertSame('BETWEEN', $condition->getOperator());
        $this->assertSame('min_value', $condition->getIntervalStartColumn());
        $this->assertSame('max_value', $condition->getIntervalEndColumn());
    }

    public function testFromArrayDefinitionWithNotBetween(): void
    {
        $condition = BetweenColumnsCondition::fromArrayDefinition('NOT BETWEEN', ['val', 'start', 'end']);

        $this->assertSame('NOT BETWEEN', $condition->getOperator());
    }

    public function testFromArrayDefinitionThrowsOnMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'BETWEEN' requires three operands.");

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [42, 'min_value']);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', []);
    }

    public function testFromArrayDefinitionThrowsOnSingleOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [42]);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new BetweenColumnsCondition(1, 'BETWEEN', 'a', 'b');

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testFromArrayDefinitionThrowsWhenFirstOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [null, 'start', 'end']);
    }

    public function testFromArrayDefinitionThrowsWhenSecondOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [42, null, 'end']);
    }

    public function testFromArrayDefinitionThrowsWhenThirdOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [42, 'start', null]);
    }

    public function testConstructorWithFloatValue(): void
    {
        $condition = new BetweenColumnsCondition(3.14, 'BETWEEN', 'min_col', 'max_col');

        $this->assertSame(3.14, $condition->getValue());
    }

    public function testConstructorWithZeroValue(): void
    {
        $condition = new BetweenColumnsCondition(0, 'BETWEEN', 'a', 'b');

        $this->assertSame(0, $condition->getValue());
    }

    public function testFromArrayDefinitionPreservesTypes(): void
    {
        $condition = BetweenColumnsCondition::fromArrayDefinition('BETWEEN', [1.5, 'start', 'end']);

        $this->assertSame(1.5, $condition->getValue());
        $this->assertSame('start', $condition->getIntervalStartColumn());
        $this->assertSame('end', $condition->getIntervalEndColumn());
    }

    public function testConstructorWithArrayValue(): void
    {
        $condition = new BetweenColumnsCondition([1, 2], 'BETWEEN', 'a', 'b');

        $this->assertSame([1, 2], $condition->getValue());
    }
}

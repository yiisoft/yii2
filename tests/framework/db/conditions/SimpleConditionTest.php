<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\SimpleCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class SimpleConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $condition = new SimpleCondition('age', '>', 18);

        $this->assertSame('age', $condition->getColumn());
        $this->assertSame('>', $condition->getOperator());
        $this->assertSame(18, $condition->getValue());
    }

    public function testConstructorWithEquality(): void
    {
        $condition = new SimpleCondition('status', '=', 'active');

        $this->assertSame('status', $condition->getColumn());
        $this->assertSame('=', $condition->getOperator());
        $this->assertSame('active', $condition->getValue());
    }

    public function testConstructorWithNullValue(): void
    {
        $condition = new SimpleCondition('deleted_at', 'IS', null);

        $this->assertSame('deleted_at', $condition->getColumn());
        $this->assertSame('IS', $condition->getOperator());
        $this->assertNull($condition->getValue());
    }

    public function testConstructorWithExpression(): void
    {
        $expression = new Expression('NOW()');
        $condition = new SimpleCondition('created_at', '<', $expression);

        $this->assertSame($expression, $condition->getValue());
    }

    public function testConstructorWithExpressionColumn(): void
    {
        $column = new Expression('LOWER(name)');
        $condition = new SimpleCondition($column, '=', 'test');

        $this->assertSame($column, $condition->getColumn());
    }

    public function testConstructorWithLessThanOrEqual(): void
    {
        $condition = new SimpleCondition('price', '<=', 99.99);

        $this->assertSame('<=', $condition->getOperator());
        $this->assertSame(99.99, $condition->getValue());
    }

    public function testConstructorWithGreaterThanOrEqual(): void
    {
        $condition = new SimpleCondition('quantity', '>=', 0);

        $this->assertSame('>=', $condition->getOperator());
        $this->assertSame(0, $condition->getValue());
    }

    public function testConstructorWithNotEqual(): void
    {
        $condition = new SimpleCondition('type', '!=', 'deleted');

        $this->assertSame('!=', $condition->getOperator());
        $this->assertSame('deleted', $condition->getValue());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = SimpleCondition::fromArrayDefinition('>', ['age', 18]);

        $this->assertInstanceOf(SimpleCondition::class, $condition);
        $this->assertSame('age', $condition->getColumn());
        $this->assertSame('>', $condition->getOperator());
        $this->assertSame(18, $condition->getValue());
    }

    public function testFromArrayDefinitionWithEquality(): void
    {
        $condition = SimpleCondition::fromArrayDefinition('=', ['name', 'John']);

        $this->assertSame('name', $condition->getColumn());
        $this->assertSame('=', $condition->getOperator());
        $this->assertSame('John', $condition->getValue());
    }

    public function testFromArrayDefinitionThrowsOnTooFewOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator '>' requires two operands.");

        SimpleCondition::fromArrayDefinition('>', ['age']);
    }

    public function testFromArrayDefinitionThrowsOnTooManyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator '>' requires two operands.");

        SimpleCondition::fromArrayDefinition('>', ['age', 18, 'extra']);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SimpleCondition::fromArrayDefinition('=', []);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new SimpleCondition('col', '=', 1);

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testConstructorWithZeroValue(): void
    {
        $condition = new SimpleCondition('col', '=', 0);

        $this->assertSame(0, $condition->getValue());
    }

    public function testConstructorWithEmptyStringValue(): void
    {
        $condition = new SimpleCondition('col', '=', '');

        $this->assertSame('', $condition->getValue());
    }

    public function testConstructorWithFalseValue(): void
    {
        $condition = new SimpleCondition('col', '=', false);

        $this->assertFalse($condition->getValue());
    }

    public function testConstructorWithArrayColumn(): void
    {
        $condition = new SimpleCondition(['table', 'column'], '=', 1);

        $this->assertSame(['table', 'column'], $condition->getColumn());
    }

    public function testFromArrayDefinitionPreservesTypes(): void
    {
        $condition = SimpleCondition::fromArrayDefinition('=', ['col', 3.14]);

        $this->assertSame(3.14, $condition->getValue());
    }

    public function testFromArrayDefinitionWithNullValue(): void
    {
        $condition = SimpleCondition::fromArrayDefinition('IS', ['col', null]);

        $this->assertNull($condition->getValue());
    }
}

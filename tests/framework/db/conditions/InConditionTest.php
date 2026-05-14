<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\InCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class InConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $condition = new InCondition('id', 'IN', [1, 2, 3]);

        $this->assertSame('id', $condition->getColumn());
        $this->assertSame('IN', $condition->getOperator());
        $this->assertSame([1, 2, 3], $condition->getValues());
    }

    public function testConstructorWithNotIn(): void
    {
        $condition = new InCondition('status', 'NOT IN', ['deleted', 'banned']);

        $this->assertSame('NOT IN', $condition->getOperator());
        $this->assertSame(['deleted', 'banned'], $condition->getValues());
    }

    public function testConstructorWithEmptyValues(): void
    {
        $condition = new InCondition('id', 'IN', []);

        $this->assertSame([], $condition->getValues());
    }

    public function testConstructorWithSingleValue(): void
    {
        $condition = new InCondition('id', 'IN', [42]);

        $this->assertSame([42], $condition->getValues());
    }

    public function testConstructorWithStringColumn(): void
    {
        $condition = new InCondition('user_id', 'IN', [1, 2]);

        $this->assertSame('user_id', $condition->getColumn());
    }

    public function testConstructorWithCompositeColumn(): void
    {
        $columns = ['user_id', 'role_id'];
        $condition = new InCondition($columns, 'IN', [[1, 'admin'], [2, 'user']]);

        $this->assertSame($columns, $condition->getColumn());
        $this->assertSame([[1, 'admin'], [2, 'user']], $condition->getValues());
    }

    public function testConstructorWithExpressionValues(): void
    {
        $expression = new Expression('SELECT id FROM users');
        $condition = new InCondition('id', 'IN', $expression);

        $this->assertSame($expression, $condition->getValues());
    }

    public function testConstructorWithMixedValueTypes(): void
    {
        $values = [1, 'two', 3.0, null, true];
        $condition = new InCondition('col', 'IN', $values);

        $this->assertSame($values, $condition->getValues());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = InCondition::fromArrayDefinition('IN', ['id', [1, 2, 3]]);

        $this->assertInstanceOf(InCondition::class, $condition);
        $this->assertSame('id', $condition->getColumn());
        $this->assertSame('IN', $condition->getOperator());
        $this->assertSame([1, 2, 3], $condition->getValues());
    }

    public function testFromArrayDefinitionWithNotIn(): void
    {
        $condition = InCondition::fromArrayDefinition('NOT IN', ['status', ['a', 'b']]);

        $this->assertSame('NOT IN', $condition->getOperator());
    }

    public function testFromArrayDefinitionWithCompositeColumn(): void
    {
        $columns = ['user_id', 'role_id'];
        $condition = InCondition::fromArrayDefinition('IN', [$columns, [[1, 2], [3, 4]]]);

        $this->assertSame($columns, $condition->getColumn());
    }

    public function testFromArrayDefinitionThrowsOnMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'IN' requires two operands.");

        InCondition::fromArrayDefinition('IN', ['id']);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InCondition::fromArrayDefinition('IN', []);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new InCondition('col', 'IN', []);

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testConstructorWithNullValues(): void
    {
        $condition = new InCondition('col', 'IN', [null, null]);

        $this->assertSame([null, null], $condition->getValues());
    }

    public function testFromArrayDefinitionWithEmptyValues(): void
    {
        $condition = InCondition::fromArrayDefinition('IN', ['id', []]);

        $this->assertSame([], $condition->getValues());
    }

    public function testFromArrayDefinitionPreservesValueTypes(): void
    {
        $condition = InCondition::fromArrayDefinition('IN', ['col', [1, '2', 3.0]]);

        $this->assertSame([1, '2', 3.0], $condition->getValues());
    }

    public function testConstructorWithExpressionColumn(): void
    {
        $column = new Expression('LOWER(name)');
        $condition = new InCondition($column, 'IN', ['a', 'b']);

        $this->assertSame($column, $condition->getColumn());
    }

    public function testFromArrayDefinitionThrowsWhenFirstOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InCondition::fromArrayDefinition('IN', [null, [1, 2]]);
    }

    public function testFromArrayDefinitionThrowsWhenSecondOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InCondition::fromArrayDefinition('IN', ['id', null]);
    }
}

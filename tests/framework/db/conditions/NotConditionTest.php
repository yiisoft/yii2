<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\AndCondition;
use yii\db\conditions\HashCondition;
use yii\db\conditions\NotCondition;
use yii\db\conditions\SimpleCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class NotConditionTest extends TestCase
{
    public function testConstructorWithArray(): void
    {
        $inner = ['=', 'status', 'banned'];
        $condition = new NotCondition($inner);

        $this->assertSame($inner, $condition->getCondition());
    }

    public function testConstructorWithConditionObject(): void
    {
        $simple = new SimpleCondition('age', '<', 18);
        $condition = new NotCondition($simple);

        $this->assertSame($simple, $condition->getCondition());
    }

    public function testConstructorWithHashCondition(): void
    {
        $hash = new HashCondition(['status' => 'active']);
        $condition = new NotCondition($hash);

        $this->assertInstanceOf(HashCondition::class, $condition->getCondition());
    }

    public function testConstructorWithString(): void
    {
        $condition = new NotCondition('status = 1');

        $this->assertSame('status = 1', $condition->getCondition());
    }

    public function testConstructorWithExpression(): void
    {
        $expression = new Expression('deleted_at IS NOT NULL');
        $condition = new NotCondition($expression);

        $this->assertSame($expression, $condition->getCondition());
    }

    public function testConstructorWithNull(): void
    {
        $condition = new NotCondition(null);

        $this->assertNull($condition->getCondition());
    }

    public function testConstructorWithNestedNot(): void
    {
        $inner = new NotCondition(['=', 'a', 1]);
        $condition = new NotCondition($inner);

        $this->assertInstanceOf(NotCondition::class, $condition->getCondition());
    }

    public function testConstructorWithAndCondition(): void
    {
        $and = new AndCondition([['>', 'age', 18], ['=', 'active', 1]]);
        $condition = new NotCondition($and);

        $this->assertInstanceOf(AndCondition::class, $condition->getCondition());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = NotCondition::fromArrayDefinition('NOT', [['=', 'status', 'banned']]);

        $this->assertInstanceOf(NotCondition::class, $condition);
        $this->assertSame(['=', 'status', 'banned'], $condition->getCondition());
    }

    public function testFromArrayDefinitionWithConditionObject(): void
    {
        $simple = new SimpleCondition('col', '=', 1);
        $condition = NotCondition::fromArrayDefinition('NOT', [$simple]);

        $this->assertSame($simple, $condition->getCondition());
    }

    public function testFromArrayDefinitionThrowsOnTooManyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'NOT' requires exactly one operand.");

        NotCondition::fromArrayDefinition('NOT', [['=', 'a', 1], ['=', 'b', 2]]);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'NOT' requires exactly one operand.");

        NotCondition::fromArrayDefinition('NOT', []);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new NotCondition('a = 1');

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testFromArrayDefinitionWithString(): void
    {
        $condition = NotCondition::fromArrayDefinition('NOT', ['active = 1']);

        $this->assertSame('active = 1', $condition->getCondition());
    }

    public function testFromArrayDefinitionWithExpression(): void
    {
        $expr = new Expression('1 = 1');
        $condition = NotCondition::fromArrayDefinition('NOT', [$expr]);

        $this->assertSame($expr, $condition->getCondition());
    }

    public function testConstructorWithEmptyArray(): void
    {
        $condition = new NotCondition([]);

        $this->assertSame([], $condition->getCondition());
    }

    public function testConstructorWithBooleanCondition(): void
    {
        $condition = new NotCondition(false);

        $this->assertFalse($condition->getCondition());
    }
}

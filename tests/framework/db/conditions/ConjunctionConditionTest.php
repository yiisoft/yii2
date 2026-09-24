<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\db\conditions\AndCondition;
use yii\db\conditions\BetweenCondition;
use yii\db\conditions\HashCondition;
use yii\db\conditions\NotCondition;
use yii\db\conditions\OrCondition;
use yii\db\conditions\SimpleCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class ConjunctionConditionTest extends TestCase
{
    public function testAndConditionOperator(): void
    {
        $condition = new AndCondition([]);

        $this->assertSame('AND', $condition->getOperator());
    }

    public function testAndConditionWithEmptyExpressions(): void
    {
        $condition = new AndCondition([]);

        $this->assertSame([], $condition->getExpressions());
    }

    public function testAndConditionWithSingleExpression(): void
    {
        $expressions = [['>', 'age', 18]];
        $condition = new AndCondition($expressions);

        $this->assertSame($expressions, $condition->getExpressions());
    }

    public function testAndConditionWithMultipleExpressions(): void
    {
        $expressions = [
            ['>', 'age', 18],
            ['=', 'status', 'active'],
            ['IS NOT', 'email', null],
        ];
        $condition = new AndCondition($expressions);

        $this->assertCount(3, $condition->getExpressions());
        $this->assertSame($expressions, $condition->getExpressions());
    }

    public function testAndConditionWithConditionObjects(): void
    {
        $simple = new SimpleCondition('age', '>', 18);
        $hash = new HashCondition(['status' => 'active']);
        $condition = new AndCondition([$simple, $hash]);

        $expressions = $condition->getExpressions();
        $this->assertCount(2, $expressions);
        $this->assertSame($simple, $expressions[0]);
        $this->assertSame($hash, $expressions[1]);
    }

    public function testAndConditionWithNestedConjunction(): void
    {
        $inner = new OrCondition([['=', 'a', 1], ['=', 'b', 2]]);
        $condition = new AndCondition([$inner, ['=', 'c', 3]]);

        $this->assertCount(2, $condition->getExpressions());
        $this->assertInstanceOf(OrCondition::class, $condition->getExpressions()[0]);
    }

    public function testAndConditionFromArrayDefinition(): void
    {
        $operands = [['>', 'age', 18], ['=', 'status', 1]];
        $condition = AndCondition::fromArrayDefinition('AND', $operands);

        $this->assertInstanceOf(AndCondition::class, $condition);
        $this->assertSame($operands, $condition->getExpressions());
    }

    public function testAndConditionFromArrayDefinitionEmpty(): void
    {
        $condition = AndCondition::fromArrayDefinition('AND', []);

        $this->assertSame([], $condition->getExpressions());
    }

    public function testAndConditionImplementsConditionInterface(): void
    {
        $condition = new AndCondition([]);

        $this->assertInstanceOf('yii\db\conditions\ConjunctionCondition', $condition);
        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testOrConditionOperator(): void
    {
        $condition = new OrCondition([]);

        $this->assertSame('OR', $condition->getOperator());
    }

    public function testOrConditionWithEmptyExpressions(): void
    {
        $condition = new OrCondition([]);

        $this->assertSame([], $condition->getExpressions());
    }

    public function testOrConditionWithSingleExpression(): void
    {
        $expressions = [['=', 'type', 'admin']];
        $condition = new OrCondition($expressions);

        $this->assertSame($expressions, $condition->getExpressions());
    }

    public function testOrConditionWithMultipleExpressions(): void
    {
        $expressions = [
            ['=', 'role', 'admin'],
            ['=', 'role', 'moderator'],
        ];
        $condition = new OrCondition($expressions);

        $this->assertCount(2, $condition->getExpressions());
        $this->assertSame($expressions, $condition->getExpressions());
    }

    public function testOrConditionWithConditionObjects(): void
    {
        $between = new BetweenCondition('age', 'BETWEEN', 18, 65);
        $not = new NotCondition(['=', 'banned', 1]);
        $condition = new OrCondition([$between, $not]);

        $expressions = $condition->getExpressions();
        $this->assertCount(2, $expressions);
        $this->assertSame($between, $expressions[0]);
        $this->assertSame($not, $expressions[1]);
    }

    public function testOrConditionFromArrayDefinition(): void
    {
        $operands = [['=', 'a', 1], ['=', 'b', 2]];
        $condition = OrCondition::fromArrayDefinition('OR', $operands);

        $this->assertInstanceOf(OrCondition::class, $condition);
        $this->assertSame($operands, $condition->getExpressions());
    }

    public function testOrConditionFromArrayDefinitionEmpty(): void
    {
        $condition = OrCondition::fromArrayDefinition('OR', []);

        $this->assertSame([], $condition->getExpressions());
    }

    public function testOrConditionImplementsConditionInterface(): void
    {
        $condition = new OrCondition([]);

        $this->assertInstanceOf('yii\db\conditions\ConjunctionCondition', $condition);
        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
    }

    public function testAndAndOrReturnDifferentOperators(): void
    {
        $and = new AndCondition([]);
        $or = new OrCondition([]);

        $this->assertSame('AND', $and->getOperator());
        $this->assertSame('OR', $or->getOperator());
        $this->assertNotSame($and->getOperator(), $or->getOperator());
    }

    public function testExpressionsWithExpressionObjects(): void
    {
        $expr = new Expression('1 = 1');
        $andCondition = new AndCondition([$expr]);
        $orCondition = new OrCondition([$expr]);

        $this->assertSame($expr, $andCondition->getExpressions()[0]);
        $this->assertSame($expr, $orCondition->getExpressions()[0]);
    }

    public function testExpressionsWithStringValues(): void
    {
        $condition = new AndCondition(['status = 1', 'type = 2']);

        $this->assertSame(['status = 1', 'type = 2'], $condition->getExpressions());
    }

    public function testFromArrayDefinitionPassesOperandsAsExpressions(): void
    {
        $operands = ['a = 1', 'b = 2', 'c = 3'];

        $and = AndCondition::fromArrayDefinition('AND', $operands);
        $or = OrCondition::fromArrayDefinition('OR', $operands);

        $this->assertSame($operands, $and->getExpressions());
        $this->assertSame($operands, $or->getExpressions());
    }

    public function testNestedAndInsideOr(): void
    {
        $inner = new AndCondition([['=', 'a', 1], ['=', 'b', 2]]);
        $outer = new OrCondition([$inner, ['=', 'c', 3]]);

        $this->assertSame('OR', $outer->getOperator());
        $this->assertCount(2, $outer->getExpressions());
        $this->assertInstanceOf(AndCondition::class, $outer->getExpressions()[0]);
    }

    public function testNestedOrInsideAnd(): void
    {
        $inner = new OrCondition([['=', 'x', 1], ['=', 'y', 2]]);
        $outer = new AndCondition([$inner, ['=', 'z', 3]]);

        $this->assertSame('AND', $outer->getOperator());
        $this->assertCount(2, $outer->getExpressions());
        $this->assertInstanceOf(OrCondition::class, $outer->getExpressions()[0]);
    }
}

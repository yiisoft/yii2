<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\LikeCondition;
use yii\db\Expression;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class LikeConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $condition = new LikeCondition('name', 'LIKE', 'John');

        $this->assertSame('name', $condition->getColumn());
        $this->assertSame('LIKE', $condition->getOperator());
        $this->assertSame('John', $condition->getValue());
    }

    public function testConstructorWithNotLike(): void
    {
        $condition = new LikeCondition('name', 'NOT LIKE', 'test');

        $this->assertSame('NOT LIKE', $condition->getOperator());
    }

    public function testConstructorWithOrLike(): void
    {
        $condition = new LikeCondition('name', 'OR LIKE', ['foo', 'bar']);

        $this->assertSame('OR LIKE', $condition->getOperator());
        $this->assertSame(['foo', 'bar'], $condition->getValue());
    }

    public function testConstructorWithOrNotLike(): void
    {
        $condition = new LikeCondition('name', 'OR NOT LIKE', ['spam', 'junk']);

        $this->assertSame('OR NOT LIKE', $condition->getOperator());
    }

    public function testConstructorWithArrayValue(): void
    {
        $values = ['foo', 'bar', 'baz'];
        $condition = new LikeCondition('title', 'LIKE', $values);

        $this->assertSame($values, $condition->getValue());
    }

    public function testDefaultEscapingReplacements(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');

        $this->assertNull($condition->getEscapingReplacements());
    }

    public function testSetEscapingReplacementsWithArray(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');
        $replacements = ['%' => '\%', '_' => '\_'];
        $condition->setEscapingReplacements($replacements);

        $this->assertSame($replacements, $condition->getEscapingReplacements());
    }

    public function testSetEscapingReplacementsWithNull(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');
        $condition->setEscapingReplacements(null);

        $this->assertNull($condition->getEscapingReplacements());
    }

    public function testSetEscapingReplacementsWithFalse(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');
        $condition->setEscapingReplacements(false);

        $this->assertFalse($condition->getEscapingReplacements());
    }

    public function testSetEscapingReplacementsWithEmptyArray(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');
        $condition->setEscapingReplacements([]);

        $this->assertSame([], $condition->getEscapingReplacements());
    }

    public function testFromArrayDefinition(): void
    {
        $condition = LikeCondition::fromArrayDefinition('LIKE', ['name', 'John']);

        $this->assertInstanceOf(LikeCondition::class, $condition);
        $this->assertSame('name', $condition->getColumn());
        $this->assertSame('LIKE', $condition->getOperator());
        $this->assertSame('John', $condition->getValue());
        $this->assertNull($condition->getEscapingReplacements());
    }

    public function testFromArrayDefinitionWithEscapingReplacements(): void
    {
        $replacements = ['%' => '\%', '_' => '\_'];
        $condition = LikeCondition::fromArrayDefinition('LIKE', ['name', 'John', $replacements]);

        $this->assertSame($replacements, $condition->getEscapingReplacements());
    }

    public function testFromArrayDefinitionWithFalseEscaping(): void
    {
        $condition = LikeCondition::fromArrayDefinition('LIKE', ['name', '%John%', false]);

        $this->assertFalse($condition->getEscapingReplacements());
    }

    public function testFromArrayDefinitionThrowsOnMissingOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Operator 'LIKE' requires two operands.");

        LikeCondition::fromArrayDefinition('LIKE', ['name']);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LikeCondition::fromArrayDefinition('LIKE', []);
    }

    public function testExtendsSimpleCondition(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');

        $this->assertInstanceOf('yii\db\conditions\SimpleCondition', $condition);
        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
    }

    public function testConstructorWithExpressionColumn(): void
    {
        $column = new Expression('LOWER(name)');
        $condition = new LikeCondition($column, 'LIKE', 'test');

        $this->assertSame($column, $condition->getColumn());
    }

    public function testConstructorWithEmptyStringValue(): void
    {
        $condition = new LikeCondition('name', 'LIKE', '');

        $this->assertSame('', $condition->getValue());
    }

    public function testConstructorWithEmptyArrayValue(): void
    {
        $condition = new LikeCondition('name', 'LIKE', []);

        $this->assertSame([], $condition->getValue());
    }

    public function testFromArrayDefinitionWithNotLike(): void
    {
        $condition = LikeCondition::fromArrayDefinition('NOT LIKE', ['title', 'spam']);

        $this->assertSame('NOT LIKE', $condition->getOperator());
        $this->assertSame('title', $condition->getColumn());
        $this->assertSame('spam', $condition->getValue());
    }

    public function testFromArrayDefinitionWithArrayValue(): void
    {
        $condition = LikeCondition::fromArrayDefinition('OR LIKE', ['name', ['foo', 'bar']]);

        $this->assertSame(['foo', 'bar'], $condition->getValue());
    }

    public function testEscapingReplacementsOverwrite(): void
    {
        $condition = new LikeCondition('col', 'LIKE', 'val');

        $condition->setEscapingReplacements(['%' => '\%']);
        $this->assertSame(['%' => '\%'], $condition->getEscapingReplacements());

        $condition->setEscapingReplacements(false);
        $this->assertFalse($condition->getEscapingReplacements());

        $condition->setEscapingReplacements(null);
        $this->assertNull($condition->getEscapingReplacements());
    }

    public function testFromArrayDefinitionThrowsWhenFirstOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LikeCondition::fromArrayDefinition('LIKE', [null, 'value']);
    }

    public function testFromArrayDefinitionThrowsWhenSecondOperandIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);

        LikeCondition::fromArrayDefinition('LIKE', ['col', null]);
    }
}

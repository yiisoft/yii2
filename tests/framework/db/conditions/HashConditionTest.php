<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\db\conditions\HashCondition;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class HashConditionTest extends TestCase
{
    public function testConstructorWithArray(): void
    {
        $hash = ['status' => 'active', 'type' => 1];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }

    public function testConstructorWithNull(): void
    {
        $condition = new HashCondition(null);

        $this->assertNull($condition->getHash());
    }

    public function testConstructorWithEmptyArray(): void
    {
        $condition = new HashCondition([]);

        $this->assertSame([], $condition->getHash());
    }

    public function testConstructorWithSinglePair(): void
    {
        $condition = new HashCondition(['id' => 1]);

        $this->assertSame(['id' => 1], $condition->getHash());
    }

    public function testConstructorWithMultiplePairs(): void
    {
        $hash = [
            'name' => 'John',
            'age' => 30,
            'active' => true,
        ];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }

    public function testConstructorWithNullValues(): void
    {
        $hash = ['deleted_at' => null, 'status' => 'active'];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }

    public function testConstructorWithArrayValues(): void
    {
        $hash = ['id' => [1, 2, 3]];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }

    public function testFromArrayDefinition(): void
    {
        $operands = ['status' => 'active', 'type' => 1];
        $condition = HashCondition::fromArrayDefinition('AND', $operands);

        $this->assertInstanceOf(HashCondition::class, $condition);
        $this->assertSame($operands, $condition->getHash());
    }

    public function testFromArrayDefinitionWithEmptyOperands(): void
    {
        $condition = HashCondition::fromArrayDefinition('AND', []);

        $this->assertSame([], $condition->getHash());
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new HashCondition([]);

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testConstructorWithIntegerKeys(): void
    {
        $hash = [0 => 'a', 1 => 'b'];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }

    public function testConstructorWithMixedValueTypes(): void
    {
        $hash = [
            'string_val' => 'text',
            'int_val' => 42,
            'float_val' => 3.14,
            'bool_val' => true,
            'null_val' => null,
            'array_val' => [1, 2],
        ];
        $condition = new HashCondition($hash);

        $this->assertSame($hash, $condition->getHash());
    }
}

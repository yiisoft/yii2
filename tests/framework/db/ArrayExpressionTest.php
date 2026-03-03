<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db;

use yii\base\InvalidConfigException;
use yii\db\ArrayExpression;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * @group db
 * @covers \yii\db\ArrayExpression
 */
class ArrayExpressionTest extends TestCase
{
    public function testConstructorWithArray()
    {
        $expression = new ArrayExpression([1, 2, 3]);

        $this->assertSame([1, 2, 3], $expression->getValue());
        $this->assertNull($expression->getType());
        $this->assertSame(1, $expression->getDimension());
    }

    public function testConstructorWithType()
    {
        $expression = new ArrayExpression([1, 2], 'integer');

        $this->assertSame('integer', $expression->getType());
    }

    public function testConstructorWithDimension()
    {
        $expression = new ArrayExpression([[1, 2], [3, 4]], 'integer', 2);

        $this->assertSame(2, $expression->getDimension());
    }

    public function testConstructorUnwrapsNestedArrayExpression()
    {
        $inner = new ArrayExpression([1, 2, 3], 'integer');
        $outer = new ArrayExpression($inner, 'text', 2);

        $this->assertSame([1, 2, 3], $outer->getValue());
        $this->assertSame('text', $outer->getType());
        $this->assertSame(2, $outer->getDimension());
    }

    public function testConstructorWithQueryInterface()
    {
        $query = new Query();
        $expression = new ArrayExpression($query);

        $this->assertSame($query, $expression->getValue());
    }

    public function testConstructorWithScalarValue()
    {
        $expression = new ArrayExpression('scalar');

        $this->assertSame('scalar', $expression->getValue());
    }

    public function testConstructorWithNull()
    {
        $expression = new ArrayExpression(null);

        $this->assertNull($expression->getValue());
    }

    public function testOffsetExists()
    {
        $expression = new ArrayExpression(['a' => 1, 'b' => 2]);

        $this->assertTrue(isset($expression['a']));
        $this->assertTrue(isset($expression['b']));
        $this->assertFalse(isset($expression['c']));
    }

    public function testOffsetExistsNumeric()
    {
        $expression = new ArrayExpression([10, 20, 30]);

        $this->assertTrue(isset($expression[0]));
        $this->assertTrue(isset($expression[2]));
        $this->assertFalse(isset($expression[3]));
    }

    public function testOffsetGet()
    {
        $expression = new ArrayExpression(['x' => 'foo', 'y' => 'bar']);

        $this->assertSame('foo', $expression['x']);
        $this->assertSame('bar', $expression['y']);
    }

    public function testOffsetGetNumeric()
    {
        $expression = new ArrayExpression([10, 20, 30]);

        $this->assertSame(10, $expression[0]);
        $this->assertSame(30, $expression[2]);
    }

    public function testOffsetSet()
    {
        $expression = new ArrayExpression([1, 2, 3]);

        $expression[1] = 99;
        $this->assertSame(99, $expression[1]);
    }

    public function testOffsetSetWithStringKey()
    {
        $expression = new ArrayExpression(['a' => 1]);

        $expression['b'] = 2;
        $this->assertSame(2, $expression['b']);
    }

    public function testOffsetSetWithNullKey()
    {
        $expression = new ArrayExpression(['a' => 1]);

        $expression[] = 2;
        $this->assertSame(2, $expression->count());
        $this->assertSame(2, $expression['']);
    }

    public function testOffsetUnset()
    {
        $expression = new ArrayExpression(['a' => 1, 'b' => 2, 'c' => 3]);

        unset($expression['b']);
        $this->assertFalse(isset($expression['b']));
        $this->assertSame(2, $expression->count());
    }

    public function testCount()
    {
        $this->assertSame(0, (new ArrayExpression([]))->count());
        $this->assertSame(3, (new ArrayExpression([1, 2, 3]))->count());
        $this->assertSame(1, (new ArrayExpression(['only']))->count());
    }

    public function testCountable()
    {
        $expression = new ArrayExpression([1, 2, 3, 4]);

        $this->assertCount(4, $expression);
    }

    public function testGetIteratorWithArray()
    {
        $expression = new ArrayExpression([10, 20, 30]);

        $result = [];
        foreach ($expression as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame([10, 20, 30], $result);
    }

    public function testGetIteratorReturnsArrayIterator()
    {
        $expression = new ArrayExpression([1, 2]);

        $this->assertInstanceOf(\ArrayIterator::class, $expression->getIterator());
    }

    public function testGetIteratorWithNullValue()
    {
        $expression = new ArrayExpression(null);

        $result = [];
        foreach ($expression as $value) {
            $result[] = $value;
        }

        $this->assertSame([], $result);
    }

    public function testGetIteratorThrowsOnQueryInterface()
    {
        $expression = new ArrayExpression(new Query());

        $this->expectException(InvalidConfigException::class);
        $expression->getIterator();
    }

    public function testGetIteratorWithAssociativeArray()
    {
        $expression = new ArrayExpression(['name' => 'John', 'age' => 30]);

        $result = [];
        foreach ($expression as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame(['name' => 'John', 'age' => 30], $result);
    }

    public function testGetIteratorWithEmptyArray()
    {
        $expression = new ArrayExpression([]);
        $iterator = $expression->getIterator();

        $this->assertInstanceOf(\ArrayIterator::class, $iterator);
        $this->assertSame(0, $iterator->count());
    }

    public function testImplementsExpressionInterface()
    {
        $expression = new ArrayExpression([]);

        $this->assertInstanceOf(\yii\db\ExpressionInterface::class, $expression);
    }

    public function testOffsetSetOverwritesExistingValue()
    {
        $expression = new ArrayExpression(['a' => 'old']);

        $expression['a'] = 'new';
        $this->assertSame('new', $expression['a']);
        $this->assertSame(1, $expression->count());
    }

    public function testMutationsReflectInCount()
    {
        $expression = new ArrayExpression([1, 2]);

        $this->assertSame(2, $expression->count());
        $expression[] = 3;
        $this->assertSame(3, $expression->count());
        unset($expression[0]);
        $this->assertSame(2, $expression->count());
    }

    public function testMutationsReflectInIteration()
    {
        $expression = new ArrayExpression([1, 2, 3]);

        $expression[1] = 99;
        unset($expression[2]);

        $result = [];
        foreach ($expression as $value) {
            $result[] = $value;
        }

        $this->assertSame([1, 99], $result);
    }

    public function testNestedArrayExpressionUnwrapDoesNotAffectOriginal()
    {
        $inner = new ArrayExpression([1, 2, 3]);
        $outer = new ArrayExpression($inner);

        $outer[] = 4;
        $this->assertSame(3, $inner->count());
        $this->assertSame(4, $outer->count());
    }
}

<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db;

use yii\base\InvalidConfigException;
use yii\db\ExpressionInterface;
use yii\db\JsonExpression;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * @group db
 */
class JsonExpressionTest extends TestCase
{
    public function testConstructorWithArray(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $expression = new JsonExpression($data);

        self::assertSame($data, $expression->getValue());
        self::assertNull($expression->getType());
    }

    public function testConstructorWithType(): void
    {
        $expression = new JsonExpression([1, 2, 3], JsonExpression::TYPE_JSON);

        self::assertSame(JsonExpression::TYPE_JSON, $expression->getType());
    }

    public function testConstructorWithJsonbType(): void
    {
        $expression = new JsonExpression(['key' => 'val'], JsonExpression::TYPE_JSONB);

        self::assertSame(JsonExpression::TYPE_JSONB, $expression->getType());
    }

    public function testUnwrapsNestedJsonExpression(): void
    {
        $inner = new JsonExpression(['nested' => true]);
        $outer = new JsonExpression($inner);

        self::assertSame(['nested' => true], $outer->getValue());
    }

    public function testUnwrapsNestedJsonExpressionPreservesOuterType(): void
    {
        $inner = new JsonExpression(['data' => 1], JsonExpression::TYPE_JSON);
        $outer = new JsonExpression($inner, JsonExpression::TYPE_JSONB);

        self::assertSame(['data' => 1], $outer->getValue());
        self::assertSame(JsonExpression::TYPE_JSONB, $outer->getType());
    }

    public function testUnwrapsOnlyOneLevel(): void
    {
        $innermost = new JsonExpression('deep');
        $middle = new JsonExpression($innermost);
        $outer = new JsonExpression($middle);

        self::assertSame('deep', $outer->getValue());
    }

    public function testImplementsExpressionInterface(): void
    {
        $expression = new JsonExpression([]);

        self::assertInstanceOf(ExpressionInterface::class, $expression);
    }

    public function testImplementsJsonSerializable(): void
    {
        $expression = new JsonExpression([]);

        self::assertInstanceOf(\JsonSerializable::class, $expression);
    }

    public function testJsonSerializeReturnsValue(): void
    {
        $data = ['x' => 10, 'y' => 20];
        $expression = new JsonExpression($data);

        self::assertSame($data, $expression->jsonSerialize());
    }

    public function testJsonSerializeWithScalar(): void
    {
        $expression = new JsonExpression('string value');

        self::assertSame('string value', $expression->jsonSerialize());
    }

    public function testJsonSerializeWithNull(): void
    {
        $expression = new JsonExpression(null);

        self::assertNull($expression->jsonSerialize());
    }

    public function testJsonSerializeThrowsOnQueryInterface(): void
    {
        $query = new Query();
        $expression = new JsonExpression($query);

        $this->expectException(InvalidConfigException::class);
        $expression->jsonSerialize();
    }

    public function testJsonEncodeUsesJsonSerialize(): void
    {
        $data = ['name' => 'test', 'count' => 42];
        $expression = new JsonExpression($data);

        self::assertSame('{"name":"test","count":42}', json_encode($expression));
    }

    /**
     * @dataProvider valueTypesProvider
     */
    public function testAcceptsVariousValueTypes($value): void
    {
        $expression = new JsonExpression($value);

        self::assertSame($value, $expression->getValue());
    }

    public static function valueTypesProvider(): array
    {
        return [
            'array' => [['a', 'b', 'c']],
            'associative array' => [['key' => 'value']],
            'nested array' => [['a' => ['b' => ['c' => 1]]]],
            'string' => ['simple string'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'null' => [null],
            'empty array' => [[]],
        ];
    }
}

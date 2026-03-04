<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\db\Expression;
use yii\db\ExpressionInterface;
use yiiunit\TestCase;

/**
 * @group db
 */
class ExpressionTest extends TestCase
{
    public function testConstructor(): void
    {
        $expression = new Expression('NOW()');

        self::assertSame('NOW()', $expression->expression);
        self::assertSame([], $expression->params);
    }

    public function testConstructorWithParams(): void
    {
        $params = [':name' => 'John', ':age' => 25];
        $expression = new Expression('name = :name AND age = :age', $params);

        self::assertSame('name = :name AND age = :age', $expression->expression);
        self::assertSame($params, $expression->params);
    }

    public function testConstructorWithConfig(): void
    {
        $expression = new Expression('DEFAULT', [], ['expression' => 'overridden']);

        self::assertSame('overridden', $expression->expression);
    }

    public function testToString(): void
    {
        $expression = new Expression('COUNT(*)');

        self::assertSame('COUNT(*)', (string) $expression);
        self::assertSame('COUNT(*)', $expression->__toString());
    }

    public function testToStringReturnsExpression(): void
    {
        $expression = new Expression('COALESCE(a, b, 0)');

        self::assertSame($expression->expression, (string) $expression);
    }

    public function testImplementsExpressionInterface(): void
    {
        $expression = new Expression('1');

        self::assertInstanceOf(ExpressionInterface::class, $expression);
    }

    public function testEmptyExpression(): void
    {
        $expression = new Expression('');

        self::assertSame('', $expression->expression);
        self::assertSame('', (string) $expression);
    }

    public function testExpressionIsPubliclyMutable(): void
    {
        $expression = new Expression('OLD');
        $expression->expression = 'NEW';
        $expression->params = [':id' => 1];

        self::assertSame('NEW', (string) $expression);
        self::assertSame([':id' => 1], $expression->params);
    }

    /**
     * @dataProvider expressionsProvider
     */
    public function testVariousExpressions(string $sql, array $params): void
    {
        $expression = new Expression($sql, $params);

        self::assertSame($sql, (string) $expression);
        self::assertSame($params, $expression->params);
    }

    public static function expressionsProvider(): array
    {
        return [
            'simple function' => ['NOW()', []],
            'aggregate' => ['COUNT(*)', []],
            'with placeholder' => ['status = :status', [':status' => 1]],
            'multiple placeholders' => [
                'age BETWEEN :min AND :max',
                [':min' => 18, ':max' => 65],
            ],
            'subquery-like' => ['(SELECT MAX(id) FROM users)', []],
            'raw literal' => ['1', []],
            'complex expression' => [
                'CASE WHEN status = :active THEN 1 ELSE 0 END',
                [':active' => 'active'],
            ],
        ];
    }
}

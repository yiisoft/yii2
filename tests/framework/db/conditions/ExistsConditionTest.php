<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db\conditions;

use yii\base\InvalidArgumentException;
use yii\db\conditions\ExistsCondition;
use yii\db\Query;
use yiiunit\TestCase;

/**
 * @group db
 * @group conditions
 */
class ExistsConditionTest extends TestCase
{
    public function testConstructor(): void
    {
        $query = new Query();
        $condition = new ExistsCondition('EXISTS', $query);

        $this->assertSame('EXISTS', $condition->getOperator());
        $this->assertSame($query, $condition->getQuery());
    }

    public function testConstructorWithNotExists(): void
    {
        $query = new Query();
        $condition = new ExistsCondition('NOT EXISTS', $query);

        $this->assertSame('NOT EXISTS', $condition->getOperator());
        $this->assertSame($query, $condition->getQuery());
    }

    public function testConstructorWithConfiguredQuery(): void
    {
        $query = (new Query())->select('id')->from('users')->where(['active' => 1]);
        $condition = new ExistsCondition('EXISTS', $query);

        $this->assertSame($query, $condition->getQuery());
    }

    public function testConstructorWithSubselect(): void
    {
        $query = (new Query())
            ->select('1')
            ->from('orders')
            ->where('orders.user_id = users.id');
        $condition = new ExistsCondition('EXISTS', $query);

        $this->assertInstanceOf(Query::class, $condition->getQuery());
    }

    public function testFromArrayDefinition(): void
    {
        $query = new Query();
        $condition = ExistsCondition::fromArrayDefinition('EXISTS', [$query]);

        $this->assertInstanceOf(ExistsCondition::class, $condition);
        $this->assertSame('EXISTS', $condition->getOperator());
        $this->assertSame($query, $condition->getQuery());
    }

    public function testFromArrayDefinitionWithNotExists(): void
    {
        $query = (new Query())->from('users');
        $condition = ExistsCondition::fromArrayDefinition('NOT EXISTS', [$query]);

        $this->assertSame('NOT EXISTS', $condition->getOperator());
        $this->assertSame($query, $condition->getQuery());
    }

    public function testFromArrayDefinitionThrowsOnNonQueryOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subquery for EXISTS operator must be a Query object.');

        ExistsCondition::fromArrayDefinition('EXISTS', ['not a query']);
    }

    public function testFromArrayDefinitionThrowsOnEmptyOperands(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subquery for EXISTS operator must be a Query object.');

        ExistsCondition::fromArrayDefinition('EXISTS', []);
    }

    public function testFromArrayDefinitionThrowsOnNullOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ExistsCondition::fromArrayDefinition('EXISTS', [null]);
    }

    public function testFromArrayDefinitionThrowsOnIntOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ExistsCondition::fromArrayDefinition('EXISTS', [42]);
    }

    public function testFromArrayDefinitionThrowsOnArrayOperand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ExistsCondition::fromArrayDefinition('EXISTS', [['SELECT 1']]);
    }

    public function testImplementsConditionInterface(): void
    {
        $condition = new ExistsCondition('EXISTS', new Query());

        $this->assertInstanceOf('yii\db\conditions\ConditionInterface', $condition);
        $this->assertInstanceOf('yii\db\ExpressionInterface', $condition);
    }

    public function testQueryObjectIsNotCloned(): void
    {
        $query = new Query();
        $condition = new ExistsCondition('EXISTS', $query);

        $this->assertSame($query, $condition->getQuery());

        $query->select('id');
        $this->assertSame($query, $condition->getQuery());
    }

    public function testFromArrayDefinitionWithComplexQuery(): void
    {
        $query = (new Query())
            ->select('1')
            ->from('orders o')
            ->where('o.user_id = u.id')
            ->andWhere(['>', 'o.total', 100])
            ->limit(1);
        $condition = ExistsCondition::fromArrayDefinition('EXISTS', [$query]);

        $this->assertSame($query, $condition->getQuery());
    }
}

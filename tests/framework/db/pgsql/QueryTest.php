<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\base\db\BaseQuery;

/**
 * Unit test for {@see \yii\db\Query} with PostgreSQL driver.
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('query')]
class QueryTest extends BaseQuery
{
    protected $driverName = 'pgsql';

    public function testLimitOffsetExecution(): void
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->select(['id', 'name'])
            ->from('customer')
            ->orderBy(['id' => SORT_ASC])
            ->limit(1)
            ->offset(1)
            ->all($db);

        self::assertCount(
            1,
            $rows,
            "LIMIT '1' OFFSET '1' should return exactly one row.",
        );
        self::assertSame(
            2,
            $rows[0]['id'],
            "OFFSET '1' should skip the first row and start at 'id=2'.",
        );
        self::assertSame(
            'user2',
            $rows[0]['name'],
            "Row at OFFSET '1' should correspond to 'user2'.",
        );
    }

    public function testOffsetExecution(): void
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->select(['id'])
            ->from('customer')
            ->orderBy(['id' => SORT_ASC])
            ->offset(1)
            ->column($db);

        self::assertSame(
            [2, 3],
            $rows,
            "OFFSET '1' without LIMIT should return remaining rows starting at 'id=2'.",
        );
    }

    public function testLimitExecution(): void
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->select(['id'])
            ->from('customer')
            ->orderBy(['id' => SORT_ASC])
            ->limit(2)
            ->column($db);

        self::assertSame(
            [1, 2],
            $rows,
            "LIMIT '2' without OFFSET should return the first two rows.",
        );
    }

    public function testLimitOffsetWithExpression(): void
    {
        $db = $this->getConnection();

        $rows = (new Query())
            ->select(['id'])
            ->from('customer')
            ->orderBy(['id' => SORT_ASC])
            ->limit(new Expression('1 + 1'))
            ->offset(new Expression('1'))
            ->column($db);

        self::assertSame(
            [2, 3],
            $rows,
            "Expression LIMIT '1 + 1' OFFSET '1' should return rows with 'id=2' and 'id=3'.",
        );
    }

    public function testBooleanValues(): void
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert(
            'bool_values',
            ['bool_col'],
            [
                [true],
                [false],
            ]
        )->execute();

        $this->assertEquals(1, (new Query())->from('bool_values')->where('bool_col = TRUE')->count('*', $db));
        $this->assertEquals(1, (new Query())->from('bool_values')->where('bool_col = FALSE')->count('*', $db));
        $this->assertEquals(2, (new Query())->from('bool_values')->where('bool_col IN (TRUE, FALSE)')->count('*', $db));

        $this->assertEquals(1, (new Query())->from('bool_values')->where(['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, (new Query())->from('bool_values')->where(['bool_col' => false])->count('*', $db));
        $this->assertEquals(2, (new Query())->from('bool_values')->where(['bool_col' => [true, false]])->count('*', $db));

        $this->assertEquals(1, (new Query())->from('bool_values')->where('bool_col = :bool_col', ['bool_col' => true])->count('*', $db));
        $this->assertEquals(1, (new Query())->from('bool_values')->where('bool_col = :bool_col', ['bool_col' => false])->count('*', $db));
    }
}

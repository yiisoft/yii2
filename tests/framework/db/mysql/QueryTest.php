<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\base\db\BaseQuery;

/**
 * Unit test for {@see \yii\db\Query} with MySQL driver.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('query')]
class QueryTest extends BaseQuery
{
    protected $driverName = 'mysql';

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

    /**
     * Tests MySQL specific syntax for index hints.
     */
    public function testQueryIndexHint(): void
    {
        $db = $this->getConnection();

        $query = (new Query())->from([new Expression('{{%customer}} USE INDEX (primary)')]);
        $row = $query->one($db);
        $this->assertArrayHasKey('id', $row);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('email', $row);
    }

    public function testLimitOffsetWithExpression(): void
    {
        $query = (new Query())
            ->from('customer')
            ->select('id')
            ->orderBy('id');

        // In MySQL limit and offset arguments must both be nonnegative integer constant
        $query
            ->limit(new Expression('2'))
            ->offset(new Expression('1'));

        $result = $query->column($this->getConnection());

        self::assertCount(
            2,
            $result,
            "Expression LIMIT '2' OFFSET '1' should return exactly two rows.",
        );
        self::assertSame(
            [2, 3],
            $result,
            "Expression LIMIT '2' OFFSET '1' should return rows with 'id=2' and 'id=3'.",
        );
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Query;
use yiiunit\base\db\BaseQuery;

/**
 * Unit test for {@see \yii\db\Query} with MSSQL driver.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('query')]
class QueryTest extends BaseQuery
{
    protected $driverName = 'sqlsrv';

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
            '2',
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
            ['2', '3'],
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
            ['1', '2'],
            $rows,
            "LIMIT '2' without OFFSET should return the first two rows.",
        );
    }

    public function testUnion(): void
    {
        $connection = $this->getConnection();

        // MSSQL supports limit only in sub queries with UNION
        $query = (new Query())
            ->select(['id', 'name'])
            ->from(
                (new Query())
                    ->select(['id', 'name'])
                    ->from('item')
                    ->limit(2)
            )
            ->union(
                (new Query())
                    ->select(['id', 'name'])
                    ->from(
                        (new Query())
                            ->select(['id', 'name'])
                            ->from(['category'])
                            ->limit(2)
                    )
            );

        $result = $query->all($connection);
        $this->assertNotEmpty($result);
        $this->assertCount(4, $result);
    }
}

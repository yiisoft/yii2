<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Query;
use yiiunit\base\db\BaseQuery;

/**
 * Unit test for {@see \yii\db\Query} with Oracle driver.
 */
#[Group('db')]
#[Group('oci')]
#[Group('query')]
class QueryTest extends BaseQuery
{
    protected $driverName = 'oci';

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
            (int) $rows[0]['id'],
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
            array_map('intval', $rows),
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
            array_map('intval', $rows),
            "LIMIT '2' without OFFSET should return the first two rows.",
        );
    }

    public function testUnion(): void
    {
        $this->markTestSkipped('Unsupported use of WITH clause in Oracle.');
    }
}

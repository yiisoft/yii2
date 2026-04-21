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
use yiiunit\base\db\BaseQueryBuilderUnion;

/**
 * Unit tests for {@see \yii\db\oci\QueryBuilder} UNION and WITH query building tests for the Oracle driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('querybuilder')]
class QueryBuilderUnionTest extends BaseQueryBuilderUnion
{
    protected $driverName = 'oci';
    protected static string $driverNameStatic = 'oci';

    /**
     * Oracle does not support the `RECURSIVE` keyword. Recursion is implicit when a CTE references itself.
     */
    public function testBuildWithQueryRecursive(): void
    {
        $db = $this->getConnection(true, false);

        $with1Query = (new Query())
            ->select('id')
            ->from('t1')
            ->where('expr = 1');
        $query = (new Query())
            ->withQuery($with1Query, 'a1', true)
            ->from('a1');

        $expectedQuerySql = $this->replaceQuotes(
            <<<SQL
            WITH a1 AS (SELECT [[id]] FROM [[t1]] WHERE expr = 1) SELECT * FROM [[a1]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'Oracle WITH query should omit RECURSIVE keyword.',
        );
        self::assertEmpty(
            $queryParams,
            'WITH query should have no bound parameters.',
        );
    }
}

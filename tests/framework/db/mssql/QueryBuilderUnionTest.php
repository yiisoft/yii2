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
use yiiunit\base\db\BaseQueryBuilderUnion;

/**
 * Unit tests for {@see \yii\db\mssql\QueryBuilder} UNION and WITH query building tests for the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('querybuilder')]
class QueryBuilderUnionTest extends BaseQueryBuilderUnion
{
    protected $driverName = 'sqlsrv';
    protected static string $driverNameStatic = 'sqlsrv';

    /**
     * Ensures `buildWithQueries()` joins multiple CTEs with commas.
     */
    public function testBuildWithQueryMultiple(): void
    {
        $db = $this->getConnection(true, false);

        $with1Query = (new Query())
            ->select('id')
            ->from('t1')
            ->where('expr = 1');
        $with2Query = (new Query())
            ->select('id')
            ->from('t2')
            ->where('expr = 2');
        $query = (new Query())
            ->withQuery($with1Query, 'a1')
            ->withQuery($with2Query, 'a2')
            ->from('a1');

        $expectedQuerySql = $this->replaceQuotes(
            <<<SQL
            WITH a1 AS (SELECT [[id]] FROM [[t1]] WHERE expr = 1), a2 AS (SELECT [[id]] FROM [[t2]] WHERE expr = 2) SELECT * FROM [[a1]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'Multiple CTEs should be joined with commas.',
        );
        self::assertEmpty(
            $queryParams,
            'Multiple CTEs query should have no bound parameters.',
        );
    }

    /**
     * SQL Server does not support the `RECURSIVE` keyword. Recursion is implicit when a CTE references itself.
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
            'MSSQL WITH query should omit RECURSIVE keyword.',
        );
        self::assertEmpty(
            $queryParams,
            'WITH query should have no bound parameters.',
        );
    }
}

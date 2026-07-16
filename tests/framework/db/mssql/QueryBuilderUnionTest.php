<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\Query;
use yiiunit\base\db\BaseQueryBuilderUnion;
use yiiunit\framework\db\mssql\providers\QueryBuilderUnionProvider;

use function str_replace;

/**
 * Unit tests for {@see \yii\db\mssql\QueryBuilder} UNION and WITH query building tests for the MSSQL driver.
 *
 * {@see QueryBuilderUnionProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('query-builder')]
class QueryBuilderUnionTest extends BaseQueryBuilderUnion
{
    protected $driverName = 'sqlsrv';
    protected static string $driverNameStatic = 'sqlsrv';

    public function testBuildUnionPaginationWithoutOrderBy(): void
    {
        $db = $this->getConnection(true, false);

        $query = (new Query())
            ->select('id')
            ->from('table1')
            ->union((new Query())->select('id')->from('table2'))
            ->unionLimit(2)
            ->unionOffset(1);

        $expectedQuerySql = $this->replaceQuotes(
            <<<SQL
            (SELECT [[id]] FROM [[table1]])
            UNION ( SELECT [[id]] FROM [[table2]] )
            ORDER BY 1
            OFFSET 1 ROWS
            FETCH NEXT 2 ROWS ONLY
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'An ordinal ORDER BY must be synthesized for OFFSET/FETCH.',
        );
        self::assertEmpty(
            $queryParams,
            'No parameters must be bound.',
        );
    }

    public function testBuildUnionLimitZero(): void
    {
        $db = $this->getConnection(true, false);

        $query = (new Query())
            ->select('id')
            ->from('table1')
            ->union((new Query())->select('id')->from('table2'))
            ->unionLimit(0);

        $expectedQuerySql = $this->replaceQuotes(
            <<<SQL
            (SELECT TOP (0) [[id]] FROM [[table1]])
            UNION ( SELECT TOP (0) [[id]] FROM [[table2]] )
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'Every operand must be limited to zero rows.',
        );
        self::assertEmpty(
            $queryParams,
            'No parameters must be bound.',
        );
    }

    public function testBuildNestedUnionLimitZero(): void
    {
        $db = $this->getConnection(true, false);

        $nestedUnion = (new Query())
            ->select('id')
            ->from('table2')
            ->union((new Query())->select('id')->from('table3'));
        $query = (new Query())
            ->select('id')
            ->from('table1')
            ->union($nestedUnion)
            ->unionLimit(0);

        $expectedQuerySql = $this->replaceQuotes(
            <<<SQL
            (SELECT TOP (0) [[id]] FROM [[table1]])
            UNION ( (SELECT TOP (0) [[id]] FROM [[table2]])
            UNION ( SELECT TOP (0) [[id]] FROM [[table3]] ) )
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'Every nested operand must be limited to zero rows.',
        );
        self::assertEmpty(
            $queryParams,
            'No parameters must be bound.',
        );
    }

    #[DataProviderExternal(QueryBuilderUnionProvider::class, 'rawUnionLimitZero')]
    public function testBuildRawUnionLimitZero(string $rawUnion, string $expectedQuerySql): void
    {
        $db = $this->getConnection(true, false);

        $query = (new Query())
            ->select('id')
            ->from('table1')
            ->union($rawUnion)
            ->unionLimit(0);

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'Raw operand must be rewritten to a zero-row SELECT.',
        );
        self::assertEmpty(
            $queryParams,
            'No parameters must be bound.',
        );
    }

    public function testThrowNotSupportedExceptionForNonSelectRawUnionWithLimitZero(): void
    {
        $db = $this->getConnection(true, false);
        $query = (new Query())
            ->select('id')
            ->from('table1')
            ->union('WITH source AS (SELECT 1 AS id) SELECT id FROM source')
            ->unionLimit(0);

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('must start with SELECT');

        $db->getQueryBuilder()->build($query);
    }

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

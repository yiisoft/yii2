<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Base tests for UNION and WITH query building across all database drivers.
 *
 * Validates SQL generation for UNION/UNION ALL used as standalone queries, subqueries in FROM, JOIN, and IN clauses,
 * and WITH (CTE) queries including recursive variants.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
abstract class BaseQueryBuilderUnion extends DatabaseTestCase
{
    public function testBuildUnion(): void
    {
        $db = $this->getConnection(true, false);

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            (SELECT [[id]] FROM [[TotalExample]] [[t1]] WHERE (w > 0) AND (x < 2))
            UNION ( SELECT [[id]] FROM [[TotalTotalExample]] [[t2]] WHERE w > 5 )
            UNION ALL ( SELECT [[id]] FROM [[TotalTotalExample]] [[t3]] WHERE w = 3 )
            SQL
        );

        $secondQuery = (new Query())
            ->select('id')
            ->from('TotalTotalExample t2')
            ->where('w > 5');
        $thirdQuery = (new Query())
            ->select('id')
            ->from('TotalTotalExample t3')
            ->where('w = 3');
        $query = (new Query())
            ->select('id')
            ->from('TotalExample t1')
            ->where(['and', 'w > 0', 'x < 2'])
            ->union($secondQuery)
            ->union($thirdQuery, true);

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'UNION with multiple queries generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'UNION query should have no bound parameters.',
        );
    }

    public function testBuildUnionSubqueryInFrom(): void
    {
        $db = $this->getConnection(true, false);

        $union = (new Query())
            ->select('id, name')
            ->from('table1')
            ->union((new Query())->select('id, name')->from('table2'));
        $query = (new Query())
            ->select('*')
            ->from(['sub' => $union]);

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            SELECT * FROM ((SELECT [[id]], [[name]] FROM [[table1]])
            UNION ( SELECT [[id]], [[name]] FROM [[table2]] )) [[sub]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'UNION subquery in FROM clause generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'UNION subquery in FROM should have no bound parameters.',
        );
    }

    public function testBuildUnionAllSubqueryInFrom(): void
    {
        $db = $this->getConnection(true, false);

        $union = (new Query())
            ->select('id, name')
            ->from('table1')
            ->union((new Query())->select('id, name')->from('table2'), true);
        $query = (new Query())
            ->select('*')
            ->from(['sub' => $union]);

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            SELECT * FROM ((SELECT [[id]], [[name]] FROM [[table1]])
            UNION ALL ( SELECT [[id]], [[name]] FROM [[table2]] )) [[sub]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'UNION ALL subquery in FROM clause generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'UNION ALL subquery in FROM should have no bound parameters.',
        );
    }

    public function testBuildUnionSubqueryInJoin(): void
    {
        $db = $this->getConnection(true, false);

        $union = (new Query())
            ->select('id, name')
            ->from('table1')
            ->union((new Query())->select('id, name')->from('table2'));
        $query = (new Query())
            ->select('*')
            ->from('main_table')
            ->leftJoin(['sub' => $union], 'main_table.id = sub.id');

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            SELECT * FROM [[main_table]]
            LEFT JOIN ((SELECT [[id]], [[name]] FROM [[table1]])
            UNION ( SELECT [[id]], [[name]] FROM [[table2]] )) [[sub]] ON main_table.id = sub.id
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'UNION subquery in JOIN clause generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'UNION subquery in JOIN should have no bound parameters.',
        );
    }

    public function testBuildUnionSubqueryInCondition(): void
    {
        $db = $this->getConnection(true, false);

        $union = (new Query())
            ->select('id')
            ->from('table1')
            ->union((new Query())->select('id')->from('table2'));
        $query = (new Query())
            ->select('*')
            ->from('main_table')
            ->where(['in', 'id', $union]);

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            SELECT * FROM [[main_table]]
            WHERE [[id]] IN ((SELECT [[id]] FROM [[table1]]) UNION ( SELECT [[id]] FROM [[table2]] ))
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'UNION subquery in IN condition generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'UNION subquery in IN condition should have no bound parameters.',
        );
    }

    public function testBuildWithQuery(): void
    {
        $db = $this->getConnection(true, false);

        $with1Query = (new Query())
            ->select('id')
            ->from('t1')
            ->where('expr = 1');
        $with2Query = (new Query())
            ->select('id')
            ->from('t2')
            ->innerJoin('a1', 't2.id = a1.id')
            ->where('expr = 2');
        $with3Query = (new Query())
            ->select('id')
            ->from('t3')
            ->where('expr = 3');
        $query = (new Query())
            ->withQuery($with1Query, 'a1')
            ->withQuery($with2Query->union($with3Query), 'a2')
            ->from('a2');

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            WITH a1 AS (SELECT [[id]] FROM [[t1]] WHERE expr = 1),
            a2 AS ((SELECT [[id]] FROM [[t2]] INNER JOIN [[a1]] ON t2.id = a1.id WHERE expr = 2)
            UNION ( SELECT [[id]] FROM [[t3]] WHERE expr = 3 ))
            SELECT * FROM [[a2]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            str_replace("\n", ' ', $expectedQuerySql),
            $actualQuerySql,
            'WITH query with UNION generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'WITH query should have no bound parameters.',
        );
    }

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

        $expectedQuerySql = static::replaceQuotes(
            <<<SQL
            WITH RECURSIVE a1 AS (SELECT [[id]] FROM [[t1]] WHERE expr = 1) SELECT * FROM [[a1]]
            SQL
        );

        [$actualQuerySql, $queryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            $expectedQuerySql,
            $actualQuerySql,
            'WITH RECURSIVE query generated unexpected SQL.',
        );
        self::assertEmpty(
            $queryParams,
            'WITH RECURSIVE query should have no bound parameters.',
        );
    }
}

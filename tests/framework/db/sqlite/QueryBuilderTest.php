<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use Closure;
use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;
use yii\db\sqlite\QueryBuilder;
use yiiunit\base\db\BaseQueryBuilder;

/**
 * Unit test for {@see \yii\db\QueryBuilder} with SQLite driver.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('queryBuilder')]
final class QueryBuilderTest extends BaseQueryBuilder
{
    protected $driverName = 'sqlite';
    protected static string $driverNameStatic = 'sqlite';

    protected $likeEscapeCharSql = " ESCAPE '\\'";

    public function testBuildOrderByAndLimitWithOffsetAndLimit(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(10)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT 10 OFFSET 5
            SQL,
            $actualQuerySql,
            'OFFSET and LIMIT should generate LIMIT x OFFSET y.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'OFFSET/LIMIT query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithLimitOnly(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(10);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT 10
            SQL,
            $actualQuerySql,
            'LIMIT without OFFSET should generate LIMIT x.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'LIMIT-only query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithOffsetOnly(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->offset(10);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT -1 OFFSET 10
            SQL,
            $actualQuerySql,
            "OFFSET without LIMIT should generate SQLite's documented negative LIMIT sentinel.",
        );
        self::assertEmpty(
            $actualQueryParams,
            'OFFSET-only query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithoutOffsetAndLimit(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example');

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example`
            SQL,
            $actualQuerySql,
            'Query without OFFSET/LIMIT should not contain pagination clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'Query without OFFSET/LIMIT should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithOrderByWithoutPagination(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->orderBy('id');

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` ORDER BY `id`
            SQL,
            $actualQuerySql,
            'ORDER BY without OFFSET/LIMIT should not contain pagination clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'ORDER BY without pagination should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithZeroLimit(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(0);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT 0
            SQL,
            $actualQuerySql,
            "Limit '0' should generate LIMIT '0' and return zero rows.",
        );
        self::assertEmpty(
            $actualQueryParams,
            "Limit '0' query should have no bound parameters.",
        );
    }

    public function testBuildOrderByAndLimitWithZeroLimitAndOffset(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(0)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT 0 OFFSET 5
            SQL,
            $actualQuerySql,
            "Limit '0' with offset should still generate LIMIT '0' and return zero rows.",
        );
        self::assertEmpty(
            $actualQueryParams,
            "Limit '0' with offset query should have no bound parameters.",
        );
    }

    public function testBuildOrderByAndLimitWithExplicitOrderBy(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->orderBy('id')
            ->limit(10)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` ORDER BY `id` LIMIT 10 OFFSET 5
            SQL,
            $actualQuerySql,
            'Explicit ORDER BY should be preserved alongside LIMIT/OFFSET clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'Query with explicit ORDER BY should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithExpressionLimitAndOffset(): void
    {
        $query = (new Query())
            ->select('id')
            ->from('example')
            ->limit(new Expression('1 + 1'))
            ->offset(new Expression('1'));

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT `id` FROM `example` LIMIT 1 + 1 OFFSET 1
            SQL,
            $actualQuerySql,
            'Scalar Expression values should generate LIMIT/OFFSET clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'Pagination query with scalar expressions should have no bound parameters.',
        );
    }

    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_PK,
                $this->primaryKey()->first()->after('col_before'),
                'integer PRIMARY KEY AUTOINCREMENT NOT NULL',
            ],
        ]);
    }

    public static function indexesProvider(): array
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';

        $indexName = 'myindex';
        $schemaName = 'myschema';
        $tableName = 'mytable';

        $result['with schema'] = [
            "CREATE INDEX {{{$schemaName}}}.[[$indexName]] ON {{{$tableName}}} ([[C_index_1]])",
            fn(QueryBuilder $qb) => $qb->createIndex($indexName, $schemaName . '.' . $tableName, 'C_index_1'),
        ];

        return $result;
    }

    public function testCommentColumn(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\db\sqlite\QueryBuilder::addCommentOnColumn is not supported by SQLite.',
        );

        parent::testCommentColumn();
    }

    public function testCommentTable(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\db\sqlite\QueryBuilder::addCommentOnTable is not supported by SQLite.',
        );

        parent::testCommentTable();
    }

    public static function batchInsertProvider(): array
    {
        $data = parent::batchInsertProvider();
        $data['escape-danger-chars'][3] = "INSERT INTO `customer` (`address`) VALUES ('SQL-danger chars are escaped: ''); --')";
        return $data;
    }

    public function testRenameTable(): void
    {
        $sql = $this->getQueryBuilder()->renameTable('table_from', 'table_to');
        $this->assertEquals('ALTER TABLE `table_from` RENAME TO `table_to`', $sql);
    }

    public function testResetSequence(): void
    {
        $qb = $this->getQueryBuilder(true, true);

        $expected = "UPDATE sqlite_sequence SET seq='5' WHERE name='item'";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "UPDATE sqlite_sequence SET seq='3' WHERE name='item'";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public static function upsertProvider(): array
    {
        $concreteData = [
            'regular values' => [
                3 => 'WITH "EXCLUDED" (`email`, `address`, `status`, `profile_id`) AS (VALUES (:qp0, :qp1, :qp2, :qp3)) UPDATE `T_upsert` SET `address`=(SELECT `address` FROM `EXCLUDED`), `status`=(SELECT `status` FROM `EXCLUDED`), `profile_id`=(SELECT `profile_id` FROM `EXCLUDED`) WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3);',
            ],
            'regular values with update part' => [
                3 => 'WITH "EXCLUDED" (`email`, `address`, `status`, `profile_id`) AS (VALUES (:qp0, :qp1, :qp2, :qp3)) UPDATE `T_upsert` SET `address`=:qp4, `status`=:qp5, `orders`=T_upsert.orders + 1 WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3);',
            ],
            'regular values without update part' => [
                3 => 'INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3)',
            ],
            'query' => [
                3 => 'WITH "EXCLUDED" (`email`, `status`) AS (SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1) UPDATE `T_upsert` SET `status`=(SELECT `status` FROM `EXCLUDED`) WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1;',
            ],
            'query with update part' => [
                3 => 'WITH "EXCLUDED" (`email`, `status`) AS (SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1) UPDATE `T_upsert` SET `address`=:qp1, `status`=:qp2, `orders`=T_upsert.orders + 1 WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1;',
            ],
            'query without update part' => [
                3 => 'INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1',
            ],
            'values and expressions' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions with update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions without update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'query, values and expressions with update part' => [
                3 => 'WITH "EXCLUDED" (`email`, [[time]]) AS (SELECT :phEmail AS `email`, now() AS [[time]]) UPDATE {{%T_upsert}} SET `ts`=:qp1, [[orders]]=T_upsert.orders + 1 WHERE {{%T_upsert}}.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]];',
            ],
            'query, values and expressions without update part' => [
                3 => 'WITH "EXCLUDED" (`email`, [[time]]) AS (SELECT :phEmail AS `email`, now() AS [[time]]) UPDATE {{%T_upsert}} SET `ts`=:qp1, [[orders]]=T_upsert.orders + 1 WHERE {{%T_upsert}}.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]];',
            ],
            'no columns to update' => [
                3 => 'INSERT OR IGNORE INTO `T_upsert_1` (`a`) VALUES (:qp0)',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }

    /**
     * @dataProvider primaryKeysProvider
     * @param string $sql
     */
    public function testAddDropPrimaryKey($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addPrimaryKey|dropPrimaryKey) is not supported by SQLite\.$/',
        );

        parent::testAddDropPrimaryKey($sql, $builder);
    }

    /**
     * @dataProvider foreignKeysProvider
     * @param string $sql
     */
    public function testAddDropForeignKey($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addForeignKey|dropForeignKey) is not supported by SQLite\.$/',
        );

        parent::testAddDropForeignKey($sql, $builder);
    }

    /**
     * @dataProvider uniquesProvider
     * @param string $sql
     */
    public function testAddDropUnique($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addUnique|dropUnique) is not supported by SQLite\.$/',
        );

        parent::testAddDropUnique($sql, $builder);
    }

    /**
     * @dataProvider checksProvider
     * @param string $sql
     */
    public function testAddDropCheck($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addCheck|dropCheck) is not supported by SQLite\.$/',
        );

        parent::testAddDropCheck($sql, $builder);
    }

    /**
     * @dataProvider defaultValuesProvider
     * @param string $sql
     */
    public function testAddDropDefaultValue($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addDefaultValue|dropDefaultValue) is not supported by SQLite\.$/',
        );

        parent::testAddDropDefaultValue($sql, $builder);
    }
}

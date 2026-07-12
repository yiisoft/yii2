<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use Closure;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;
use yii\db\sqlite\QueryBuilder;
use yiiunit\base\db\BaseQueryBuilder;
use yiiunit\framework\db\sqlite\providers\QueryBuilderProvider;

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

    #[DataProviderExternal(QueryBuilderProvider::class, 'upsert')]
    public function testUpsert(
        string $table,
        array|Query $insertColumns,
        array|bool|null $updateColumns,
        array|string $expectedSql,
        array $expectedParams,
    ): void {
        $db = $this->getConnection(false);

        $actualParams = [];

        $actualSql = $db->getQueryBuilder()->upsert(
            $table,
            $insertColumns,
            $updateColumns,
            $actualParams,
        );

        self::assertSame(
            $expectedSql,
            $actualSql,
            'Generated SQL must match the expected statement.',
        );
        self::assertSame(
            $expectedParams,
            $actualParams,
            'Bound parameters must match the expected binding map.',
        );
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

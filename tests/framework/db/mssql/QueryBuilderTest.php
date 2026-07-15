<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use Closure;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Schema;
use yiiunit\base\db\BaseQueryBuilder;
use yiiunit\framework\db\mssql\providers\QueryBuilderProvider;
use yiiunit\support\DbHelper;

/**
 * Unit test for {@see \yii\db\QueryBuilder} with MSSQL driver.
 *
 * {@see QueryBuilderProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('query-builder')]
final class QueryBuilderTest extends BaseQueryBuilder
{
    public $driverName = 'sqlsrv';
    public static string $driverNameStatic = 'sqlsrv';

    protected $likeParameterReplacements = [
        '\%' => '[%]',
        '\_' => '[_]',
        '[' => '[[]',
        ']' => '[]]',
        '\\\\' => '[\\]',
    ];

    public function testOffsetLimit(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example')
            ->limit(10)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL,
            $actualQuerySql,
            'OFFSET and LIMIT should emit ORDER BY (SELECT NULL) fallback with OFFSET and FETCH clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'OFFSET and LIMIT query should have no bound parameters.',
        );
    }

    public function testLimit(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example')
            ->limit(10);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY
            SQL,
            $actualQuerySql,
            "LIMIT without OFFSET should emit ORDER BY (SELECT NULL) fallback with OFFSET '0' ROWS and FETCH clause.",
        );
        self::assertEmpty(
            $actualQueryParams,
            'LIMIT-only query should have no bound parameters.',
        );
    }

    public function testOffset(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example')
            ->offset(10);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 10 ROWS
            SQL,
            $actualQuerySql,
            'OFFSET without LIMIT should emit ORDER BY (SELECT NULL) fallback with OFFSET clause and no FETCH.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'OFFSET-only query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithoutOffsetAndLimit(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example');

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example]
            SQL,
            $actualQuerySql,
            'Query without OFFSET/LIMIT should not contain OFFSET or FETCH clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'Query without OFFSET/LIMIT should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithExplicitOrderBy(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example')
            ->orderBy('id')
            ->limit(10)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example] ORDER BY [id] OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL,
            $actualQuerySql,
            'Explicit ORDER BY should be preserved alongside OFFSET/FETCH clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'Query with explicit ORDER BY should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithOrderByWithoutPagination(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->from('example')
            ->orderBy('id');

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT [id] FROM [example] ORDER BY [id]
            SQL,
            $actualQuerySql,
            'ORDER BY without OFFSET/LIMIT should not contain OFFSET or FETCH clauses.',
        );
        self::assertEmpty(
            $actualQueryParams,
            'ORDER BY without pagination should have no bound parameters.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'zeroLimitQueries')]
    public function testBuildOrderByAndLimitWithZeroLimit(
        Closure $queryFactory,
        string $expectedSql,
        array $expectedParams,
    ): void {
        [$actualSql, $actualParams] = $this->getQueryBuilder()->build($queryFactory());

        self::assertSame(
            $expectedSql,
            $actualSql,
            "Limit '0' query generated unexpected SQL.",
        );
        self::assertSame(
            $expectedParams,
            $actualParams,
            "Limit '0' query generated unexpected parameters.",
        );
        self::assertStringNotContainsString(
            'SELECT * FROM (',
            $actualSql,
            "Limit '0' must not wrap the original SELECT in a derived table.",
        );
        self::assertStringNotContainsString(
            'WHERE 1=0',
            $actualSql,
            "Limit '0' must not use a false WHERE condition.",
        );
        self::assertStringNotContainsString(
            'OFFSET',
            $actualSql,
            "Limit '0' must not emit OFFSET.",
        );
        self::assertStringNotContainsString(
            'FETCH',
            $actualSql,
            "Limit '0' must not emit FETCH.",
        );
    }

    public function testBuildOrderByAndLimitWithDistinctWithoutOrderBy(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->distinct()
            ->from('example')
            ->limit(10)
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT DISTINCT [id] FROM [example] ORDER BY 1 OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY
            SQL,
            $actualQuerySql,
            "DISTINCT with pagination must use ORDER BY '1' (SQL Server rejects ORDER BY (SELECT NULL) with DISTINCT).",
        );
        self::assertEmpty(
            $actualQueryParams,
            'DISTINCT pagination query should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithDistinctLimitOnly(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->distinct()
            ->from('example')
            ->limit(10);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT DISTINCT [id] FROM [example] ORDER BY 1 OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY
            SQL,
            $actualQuerySql,
            "DISTINCT with LIMIT only must use ORDER BY '1' and default OFFSET to '0'.",
        );
        self::assertEmpty(
            $actualQueryParams,
            'DISTINCT with LIMIT only should have no bound parameters.',
        );
    }

    public function testBuildOrderByAndLimitWithDistinctOffsetOnly(): void
    {
        $db = $this->getConnection(false, false);

        $query = new Query();

        $query
            ->select('id')
            ->distinct()
            ->from('example')
            ->offset(5);

        [$actualQuerySql, $actualQueryParams] = $db->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT DISTINCT [id] FROM [example] ORDER BY 1 OFFSET 5 ROWS
            SQL,
            $actualQuerySql,
            "DISTINCT with OFFSET only must use ORDER BY '1' and omit FETCH.",
        );
        self::assertEmpty(
            $actualQueryParams,
            'DISTINCT with OFFSET only should have no bound parameters.',
        );
    }

    public function testCommentColumn(): void
    {
        self::markTestSkipped(
            "Covered by 'testAddCommentOnColumn()' and 'testDropCommentFromColumn()'.",
        );
    }

    public function testCommentTable(): void
    {
        self::markTestSkipped(
            "Covered by 'testAddCommentOnTable()' and 'testDropCommentFromTable()'.",
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addCommentOnTable')]
    public function testAddCommentOnTable(string $table, string $comment, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addCommentOnTable($table, $comment),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addCommentOnColumn')]
    public function testAddCommentOnColumn(string $table, string $column, string $comment, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addCommentOnColumn($table, $column, $comment),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropCommentFromTable')]
    public function testDropCommentFromTable(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->dropCommentFromTable($table),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropCommentFromColumn')]
    public function testDropCommentFromColumn(string $table, string $column, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->dropCommentFromColumn($table, $column),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        return [...parent::columnTypes()];
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'batchInsert')]
    public function testBatchInsert(
        string $table,
        array $columns,
        array $value,
        string $expected,
        bool $replaceQuotes = true,
    ): void {
        parent::testBatchInsert($table, $columns, $value, $expected, $replaceQuotes);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'insert')]
    public function testInsert(
        string $table,
        array|Query $columns,
        array $params,
        string $expectedSQL,
        array|string $expectedParams,
        bool $replaceQuotes = true,
    ): void {
        parent::testInsert($table, $columns, $params, $expectedSQL, $expectedParams, $replaceQuotes);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'resetSequence')]
    public function testResetSequence(string $table, int|null $value, string $expected): void
    {
        $qb = $this->getQueryBuilder();

        $sql = $value === null ? $qb->resetSequence($table) : $qb->resetSequence($table, $value);

        self::assertSame(
            $expected,
            $sql,
            'Generated SQL must reseed SQL Server so the next identity value matches the requested value.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'upsert')]
    public function testUpsert(
        string $table,
        array|Query $insertColumns,
        array|bool|null $updateColumns,
        array|string $expectedSql,
        array $expectedParams,
    ): void {
        $db = $this->getConnection(false, false);

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

    #[DataProviderExternal(QueryBuilderProvider::class, 'addDefaultValue')]
    public function testAddDefaultValue(
        string $name,
        string $table,
        string $column,
        mixed $value,
        string $expected,
    ): void {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addDefaultValue($name, $table, $column, $value),
            'Generated SQL must match the expected DEFAULT constraint statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'defaultValuesProvider')]
    public function testAddDropDefaultValue(string $sql, Closure $builder): void
    {
        parent::testAddDropDefaultValue($sql, $builder);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'alterColumn')]
    public function testAlterColumn(string|Closure $type, string $expected): void
    {
        $db = $this->getConnection(false, false);

        if ($type instanceof Closure) {
            $type = $type($this->getDb());
        }

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->alterColumn('foo1', 'bar', $type),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'alterColumnQualifiedTableNames')]
    public function testAlterColumnWithQualifiedTableName(string $table, string|Closure $type, string $expected): void
    {
        $db = $this->getConnection(false, false);

        if ($type instanceof Closure) {
            $type = $type($this->getDb());
        }

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->alterColumn($table, 'bar', $type),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropColumn')]
    public function testDropColumn(string $table, string $column, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->dropColumn($table, $column),
            'Generated SQL must match the expected batch.',
        );
    }

    public function testDropColumnOnDb(): void
    {
        $db = $this->getConnection(false, false);

        $sql = $db->getQueryBuilder()->alterColumn(
            'foo1',
            'bar',
            $this->string(64)->defaultValue('')->check('LEN(bar) < 5')->unique(),
        );

        $db->createCommand($sql)->execute();

        $sql = $db->getQueryBuilder()->dropColumn('foo1', 'bar');

        self::assertEquals(
            0,
            $db->createCommand($sql)->execute(),
            'Batch must affect zero rows.',
        );

        $schema = $db->getTableSchema('[foo1]', true);

        self::assertEquals(
            null,
            $schema->getColumn('bar'),
            'Column must be removed from the table schema.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropColumnConstraintsOnDb')]
    public function testDropColumnDropsConstraintOnDb(
        array $tableNames,
        array $createTablesSql,
        string $table,
        string $column,
    ): void {
        $db = $this->getConnection(false, false);

        DbHelper::dropTablesIfExist($db, $tableNames);

        foreach ($createTablesSql as $sql) {
            $db->createCommand($sql)->execute();
        }

        $this->assertDropColumnOnDb($db, $table, $column);

        DbHelper::dropTablesIfExist($db, $tableNames);
    }

    private function assertDropColumnOnDb(Connection $db, string $table, string $column): void
    {
        $sql = $db->getQueryBuilder()->dropColumn($table, $column);

        self::assertSame(
            0,
            $db->createCommand($sql)->execute(),
            'Batch must affect zero rows.',
        );

        $schema = $db->getTableSchema($db->quoteTableName($table), true);

        self::assertNull(
            $schema->getColumn($column),
            'Column must be removed from the table schema.',
        );
    }

    public function testThrowInvalidArgumentExceptionWhenInsertTargetsMissingTableSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Table not found: non_existent_table',
        );

        $params = [];

        $this->getConnection()->getQueryBuilder()->insert('non_existent_table', ['email' => 'x@example.com'], $params);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'buildFrom')]
    public function testBuildFrom(string $table, string $expected): void
    {
        parent::testBuildFrom($table, $expected);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'renameTable')]
    public function testRenameTable(string $oldName, string $newName, string $expectedSql): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expectedSql,
            $db->getQueryBuilder()->renameTable($oldName, $newName),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'renameColumn')]
    public function testRenameColumn(string $table, string $oldName, string $newName, string $expectedSql): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expectedSql,
            $db->getQueryBuilder()->renameColumn($table, $oldName, $newName),
            'Generated SQL must match the expected batch.',
        );
    }

    public function testSelectExists(): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            <<<SQL
            SELECT CASE WHEN EXISTS(SELECT 1 FROM [customer]) THEN 1 ELSE 0 END AS [result]
            SQL,
            $db->getQueryBuilder()->selectExists(
                <<<SQL
                SELECT 1 FROM [customer]
                SQL,
            ),
            "Generated SQL must match the expected 'EXISTS()' statement with a simple subquery.",
        );
        self::assertSame(
            <<<SQL
            SELECT CASE WHEN EXISTS(SELECT 1 FROM [customer] WHERE [status] = 2) THEN 1 ELSE 0 END AS [result]
            SQL,
            $db->getQueryBuilder()->selectExists(
                <<<SQL
                SELECT 1 FROM [customer] WHERE [status] = 2
                SQL,
            ),
            "Generated SQL must match the expected 'EXISTS()' statement with a subquery containing a WHERE clause.",
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'checkIntegrity')]
    public function testCheckIntegrity(bool $check, string $schema, string $table, string $expectedSql): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expectedSql,
            $db->getQueryBuilder()->checkIntegrity($check, $schema, $table),
            'Generated SQL must match the expected statement.',
        );
    }

    public function testResetSequenceThrowsExceptionForNonExistentTable(): void
    {
        $db = $this->getConnection(false, false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Table not found: non_existent_table',
        );

        $db->getQueryBuilder()->resetSequence('non_existent_table');
    }

    public function testResetSequenceThrowsExceptionForTableWithoutSequence(): void
    {
        $db = $this->getConnection(false, false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "There is not sequence associated with table 'order_item'.",
        );

        $db->getQueryBuilder()->resetSequence('order_item');
    }

    public function testUpdateWithVarbinaryData(): void
    {
        $db = $this->getConnection(false, false);

        $params = [];

        $sql = $db->getQueryBuilder()->update('T_upsert_varbinary', ['blob_col' => 'test data'], ['id' => 1], $params);

        self::assertSame(
            <<<SQL
            UPDATE [T_upsert_varbinary] SET [blob_col]=CONVERT(VARBINARY(MAX), 0x746573742064617461) WHERE [id]=:qp0
            SQL,
            $sql,
            'VARBINARY value must be inlined, not parameter-bound.',
        );
        self::assertSame(
            [':qp0' => 1],
            $params,
            'Only the WHERE value must be bound as a parameter.',
        );
    }

    public function testThrowNotSupportedExceptionForCompositeInWithSubquery(): void
    {
        $db = $this->getConnection(false, false);

        $params = [];

        $condition = [
            'in',
            ['id', 'name'],
            (new Query())->select(['id', 'name'])->from('users'),
        ];

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\db\mssql\conditions\InConditionBuilder::buildSubqueryInCondition is not supported by MSSQL.',
        );

        $db->getQueryBuilder()->buildCondition($condition, $params);
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'createTableWithQualifiedTableNames')]
    public function testCreateTableWithQualifiedTableNames(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->createTable($table, ['id' => Schema::TYPE_PK]),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addColumnWithQualifiedTableNames')]
    public function testAddColumnWithQualifiedTableNames(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addColumn($table, 'label', Schema::TYPE_STRING),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'createIndexWithQualifiedTableNames')]
    public function testCreateIndexWithQualifiedTableNames(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->createIndex('idx_label', $table, 'label'),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addPrimaryKeyWithQualifiedTableNames')]
    public function testAddPrimaryKeyWithQualifiedTableNames(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addPrimaryKey('pk_T_migration', $table, 'id'),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addForeignKeyWithQualifiedTableNames')]
    public function testAddForeignKeyWithQualifiedTableNames(string $table, string $refTable, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->addForeignKey('fk_child_parent', $table, 'parent_id', $refTable, 'id'),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropTableWithQualifiedTableNames')]
    public function testDropTableWithQualifiedTableNames(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        self::assertSame(
            $expected,
            $db->getQueryBuilder()->dropTable($table),
            'Schema-qualified name must appear in generated SQL.',
        );
    }

    public function testDdlOperationsApplyConfiguredDefaultSchemaToUnqualifiedNames(): void
    {
        $db = $this->getConnection(false, false);

        $db->getSchema()->defaultSchema = 'ecbox';

        $qb = $db->getQueryBuilder();

        self::assertSame(
            <<<SQL
            CREATE TABLE [ecbox].[T_migration] (
            \t[id] int IDENTITY PRIMARY KEY
            )
            SQL,
            $qb->createTable('T_migration', ['id' => Schema::TYPE_PK]),
            'Unqualified `createTable` target must resolve to the configured schema.',
        );
        self::assertSame(
            <<<SQL
            ALTER TABLE [ecbox].[T_migration] ADD [label] nvarchar(255)
            SQL,
            $qb->addColumn('T_migration', 'label', Schema::TYPE_STRING),
            'Unqualified `addColumn` target must resolve to the configured schema.',
        );
        self::assertSame(
            <<<SQL
            DROP TABLE [ecbox].[T_migration]
            SQL,
            $qb->dropTable('T_migration'),
            'Unqualified `dropTable` target must resolve to the configured schema.',
        );
    }
}

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
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yiiunit\base\db\BaseQueryBuilder;
use yiiunit\framework\db\mssql\providers\QueryBuilderProvider;

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
        $query = new Query();

        $query->select('id')->from('example')->limit(10)->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->from('example')->limit(10);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->from('example')->offset(10);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->from('example');

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->from('example')->orderBy('id')->limit(10)->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->from('example')->orderBy('id');

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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

    public function testBuildOrderByAndLimitWithZeroLimit(): void
    {
        $query = new Query();

        $query->select('id')->from('example')->limit(0);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT * FROM (SELECT [id] FROM [example]) sub WHERE 1=0
            SQL,
            $actualQuerySql,
            "Limit '0' must wrap the query with WHERE 1=0 to return zero rows (portable semantics).",
        );
        self::assertEmpty(
            $actualQueryParams,
            "Limit '0' query should have no bound parameters.",
        );
    }

    public function testBuildOrderByAndLimitWithZeroLimitAndOffset(): void
    {
        $query = new Query();

        $query->select('id')->from('example')->limit(0)->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT * FROM (SELECT [id] FROM [example]) sub WHERE 1=0
            SQL,
            $actualQuerySql,
            "Limit '0' with offset must still return zero rows (offset is irrelevant on empty result).",
        );
        self::assertEmpty(
            $actualQueryParams,
            "Limit '0' with offset query should have no bound parameters.",
        );
    }

    public function testBuildOrderByAndLimitWithDistinctWithoutOrderBy(): void
    {
        $query = new Query();

        $query->select('id')->distinct()->from('example')->limit(10)->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->distinct()->from('example')->limit(10);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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
        $query = new Query();

        $query->select('id')->distinct()->from('example')->offset(5);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

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

    public function testBuildOrderByAndLimitWithDistinctZeroLimit(): void
    {
        $query = new Query();

        $query->select('id')->distinct()->from('example')->limit(0);

        [$actualQuerySql, $actualQueryParams] = $this->getQueryBuilder()->build($query);

        self::assertSame(
            <<<SQL
            SELECT * FROM (SELECT DISTINCT [id] FROM [example]) sub WHERE 1=0
            SQL,
            $actualQuerySql,
            "DISTINCT with LIMIT '0' must wrap with WHERE 1=0 to return zero rows.",
        );
        self::assertEmpty(
            $actualQueryParams,
            "DISTINCT with LIMIT '0' should have no bound parameters.",
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

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->addCommentOnTable($table, $comment),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'addCommentOnColumn')]
    public function testAddCommentOnColumn(string $table, string $column, string $comment, string $expected): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->addCommentOnColumn($table, $column, $comment),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropCommentFromTable')]
    public function testDropCommentFromTable(string $table, string $expected): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->dropCommentFromTable($table),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropCommentFromColumn')]
    public function testDropCommentFromColumn(string $table, string $column, string $expected): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->dropCommentFromColumn($table, $column),
            'Generated extended-property SQL must match the expected statement.',
        );
    }

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), []);
    }

    public static function batchInsertProvider(): array
    {
        $data = parent::batchInsertProvider();

        $data['escape-danger-chars'][3] = "INSERT INTO [customer] ([address]) VALUES ('SQL-danger chars are escaped: ''); --')";
        $data['bool-false, bool2-null'][3] = 'INSERT INTO [type] ([bool_col], [bool_col2]) VALUES (0, NULL)';
        $data['bool-false, time-now()'][3] = 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (0, now())';

        return $data;
    }

    public static function insertProvider(): array
    {
        return [
            'regular-values' => [
                'customer',
                [
                    'email' => 'test@example.com',
                    'name' => 'silverfire',
                    'address' => 'Kyiv {{city}}, Ukraine',
                    'is_active' => false,
                    'related_id' => null,
                ],
                [],
                'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, :qp1, :qp2, :qp3, :qp4);' .
                    'SELECT * FROM @temporary_inserted',
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'silverfire',
                    ':qp2' => 'Kyiv {{city}}, Ukraine',
                    ':qp3' => false,
                    ':qp4' => null,
                ],
            ],
            'params-and-expressions' => [
                '{{%type}}',
                [
                    '{{%type}}.[[related_id]]' => null,
                    '[[time]]' => new Expression('now()'),
                ],
                [],
                'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([int_col] int , [int_col2] int NULL, [tinyint_col] tinyint NULL, [smallint_col] smallint NULL, [char_col] char(100) , [char_col2] varchar(100) NULL, [char_col3] text NULL, [float_col] decimal(4,3) , [float_col2] float NULL, [blob_col] varbinary(max) NULL, [numeric_col] decimal(5,2) NULL, [time] datetime , [bool_col] tinyint , [bool_col2] tinyint NULL);' .
                'INSERT INTO {{%type}} ({{%type}}.[[related_id]], [[time]]) OUTPUT INSERTED.[int_col],INSERTED.[int_col2],INSERTED.[tinyint_col],INSERTED.[smallint_col],INSERTED.[char_col],INSERTED.[char_col2],INSERTED.[char_col3],INSERTED.[float_col],INSERTED.[float_col2],INSERTED.[blob_col],INSERTED.[numeric_col],INSERTED.[time],INSERTED.[bool_col],INSERTED.[bool_col2] INTO @temporary_inserted VALUES (:qp0, now());' .
                'SELECT * FROM @temporary_inserted',
                [
                    ':qp0' => null,
                ],
                false,
            ],
            'carry passed params' => [
                'customer',
                [
                    'email' => 'test@example.com',
                    'name' => 'sergeymakinen',
                    'address' => '{{city}}',
                    'is_active' => false,
                    'related_id' => null,
                    'col' => new Expression('CONCAT(:phFoo, :phBar)', [':phFoo' => 'foo']),
                ],
                [':phBar' => 'bar'],
                'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id], [col]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp1, :qp2, :qp3, :qp4, :qp5, CONCAT(:phFoo, :phBar));' .
                    'SELECT * FROM @temporary_inserted',
                [
                    ':phBar' => 'bar',
                    ':qp1' => 'test@example.com',
                    ':qp2' => 'sergeymakinen',
                    ':qp3' => '{{city}}',
                    ':qp4' => false,
                    ':qp5' => null,
                    ':phFoo' => 'foo',
                ],
            ],
            'carry passed params (query)' => [
                'customer',
                (new Query())
                    ->select([
                        'email',
                        'name',
                        'address',
                        'is_active',
                        'related_id',
                    ])
                    ->from('customer')
                    ->where([
                        'email' => 'test@example.com',
                        'name' => 'sergeymakinen',
                        'address' => '{{city}}',
                        'is_active' => false,
                        'related_id' => null,
                        'col' => new Expression('CONCAT(:phFoo, :phBar)', [':phFoo' => 'foo']),
                    ]),
                [':phBar' => 'bar'],
                'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted SELECT [email], [name], [address], [is_active], [related_id] FROM [customer] WHERE ([email]=:qp1) AND ([name]=:qp2) AND ([address]=:qp3) AND ([is_active]=:qp4) AND ([related_id] IS NULL) AND ([col]=CONCAT(:phFoo, :phBar));' .
                    'SELECT * FROM @temporary_inserted',
                [
                    ':phBar' => 'bar',
                    ':qp1' => 'test@example.com',
                    ':qp2' => 'sergeymakinen',
                    ':qp3' => '{{city}}',
                    ':qp4' => false,
                    ':phFoo' => 'foo',
                ],
            ],
        ];
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

        $qb = $db->getQueryBuilder();

        $actualParams = [];

        $actualSql = $qb->upsert(
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

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->addDefaultValue($name, $table, $column, $value),
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

        $qb = $db->getQueryBuilder();

        if ($type instanceof Closure) {
            $type = $type($this->getDb());
        }

        self::assertSame(
            $expected,
            $qb->alterColumn('foo1', 'bar', $type),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'alterColumnQualifiedTableNames')]
    public function testAlterColumnWithQualifiedTableName(string $table, string|Closure $type, string $expected): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        if ($type instanceof Closure) {
            $type = $type($this->getDb());
        }

        self::assertSame(
            $expected,
            $qb->alterColumn($table, 'bar', $type),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropColumn')]
    public function testDropColumn(string $table, string $column, string $expected): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        self::assertSame(
            $expected,
            $qb->dropColumn($table, $column),
            'Generated SQL must match the expected batch.',
        );
    }

    public function testDropColumnOnDb(): void
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(64)->defaultValue('')->check('LEN(bar) < 5')->unique());
        $connection->createCommand($sql)->execute();

        $sql = $connection->getQueryBuilder()->dropColumn('foo1', 'bar');
        $this->assertEquals(0, $connection->createCommand($sql)->execute());

        $schema = $connection->getTableSchema('[foo1]', true);
        $this->assertEquals(null, $schema->getColumn('bar'));
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'dropColumnConstraintsOnDb')]
    public function testDropColumnDropsConstraintOnDb(
        array $tableNames,
        array $createTablesSql,
        string $table,
        string $column,
    ): void {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        $this->dropTablesIfExist($db, $tableNames);

        foreach ($createTablesSql as $sql) {
            $db->createCommand($sql)->execute();
        }

        $this->assertDropColumnOnDb($db, $table, $column);

        $this->dropTablesIfExist($db, $tableNames);
    }

    private function assertDropColumnOnDb(Connection $db, string $table, string $column): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        $sql = $qb->dropColumn($table, $column);

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

    private function dropTablesIfExist(Connection $connection, array $tables): void
    {
        foreach ($tables as $table) {
            $quotedTable = $connection->quoteTableName($table);

            $connection->createCommand(
                <<<SQL
                IF OBJECT_ID(N'{$quotedTable}', N'U') IS NOT NULL DROP TABLE {$quotedTable}
                SQL,
            )->execute();
        }
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

    public static function buildFromDataProvider(): array
    {
        $data = parent::buildFromDataProvider();
        $data[] = ['[test]', '[[test]]'];
        $data[] = ['[test] [t1]', '[[test]] [[t1]]'];
        $data[] = ['[table.name]', '[[table.name]]'];
        $data[] = ['[table.name.with.dots]', '[[table.name.with.dots]]'];
        $data[] = ['[table name]', '[[table name]]'];
        $data[] = ['[table name with spaces]', '[[table name with spaces]]'];

        return $data;
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'renameTable')]
    public function testRenameTable(string $oldName, string $newName, string $expectedSql): void
    {
        $qb = $this->getQueryBuilder();

        self::assertSame(
            $expectedSql,
            $qb->renameTable($oldName, $newName),
            'Generated SQL must match the expected batch.',
        );
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'renameColumn')]
    public function testRenameColumn(string $table, string $oldName, string $newName, string $expectedSql): void
    {
        $qb = $this->getQueryBuilder();

        self::assertSame(
            $expectedSql,
            $qb->renameColumn($table, $oldName, $newName),
            'Generated SQL must match the expected batch.',
        );
    }

    public function testSelectExists(): void
    {
        $qb = $this->getQueryBuilder(false, false);

        self::assertSame(
            <<<SQL
            SELECT CASE WHEN EXISTS(SELECT 1 FROM [customer]) THEN 1 ELSE 0 END AS [result]
            SQL,
            $qb->selectExists(
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
            $qb->selectExists(
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
        $qb = $this->getQueryBuilder(false, false);

        self::assertSame(
            $expectedSql,
            $qb->checkIntegrity($check, $schema, $table),
            'Generated SQL must match the expected statement.',
        );
    }

    public function testResetSequenceThrowsExceptionForNonExistentTable(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Table not found: non_existent_table');
        $qb->resetSequence('non_existent_table');
    }

    public function testResetSequenceThrowsExceptionForTableWithoutSequence(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage("There is not sequence associated with table 'order_item'.");
        $qb->resetSequence('order_item');
    }

    public function testUpdateWithVarbinaryData(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $params = [];
        $sql = $qb->update('T_upsert_varbinary', ['blob_col' => 'test data'], ['id' => 1], $params);
        $this->assertStringContainsString('CONVERT(VARBINARY(MAX), 0x' . bin2hex('test data') . ')', $sql);
        $this->assertSame([':qp0' => 1], $params);
    }

    public function testCompositeInWithSubqueryThrowsException(): void
    {
        $db = $this->getConnection(false, false);

        $qb = $db->getQueryBuilder();

        $params = [];

        $condition = ['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')];

        $this->expectException('yii\base\NotSupportedException');

        $qb->buildCondition($condition, $params);
    }
}

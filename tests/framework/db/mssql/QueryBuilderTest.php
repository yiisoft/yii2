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

    protected function getCommmentsFromTable($table)
    {
        $db = $this->getConnection(false, false);
        $sql = "SELECT *
            FROM fn_listextendedproperty (
                N'MS_description',
                'SCHEMA', N'dbo',
                'TABLE', N" . $db->quoteValue($table) . ',
                DEFAULT, DEFAULT
        )';
        return $db->createCommand($sql)->queryAll();
    }

    protected function getCommentsFromColumn($table, $column)
    {
        $db = $this->getConnection(false, false);
        $sql = "SELECT *
            FROM fn_listextendedproperty (
                N'MS_description',
                'SCHEMA', N'dbo',
                'TABLE', N" . $db->quoteValue($table) . ",
                'COLUMN', N" . $db->quoteValue($column) . '
        )';
        return $db->createCommand($sql)->queryAll();
    }

    protected function runAddCommentOnTable($comment, $table)
    {
        $qb = $this->getQueryBuilder();
        $db = $this->getConnection(false, false);
        $sql = $qb->addCommentOnTable($table, $comment);
        return $db->createCommand($sql)->execute();
    }

    protected function runAddCommentOnColumn($comment, $table, $column)
    {
        $qb = $this->getQueryBuilder();
        $db = $this->getConnection(false, false);
        $sql = $qb->addCommentOnColumn($table, $column, $comment);
        return $db->createCommand($sql)->execute();
    }

    protected function runDropCommentFromTable($table)
    {
        $qb = $this->getQueryBuilder();
        $db = $this->getConnection(false, false);
        $sql = $qb->dropCommentFromTable($table);
        return $db->createCommand($sql)->execute();
    }

    protected function runDropCommentFromColumn($table, $column)
    {
        $qb = $this->getQueryBuilder();
        $db = $this->getConnection(false, false);
        $sql = $qb->dropCommentFromColumn($table, $column);
        return $db->createCommand($sql)->execute();
    }

    public function testCommentAdditionOnTableAndOnColumn(): void
    {
        $table = 'profile';
        $tableComment = 'A comment for profile table.';
        $this->runAddCommentOnTable($tableComment, $table);
        $resultTable = $this->getCommmentsFromTable($table);
        $this->assertEquals([
            'objtype' => 'TABLE',
            'objname' => $table,
            'name' => 'MS_description',
            'value' => $tableComment,
        ], $resultTable[0]);

        $column = 'description';
        $columnComment = 'A comment for description column in profile table.';
        $this->runAddCommentOnColumn($columnComment, $table, $column);
        $resultColumn = $this->getCommentsFromColumn($table, $column);
        $this->assertEquals([
            'objtype' => 'COLUMN',
            'objname' => $column,
            'name' => 'MS_description',
            'value' => $columnComment,
        ], $resultColumn[0]);

        // Add another comment to the same table to test update
        $tableComment2 = 'Another comment for profile table.';
        $this->runAddCommentOnTable($tableComment2, $table);
        $result = $this->getCommmentsFromTable($table);
        $this->assertEquals([
            'objtype' => 'TABLE',
            'objname' => $table,
            'name' => 'MS_description',
            'value' => $tableComment2,
        ], $result[0]);

        // Add another comment to the same column to test update
        $columnComment2 = 'Another comment for description column in profile table.';
        $this->runAddCommentOnColumn($columnComment2, $table, $column);
        $result = $this->getCommentsFromColumn($table, $column);
        $this->assertEquals([
            'objtype' => 'COLUMN',
            'objname' => $column,
            'name' => 'MS_description',
            'value' => $columnComment2,
        ], $result[0]);
    }

    public function testCommentAdditionOnQuotedTableOrColumn(): void
    {
        $table = 'stranger \'table';
        $tableComment = 'A comment for stranger \'table.';
        $this->runAddCommentOnTable($tableComment, $table);
        $resultTable = $this->getCommmentsFromTable($table);
        $this->assertEquals([
            'objtype' => 'TABLE',
            'objname' => $table,
            'name' => 'MS_description',
            'value' => $tableComment,
        ], $resultTable[0]);

        $column = 'stranger \'field';
        $columnComment = 'A comment for stranger \'field column in stranger \'table.';
        $this->runAddCommentOnColumn($columnComment, $table, $column);
        $resultColumn = $this->getCommentsFromColumn($table, $column);
        $this->assertEquals([
            'objtype' => 'COLUMN',
            'objname' => $column,
            'name' => 'MS_description',
            'value' => $columnComment,
        ], $resultColumn[0]);
    }

    public function testCommentRemovalFromTableAndFromColumn(): void
    {
        $table = 'profile';
        $tableComment = 'A comment for profile table.';
        $this->runAddCommentOnTable($tableComment, $table);
        $this->runDropCommentFromTable($table);
        $result = $this->getCommmentsFromTable($table);
        $this->assertEquals([], $result);

        $column = 'description';
        $columnComment = 'A comment for description column in profile table.';
        $this->runAddCommentOnColumn($columnComment, $table, $column);
        $this->runDropCommentFromColumn($table, $column);
        $result = $this->getCommentsFromColumn($table, $column);
        $this->assertEquals([], $result);
    }

    public function testCommentRemovalFromQuotedTableOrColumn(): void
    {
        $table = 'stranger \'table';
        $tableComment = 'A comment for stranger \'table.';
        $this->runAddCommentOnTable($tableComment, $table);
        $this->runDropCommentFromTable($table);
        $result = $this->getCommmentsFromTable($table);
        $this->assertEquals([], $result);

        $column = 'stranger \'field';
        $columnComment = 'A comment for stranger \'field in stranger \'table.';
        $this->runAddCommentOnColumn($columnComment, $table, $column);
        $this->runDropCommentFromColumn($table, $column);
        $result = $this->getCommentsFromColumn($table, $column);
        $this->assertEquals([], $result);
    }

    public function testCommentColumn(): void
    {
        $this->markTestSkipped('Testing the behavior, not sql generation anymore.');
    }

    public function testCommentTable(): void
    {
        $this->markTestSkipped('Testing the behavior, not sql generation anymore.');
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

    public function testResetSequence(): void
    {
        $qb = $this->getQueryBuilder();

        $expected = "DBCC CHECKIDENT ('[item]', RESEED, 5)";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "DBCC CHECKIDENT ('[item]', RESEED, 4)";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public static function upsertProvider(): array
    {
        $concreteData = [
            'regular values' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=[EXCLUDED].[address], [status]=[EXCLUDED].[status], [profile_id]=[EXCLUDED].[profile_id] WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);',
            ],
            'regular values with update part' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=:qp4, [status]=:qp5, [orders]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);',
            ],
            'regular values without update part' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);',
            ],
            'query' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [status]=[EXCLUDED].[status] WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);',
            ],
            'query with update part' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=:qp1, [status]=:qp2, [orders]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);',
            ],
            'query without update part' => [
                3 => 'MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);',
            ],
            'values and expressions' => [
                3 => 'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);' .
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());' .
                    'SELECT * FROM @temporary_inserted',
            ],
            'values and expressions with update part' => [
                3 => 'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);' .
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());' .
                    'SELECT * FROM @temporary_inserted',
            ],
            'values and expressions without update part' => [
                3 => 'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);' .
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());' .
                    'SELECT * FROM @temporary_inserted',
            ],
            'query, values and expressions with update part' => [
                3 => 'MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [ts]=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [ts]=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);',
            ],
            'no columns to update' => [
                3 => 'MERGE [T_upsert_1] WITH (HOLDLOCK) USING (VALUES (:qp0)) AS [EXCLUDED] ([a]) ON ([T_upsert_1].[a]=[EXCLUDED].[a]) WHEN NOT MATCHED THEN INSERT ([a]) VALUES ([EXCLUDED].[a]);',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }

    #[DataProviderExternal(QueryBuilderProvider::class, 'alterColumn')]
    public function testAlterColumn(string|Closure $type, string $expected): void
    {
        $qb = $this->getQueryBuilder(false);

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
        $qb = $this->getQueryBuilder(false);

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
        $qb = $this->getQueryBuilder(false);

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
        $db = $this->getConnection(false);

        $this->dropTablesIfExist($db, $tableNames);

        foreach ($createTablesSql as $sql) {
            $db->createCommand($sql)->execute();
        }

        $this->assertDropColumnOnDb($db, $table, $column);

        $this->dropTablesIfExist($db, $tableNames);
    }

    private function assertDropColumnOnDb(Connection $db, string $table, string $column): void
    {
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
        $qb = $this->getQueryBuilder();
        $sql = $qb->selectExists('SELECT 1 FROM [customer]');
        $this->assertSame('SELECT CASE WHEN EXISTS(SELECT 1 FROM [customer]) THEN 1 ELSE 0 END', $sql);
    }

    public function testCheckIntegrityEnableForTable(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $sql = $qb->checkIntegrity(true, '', 'customer');
        $this->assertSame('ALTER TABLE [dbo].[customer] CHECK CONSTRAINT ALL; ', $sql);
    }

    public function testCheckIntegrityDisableForTable(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $sql = $qb->checkIntegrity(false, '', 'customer');
        $this->assertSame('ALTER TABLE [dbo].[customer] NOCHECK CONSTRAINT ALL; ', $sql);
    }

    public function testCheckIntegrityFiltersOutViews(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $sql = $qb->checkIntegrity(true);
        $this->assertStringContainsString('CHECK CONSTRAINT ALL', $sql);
        $this->assertStringContainsString('[dbo].[customer]', $sql);
        $this->assertStringNotContainsString('animal_view', $sql);
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
        $qb = $this->getQueryBuilder();
        $params = [];
        $condition = ['in', ['id', 'name'], (new Query())->select(['id', 'name'])->from('users')];
        $this->expectException('yii\base\NotSupportedException');
        $qb->buildCondition($condition, $params);
    }

    public function testAddCommentOnNonExistentTableThrowsException(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Table not found: non_existent_table');
        $qb->addCommentOnColumn('non_existent_table', 'col', 'comment');
    }

    public function testDropCommentFromNonExistentTableThrowsException(): void
    {
        $qb = $this->getQueryBuilder(true, true);
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Table not found: non_existent_table');
        $qb->dropCommentFromColumn('non_existent_table', 'col');
    }
}

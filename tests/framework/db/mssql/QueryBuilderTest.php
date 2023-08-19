<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\Expression;
use yii\db\Query;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group mssql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'sqlsrv';

    protected $likeParameterReplacements = [
        '\%' => '[%]',
        '\_' => '[_]',
        '[' => '[[]',
        ']' => '[]]',
        '\\\\' => '[\\]',
    ];

    public function testOffsetLimit()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->limit(10)->offset(5);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testLimit()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->limit(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testOffset()
    {
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 10 ROWS';
        $expectedQueryParams = [];

        $query = new Query();
        $query->select('id')->from('example')->offset(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    protected function getCommmentsFromTable($table)
    {
        $db = $this->getConnection(false, false);
        $sql = "SELECT *
            FROM fn_listextendedproperty (
                N'MS_description',
                'SCHEMA', N'dbo',
                'TABLE', N" . $db->quoteValue($table) . ",
                DEFAULT, DEFAULT
        )";
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
                'COLUMN', N" . $db->quoteValue($column) . "
        )";
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

    public function testCommentAdditionOnTableAndOnColumn()
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

    public function testCommentAdditionOnQuotedTableOrColumn()
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

    public function testCommentRemovalFromTableAndFromColumn()
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

    public function testCommentRemovalFromQuotedTableOrColumn()
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

    public function testCommentColumn()
    {
        $this->markTestSkipped("Testing the behavior, not sql generation anymore.");
    }

    public function testCommentTable()
    {
        $this->markTestSkipped("Testing the behavior, not sql generation anymore.");
    }

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), []);
    }

    public function batchInsertProvider()
    {
        $data = parent::batchInsertProvider();

        $data['escape-danger-chars']['expected'] = "INSERT INTO [customer] ([address]) VALUES ('SQL-danger chars are escaped: ''); --')";
        $data['bool-false, bool2-null']['expected'] = 'INSERT INTO [type] ([bool_col], [bool_col2]) VALUES (0, NULL)';
        $data['bool-false, time-now()']['expected'] = 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (0, now())';

        return $data;
    }

    public function insertProvider()
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
                $this->replaceQuotes('SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, :qp1, :qp2, :qp3, :qp4);' .
                    'SELECT * FROM @temporary_inserted'),
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
                'SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([int_col] int , [int_col2] int NULL, [tinyint_col] tinyint NULL, [smallint_col] smallint NULL, [char_col] char(100) , [char_col2] varchar(100) NULL, [char_col3] text NULL, [float_col] decimal , [float_col2] float NULL, [blob_col] varbinary(MAX) NULL, [numeric_col] decimal NULL, [time] datetime , [bool_col] tinyint , [bool_col2] tinyint NULL);' .
                'INSERT INTO {{%type}} ({{%type}}.[[related_id]], [[time]]) OUTPUT INSERTED.[int_col],INSERTED.[int_col2],INSERTED.[tinyint_col],INSERTED.[smallint_col],INSERTED.[char_col],INSERTED.[char_col2],INSERTED.[char_col3],INSERTED.[float_col],INSERTED.[float_col2],INSERTED.[blob_col],INSERTED.[numeric_col],INSERTED.[time],INSERTED.[bool_col],INSERTED.[bool_col2] INTO @temporary_inserted VALUES (:qp0, now());' .
                'SELECT * FROM @temporary_inserted',
                [
                    ':qp0' => null,
                ],
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
                $this->replaceQuotes('SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id], [col]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp1, :qp2, :qp3, :qp4, :qp5, CONCAT(:phFoo, :phBar));' .
                    'SELECT * FROM @temporary_inserted'),
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
                $this->replaceQuotes('SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);' .
                    'INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted SELECT [email], [name], [address], [is_active], [related_id] FROM [customer] WHERE ([email]=:qp1) AND ([name]=:qp2) AND ([address]=:qp3) AND ([is_active]=:qp4) AND ([related_id] IS NULL) AND ([col]=CONCAT(:phFoo, :phBar));' .
                    'SELECT * FROM @temporary_inserted'),
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

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = "DBCC CHECKIDENT ('[item]', RESEED, (SELECT COALESCE(MAX([id]),0) FROM [item])+1)";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "DBCC CHECKIDENT ('[item]', RESEED, 4)";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function upsertProvider()
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

    public function conditionProvider()
    {
        $data = parent::conditionProvider();
        $data['composite in'] = [
            ['in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
            '(([id] = :qp0 AND [name] = :qp1))',
            [':qp0' => 1, ':qp1' => 'oy'],
        ];
        $data['composite in using array objects'] = [
            ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                ['id' => 1, 'name' => 'oy'],
                ['id' => 2, 'name' => 'yo'],
            ])],
            '(([id] = :qp0 AND [name] = :qp1) OR ([id] = :qp2 AND [name] = :qp3))',
            [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
        ];

        return $data;
    }

    public function testAlterColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] varchar(255)
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END";
        $sql = $qb->alterColumn('foo1', 'bar', 'varchar(255)');
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255) NOT NULL
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END";
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->notNull());
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [CK_foo1_bar] CHECK (LEN(bar) > 5)";
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->check('LEN(bar) > 5'));
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT '' FOR [bar]";
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->defaultValue(''));
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 'AbCdE' FOR [bar]";
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->defaultValue('AbCdE'));
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] datetime
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT CURRENT_TIMESTAMP FOR [bar]";
        $sql = $qb->alterColumn('foo1', 'bar', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->assertEquals($expected, $sql);

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(30)
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [UQ_foo1_bar] UNIQUE ([bar])";
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(30)->unique());
        $this->assertEquals($expected, $sql);
    }

    public function testAlterColumnOnDb()
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', 'varchar(255)');
        $connection->createCommand($sql)->execute();
        $schema = $connection->getTableSchema('[foo1]', true);

        $this->assertEquals("varchar(255)", $schema->getColumn('bar')->dbType);
        $this->assertEquals(true, $schema->getColumn('bar')->allowNull);

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(128)->notNull());
        $connection->createCommand($sql)->execute();
        $schema = $connection->getTableSchema('[foo1]', true);
        $this->assertEquals("nvarchar(128)", $schema->getColumn('bar')->dbType);
        $this->assertEquals(false, $schema->getColumn('bar')->allowNull);
    }

    public function testAlterColumnWithNull()
    {
        $qb = $this->getQueryBuilder();

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] int NULL
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT NULL FOR [bar]";
        $sql = $qb->alterColumn('foo1', 'bar', $this->integer()->null()->defaultValue(NULL));
        $this->assertEquals($expected, $sql);
    }

    public function testAlterColumnWithExpression()
    {
        $qb = $this->getQueryBuilder();

        $expected = "ALTER TABLE [foo1] ALTER COLUMN [bar] int NULL
DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
         WHERE so.[type]='D')
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT CAST(GETDATE() AS INT) FOR [bar]";
        $sql = $qb->alterColumn('foo1', 'bar', $this->integer()->null()->defaultValue(new Expression('CAST(GETDATE() AS INT)')));
        $this->assertEquals($expected, $sql);
    }

    public function testAlterColumnWithCheckConstraintOnDb()
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(128)->null()->check('LEN(bar) > 5'));
        $connection->createCommand($sql)->execute();
        $schema = $connection->getTableSchema('[foo1]', true);
        $this->assertEquals("nvarchar(128)", $schema->getColumn('bar')->dbType);
        $this->assertEquals(true, $schema->getColumn('bar')->allowNull);

        $sql = "INSERT INTO [foo1]([bar]) values('abcdef')";
        $this->assertEquals(1, $connection->createCommand($sql)->execute());
    }

    public function testAlterColumnWithCheckConstraintOnDbWithException()
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(64)->check('LEN(bar) > 5'));
        $connection->createCommand($sql)->execute();

        $sql = "INSERT INTO [foo1]([bar]) values('abcde')";
        $this->expectException('yii\db\IntegrityException');
        $this->assertEquals(1, $connection->createCommand($sql)->execute());
    }

    public function testAlterColumnWithUniqueConstraintOnDbWithException()
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(64)->unique());
        $connection->createCommand($sql)->execute();

        $sql = "INSERT INTO [foo1]([bar]) values('abcdef')";
        $this->assertEquals(1, $connection->createCommand($sql)->execute());

        $this->expectException('yii\db\IntegrityException');
        $this->assertEquals(1, $connection->createCommand($sql)->execute());
    }

    public function testDropColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "DECLARE @tableName VARCHAR(MAX) = '[foo1]'
DECLARE @columnName VARCHAR(MAX) = 'bar'

WHILE 1=1 BEGIN
    DECLARE @constraintName NVARCHAR(128)
    SET @constraintName = (SELECT TOP 1 OBJECT_NAME(cons.[object_id])
        FROM (
            SELECT sc.[constid] object_id
            FROM [sys].[sysconstraints] sc
            JOIN [sys].[columns] c ON c.[object_id]=sc.[id] AND c.[column_id]=sc.[colid] AND c.[name]=@columnName
            WHERE sc.[id] = OBJECT_ID(@tableName)
            UNION
            SELECT object_id(i.[name]) FROM [sys].[indexes] i
            JOIN [sys].[columns] c ON c.[object_id]=i.[object_id] AND c.[name]=@columnName
            JOIN [sys].[index_columns] ic ON ic.[object_id]=i.[object_id] AND i.[index_id]=ic.[index_id] AND c.[column_id]=ic.[column_id]
            WHERE i.[is_unique_constraint]=1 and i.[object_id]=OBJECT_ID(@tableName)
        ) cons
        JOIN [sys].[objects] so ON so.[object_id]=cons.[object_id]
        )
    IF @constraintName IS NULL BREAK
    EXEC (N'ALTER TABLE ' + @tableName + ' DROP CONSTRAINT [' + @constraintName + ']')
END
ALTER TABLE [foo1] DROP COLUMN [bar]";
        $sql = $qb->dropColumn('foo1', 'bar');
        $this->assertEquals($expected, $sql);
    }

    public function testDropColumnOnDb()
    {
        $connection = $this->getConnection();

        $sql = $connection->getQueryBuilder()->alterColumn('foo1', 'bar', $this->string(64)->defaultValue("")->check('LEN(bar) < 5')->unique());
        $connection->createCommand($sql)->execute();

        $sql = $connection->getQueryBuilder()->dropColumn('foo1', 'bar');
        $this->assertEquals(0, $connection->createCommand($sql)->execute());

        $schema = $connection->getTableSchema('[foo1]', true);
        $this->assertEquals(NULL, $schema->getColumn('bar'));
    }

    public function buildFromDataProvider()
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
}

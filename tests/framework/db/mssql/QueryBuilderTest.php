<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

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
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions with update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'values and expressions without update part' => [
                3 => 'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
            ],
            'query, values and expressions with update part' => [
                3 => 'MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [ts]=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [ts]=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);',
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

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\Query;

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

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "sp_updateextendedproperty @name = N'MS_Description', @value = 'This is my column.', @level1type = N'Table',  @level1name = comment, @level2type = N'Column', @level2name = text";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($expected, $sql);

        $expected = "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = comment, @level2type = N'Column', @level2name = text";
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($expected, $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "sp_updateextendedproperty @name = N'MS_Description', @value = 'This is my table.', @level1type = N'Table',  @level1name = comment";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($expected, $sql);

        $expected = "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = comment";
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($expected, $sql);
    }

    /**
     * this is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), []);
    }

    public function batchInsertProvider()
    {
        $data = parent::batchInsertProvider();

        $data['escape-danger-chars']['expected'] = 'INSERT INTO [customer] ([address]) VALUES ("SQL-danger chars are escaped: \'); --")';
        $data['bool-false, bool2-null']['expected'] = 'INSERT INTO [type] ([bool_col], [bool_col2]) VALUES (FALSE, NULL)';
        $data['bool-false, time-now()']['expected'] = 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (FALSE, now())';

        return $data;
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = "DBCC CHECKIDENT ('[item]', RESEED, (SELECT COALESCE(MAX([id]),0) FROM [item])+1)";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "DBCC CHECKIDENT ('[item], RESEED, 4)";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }
}

<?php

namespace yiiunit\framework\db\mssql;

<<<<<<< HEAD
=======
use yii\db\mssql\Schema;
>>>>>>> master
use yiiunit\framework\db\QueryBuilderTest;
use yii\db\Query;

/**
 * @group db
 * @group mssql
 */
class MssqlQueryBuilderTest extends QueryBuilderTest
{
    public $driverName = 'sqlsrv';

    public function testOffsetLimit()
    {
<<<<<<< HEAD
        $expectedQuerySql = 'SELECT `id` FROM `exapmle` OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = null;
=======
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 5 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];
>>>>>>> master

        $query = new Query();
        $query->select('id')->from('example')->limit(10)->offset(5);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testLimit()
    {
<<<<<<< HEAD
        $expectedQuerySql = 'SELECT `id` FROM `exapmle` OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = null;
=======
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY';
        $expectedQueryParams = [];
>>>>>>> master

        $query = new Query();
        $query->select('id')->from('example')->limit(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }

    public function testOffset()
    {
<<<<<<< HEAD
        $expectedQuerySql = 'SELECT `id` FROM `exapmle` OFFSET 10 ROWS';
        $expectedQueryParams = null;
=======
        $expectedQuerySql = 'SELECT [id] FROM [example] ORDER BY (SELECT NULL) OFFSET 10 ROWS';
        $expectedQueryParams = [];
>>>>>>> master

        $query = new Query();
        $query->select('id')->from('example')->offset(10);

        list($actualQuerySql, $actualQueryParams) = $this->getQueryBuilder()->build($query);

        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals($expectedQueryParams, $actualQueryParams);
    }
<<<<<<< HEAD
=======

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
>>>>>>> master
}

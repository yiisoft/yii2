<?php

namespace yiiunit\framework\db\mssql;

use yii\db\Expression;
use yii\db\mssql\Schema;
use yii\db\Query;

/**
 * @group db
 * @group mssql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'sqlsrv';

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
        $data['bool-false, time-now()']['expected'] = "INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (FALSE, now())";

        return $data;
    }

    public function likeConditionProvider()
    {
        $conditions = [
            // simple like
            [ ['like', 'name', 'heyho%'], '[[name]] LIKE :qp0', [':qp0' => '%heyho[%]%'] ],
            [ ['not like', 'name', 'heyho%'], '[[name]] NOT LIKE :qp0', [':qp0' => '%heyho[%]%'] ],
            [ ['or like', 'name', 'heyho%'], '[[name]] LIKE :qp0', [':qp0' => '%heyho[%]%'] ],
            [ ['or not like', 'name', 'heyho%'], '[[name]] NOT LIKE :qp0', [':qp0' => '%heyho[%]%'] ],

            // like for many values
            [ ['like', 'name', ['heyho%', '[abc]']], '[[name]] LIKE :qp0 AND [[name]] LIKE :qp1', [':qp0' => '%heyho[%]%', ':qp1' => '%[[]abc[]]%'] ],
            [ ['not like', 'name', ['heyho%', '[abc]']], '[[name]] NOT LIKE :qp0 AND [[name]] NOT LIKE :qp1', [':qp0' => '%heyho[%]%', ':qp1' => '%[[]abc[]]%'] ],
            [ ['or like', 'name', ['heyho%', '[abc]']], '[[name]] LIKE :qp0 OR [[name]] LIKE :qp1', [':qp0' => '%heyho[%]%', ':qp1' => '%[[]abc[]]%'] ],
            [ ['or not like', 'name', ['heyho%', '[abc]']], '[[name]] NOT LIKE :qp0 OR [[name]] NOT LIKE :qp1', [':qp0' => '%heyho[%]%', ':qp1' => '%[[]abc[]]%'] ],

            // like with Expression
            [ ['like', 'name', new Expression('CONCAT("test", colname, "%")')], '[[name]] LIKE CONCAT("test", colname, "%")', [] ],
            [ ['not like', 'name', new Expression('CONCAT("test", colname, "%")')], '[[name]] NOT LIKE CONCAT("test", colname, "%")', [] ],
            [ ['or like', 'name', new Expression('CONCAT("test", colname, "%")')], '[[name]] LIKE CONCAT("test", colname, "%")', [] ],
            [ ['or not like', 'name', new Expression('CONCAT("test", colname, "%")')], '[[name]] NOT LIKE CONCAT("test", colname, "%")', [] ],
            [ ['like', 'name', [new Expression('CONCAT("test", colname, "%")'), 'ab_c']], '[[name]] LIKE CONCAT("test", colname, "%") AND [[name]] LIKE :qp0', [':qp0' => '%ab[_]c%'] ],
            [ ['not like', 'name', [new Expression('CONCAT("test", colname, "%")'), 'ab_c']], '[[name]] NOT LIKE CONCAT("test", colname, "%") AND [[name]] NOT LIKE :qp0', [':qp0' => '%ab[_]c%'] ],
            [ ['or like', 'name', [new Expression('CONCAT("test", colname, "%")'), 'ab_c']], '[[name]] LIKE CONCAT("test", colname, "%") OR [[name]] LIKE :qp0', [':qp0' => '%ab[_]c%'] ],
            [ ['or not like', 'name', [new Expression('CONCAT("test", colname, "%")'), 'ab_c']], '[[name]] NOT LIKE CONCAT("test", colname, "%") OR [[name]] NOT LIKE :qp0', [':qp0' => '%ab[_]c%'] ],
        ];

        // adjust dbms specific escaping
        foreach($conditions as $i => $condition) {
            $conditions[$i][1] = $this->replaceQuotes($condition[1]);
        }
        return $conditions;
    }

    /**
     * @dataProvider likeConditionProvider
     */
    public function testBuildLikeCondition($condition, $expected, $expectedParams)
    {
        $query = (new Query())->where($condition);
        list($sql, $params) = $this->getQueryBuilder()->build($query);
        $this->assertEquals('SELECT *' . (empty($expected) ? '' : ' WHERE ' . $this->replaceQuotes($expected)), $sql);
        $this->assertEquals($expectedParams, $params);
    }
}

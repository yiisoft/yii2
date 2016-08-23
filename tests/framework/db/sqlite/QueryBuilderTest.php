<?php

namespace yiiunit\framework\db\sqlite;

use yii\db\Query;
use yii\db\Schema;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group sqlite
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    protected $driverName = 'sqlite';

    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_PK,
                $this->primaryKey()->first()->after('col_before'),
                'integer PRIMARY KEY AUTOINCREMENT NOT NULL'
            ],
        ]);
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            'composite in using array objects' => [
                ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                '(([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))',
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo']
            ],
            'composite in' => [
                ['in', ['id', 'name'], [['id' =>1, 'name' => 'oy']]],
                '(([[id]] = :qp0 AND [[name]] = :qp1))',
                [':qp0' => 1, ':qp1' => 'oy']
            ],
        ]);
    }

    public function testAddDropPrimaryKey()
    {
        $this->markTestSkipped('Comments are not supported in SQLite');
    }

    public function testCommentColumn()
    {
        $this->markTestSkipped('Comments are not supported in SQLite');
    }

    public function testCommentTable()
    {
        $this->markTestSkipped('Comments are not supported in SQLite');
    }

    public function testBatchInsert()
    {
        $db = $this->getConnection();
        if (version_compare($db->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '3.7.11', '>=')) {
            $this->markTestSkipped('This test is only relevant for SQLite < 3.7.11');
        }
        $sql = $this->getQueryBuilder()->batchInsert('{{customer}} t', ['t.id', 't.name'], [[1, 'a'], [2, 'b']]);
        $this->assertEquals("INSERT INTO {{customer}} t (`t`.`id`, `t`.`name`) SELECT 1, 'a' UNION SELECT 2, 'b'", $sql);
    }

    public function testRenameTable()
    {
        $sql = $this->getQueryBuilder()->renameTable('table_from', 'table_to');
        $this->assertEquals("ALTER TABLE `table_from` RENAME TO `table_to`", $sql);
    }

    /**
     * @inheritdoc
     */
    public function testBuildUnion()
    {
        $expectedQuerySql = $this->replaceQuotes(
            "SELECT `id` FROM `TotalExample` `t1` WHERE (w > 0) AND (x < 2) UNION  SELECT `id` FROM `TotalTotalExample` `t2` WHERE w > 5 UNION ALL  SELECT `id` FROM `TotalTotalExample` `t3` WHERE w = 3"
        );
        $query = new Query();
        $secondQuery = new Query();
        $secondQuery->select('id')
            ->from('TotalTotalExample t2')
            ->where('w > 5');
        $thirdQuery = new Query();
        $thirdQuery->select('id')
            ->from('TotalTotalExample t3')
            ->where('w = 3');
        $query->select('id')
            ->from('TotalExample t1')
            ->where(['and', 'w > 0', 'x < 2'])
            ->union($secondQuery)
            ->union($thirdQuery, TRUE);
        list($actualQuerySql, $queryParams) = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals([], $queryParams);
    }
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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

    protected $likeEscapeCharSql = " ESCAPE '\\'";

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

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            'composite in using array objects' => [
                ['in', new TraversableObject(['id', 'name']), new TraversableObject([
                    ['id' => 1, 'name' => 'oy'],
                    ['id' => 2, 'name' => 'yo'],
                ])],
                '(([[id]] = :qp0 AND [[name]] = :qp1) OR ([[id]] = :qp2 AND [[name]] = :qp3))',
                [':qp0' => 1, ':qp1' => 'oy', ':qp2' => 2, ':qp3' => 'yo'],
            ],
            'composite in' => [
                ['in', ['id', 'name'], [['id' => 1, 'name' => 'oy']]],
                '(([[id]] = :qp0 AND [[name]] = :qp1))',
                [':qp0' => 1, ':qp1' => 'oy'],
            ],
        ]);
    }

    public function primaryKeysProvider()
    {
        $this->markTestSkipped('Adding/dropping primary keys is not supported in SQLite.');
    }

    public function foreignKeysProvider()
    {
        $this->markTestSkipped('Adding/dropping foreign keys is not supported in SQLite.');
    }

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function uniquesProvider()
    {
        $this->markTestSkipped('Adding/dropping unique constraints is not supported in SQLite.');
    }

    public function checksProvider()
    {
        $this->markTestSkipped('Adding/dropping check constraints is not supported in SQLite.');
    }

    public function defaultValuesProvider()
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in SQLite.');
    }

    public function testCommentColumn()
    {
        $this->markTestSkipped('Comments are not supported in SQLite');
    }

    public function testCommentTable()
    {
        $this->markTestSkipped('Comments are not supported in SQLite');
    }

    public function batchInsertProvider()
    {
        $data = parent::batchInsertProvider();
        $data['escape-danger-chars']['expected'] = "INSERT INTO `customer` (`address`) VALUES ('SQL-danger chars are escaped: ''); --')";
        return $data;
    }

    public function testBatchInsertOnOlderVersions()
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
        $this->assertEquals('ALTER TABLE `table_from` RENAME TO `table_to`', $sql);
    }

    /**
     * {@inheritdoc}
     */
    public function testBuildUnion()
    {
        $expectedQuerySql = $this->replaceQuotes(
            'SELECT `id` FROM `TotalExample` `t1` WHERE (w > 0) AND (x < 2) UNION  SELECT `id` FROM `TotalTotalExample` `t2` WHERE w > 5 UNION ALL  SELECT `id` FROM `TotalTotalExample` `t3` WHERE w = 3'
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
            ->union($thirdQuery, true);
        [$actualQuerySql, $queryParams] = $this->getQueryBuilder()->build($query);
        $this->assertEquals($expectedQuerySql, $actualQuerySql);
        $this->assertEquals([], $queryParams);
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder(true, true);

        $expected = "UPDATE sqlite_sequence SET seq='5' WHERE name='item'";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "UPDATE sqlite_sequence SET seq='3' WHERE name='item'";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => 'WITH "EXCLUDED" (`email`, `address`, `status`, `profile_id`) AS (VALUES (:qp0, :qp1, :qp2, :qp3)) UPDATE `T_upsert` SET `address`=(SELECT `address` FROM `EXCLUDED`), `status`=(SELECT `status` FROM `EXCLUDED`), `profile_id`=(SELECT `profile_id` FROM `EXCLUDED`) WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3);',
            ],
            'regular values with update part' => [
                3 => 'WITH "EXCLUDED" (`email`, `address`, `status`, `profile_id`) AS (VALUES (:qp0, :qp1, :qp2, :qp3)) UPDATE `T_upsert` SET `address`=:qp4, `status`=:qp5, `orders`=T_upsert.orders + 1 WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3);',
            ],
            'regular values without update part' => [
                3 => 'INSERT OR IGNORE INTO `T_upsert` (`email`, `address`, `status`, `profile_id`) VALUES (:qp0, :qp1, :qp2, :qp3)',
            ],
            'query' => [
                3 => 'WITH "EXCLUDED" (`email`, `status`) AS (SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1) UPDATE `T_upsert` SET `status`=(SELECT `status` FROM `EXCLUDED`) WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1;',
            ],
            'query with update part' => [
                3 => 'WITH "EXCLUDED" (`email`, `status`) AS (SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1) UPDATE `T_upsert` SET `address`=:qp1, `status`=:qp2, `orders`=T_upsert.orders + 1 WHERE `T_upsert`.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1;',
            ],
            'query without update part' => [
                3 => 'INSERT OR IGNORE INTO `T_upsert` (`email`, `status`) SELECT `email`, 2 AS `status` FROM `customer` WHERE `name`=:qp0 LIMIT 1',
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
                3 => 'WITH "EXCLUDED" (`email`, [[time]]) AS (SELECT :phEmail AS `email`, now() AS [[time]]) UPDATE {{%T_upsert}} SET `ts`=:qp1, [[orders]]=T_upsert.orders + 1 WHERE {{%T_upsert}}.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]];',
            ],
            'query, values and expressions without update part' => [
                3 => 'WITH "EXCLUDED" (`email`, [[time]]) AS (SELECT :phEmail AS `email`, now() AS [[time]]) UPDATE {{%T_upsert}} SET `ts`=:qp1, [[orders]]=T_upsert.orders + 1 WHERE {{%T_upsert}}.`email`=(SELECT `email` FROM `EXCLUDED`); INSERT OR IGNORE INTO {{%T_upsert}} (`email`, [[time]]) SELECT :phEmail AS `email`, now() AS [[time]];',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }
}

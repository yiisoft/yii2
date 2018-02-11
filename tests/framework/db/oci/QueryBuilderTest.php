<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\oci\QueryBuilder;
use yii\db\oci\Schema;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group oci
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'oci';

    protected $likeEscapeCharSql = " ESCAPE '!'";
    protected $likeParameterReplacements = [
        '\%' => '!%',
        '\_' => '!_',
        '!' => '!!',
    ];

    /**
     * This is not used as a dataprovider for testGetColumnType to speed up the test
     * when used as dataprovider every single line will cause a reconnect with the database which is not needed here.
     */
    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' DEFAULT 1 NOT NULL',
                $this->boolean()->notNull()->defaultValue(1),
                'NUMBER(1) DEFAULT 1 NOT NULL',
            ],
        ]);
    }

    public function foreignKeysProvider()
    {
        $tableName = 'T_constraints_3';
        $name = 'CN_constraints_3';
        $pkTableName = 'T_constraints_2';
        return [
            'drop' => [
                "ALTER TABLE {{{$tableName}}} DROP CONSTRAINT [[$name]]",
                function (QueryBuilder $qb) use ($tableName, $name) {
                    return $qb->dropForeignKey($name, $tableName);
                },
            ],
            'add' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]]) ON DELETE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1', $pkTableName, 'C_id_1', 'CASCADE');
                },
            ],
            'add (2 columns)' => [
                "ALTER TABLE {{{$tableName}}} ADD CONSTRAINT [[$name]] FOREIGN KEY ([[C_fk_id_1]], [[C_fk_id_2]]) REFERENCES {{{$pkTableName}}} ([[C_id_1]], [[C_id_2]]) ON DELETE CASCADE",
                function (QueryBuilder $qb) use ($tableName, $name, $pkTableName) {
                    return $qb->addForeignKey($name, $tableName, 'C_fk_id_1, C_fk_id_2', $pkTableName, 'C_id_1, C_id_2', 'CASCADE');
                },
            ],
        ];
    }

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function testAddDropDefaultValue($sql, \Closure $builder)
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in Oracle.');
    }

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS ''";
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON TABLE [[comment]] IS 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON TABLE [[comment]] IS ''";
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'DROP SEQUENCE "item_SEQ";'
            . 'CREATE SEQUENCE "item_SEQ" START WITH 6 INCREMENT BY 1 NOMAXVALUE NOCACHE';
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP SEQUENCE "item_SEQ";'
            . 'CREATE SEQUENCE "item_SEQ" START WITH 4 INCREMENT BY 1 NOMAXVALUE NOCACHE';
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function likeConditionProvider()
    {
        /*
         * Different pdo_oci8 versions may or may not implement PDO::quote(), so
         * yii\db\Schema::quoteValue() may or may not quote \.
         */
        $encodedBackslash = substr($this->getDb()->quoteValue('\\'), 1, -1);
        $this->likeParameterReplacements[$encodedBackslash] = '\\';
        return parent::likeConditionProvider();
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            [
                ['in', 'id', range(0, 2500)],

                ' ('
                . '([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', 'id', range(0, 2500)],

                '('
                . '([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', 'id', new TraversableObject(range(0, 2500))],

                '('
                . '([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
        ]);
    }

    protected function generateSprintfSeries($pattern, $from, $to)
    {
        $items = [];
        for ($i = $from; $i <= $to; $i++) {
            $items[] = sprintf($pattern, $i);
        }

        return $items;
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "address"="EXCLUDED"."address", "status"="EXCLUDED"."status", "profile_id"="EXCLUDED"."profile_id" WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'query' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "status"="EXCLUDED"."status" WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
            ],
            'query with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
            ],
            'query without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON (("T_upsert"."email"="EXCLUDED"."email")) WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")',
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
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) "EXCLUDED" ON (({{%T_upsert}}."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) "EXCLUDED" ON (({{%T_upsert}}."email"="EXCLUDED"."email")) WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }
}

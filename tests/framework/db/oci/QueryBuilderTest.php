<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use Closure;
use Exception;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\oci\QueryBuilder;
use yii\db\oci\Schema;
use yii\db\Query;
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

    public static function foreignKeysProvider(): array
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

    public static function indexesProvider(): array
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function testCommentColumn(): void
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS ''";
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable(): void
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON TABLE [[comment]] IS 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = "COMMENT ON TABLE [[comment]] IS ''";
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testExecuteResetSequence(): void
    {
        $db = $this->getConnection();
        $qb = $this->getQueryBuilder();
        $sqlResult = "SELECT last_number FROM user_sequences WHERE sequence_name = 'item_SEQ'";

        $qb->executeResetSequence('item');
        $result = $db->createCommand($sqlResult)->queryScalar();
        $this->assertEquals(6, $result);

        $qb->executeResetSequence('item', 4);
        $result = $db->createCommand($sqlResult)->queryScalar();
        $this->assertEquals(4, $result);
    }

    public static function conditionProvider(): array
    {
        return array_merge(
            parent::conditionProvider(),
            [
                [
                    [
                        'in',
                        ['id', 'name'],
                        [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                    ],
                    '([[id]], [[name]]) IN ((:qp0, :qp1), (:qp2, :qp3))',
                    [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
                ],
                [
                    [
                        'not in',
                        ['id', 'name'],
                        [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                    ],
                    '([[id]], [[name]]) NOT IN ((:qp0, :qp1), (:qp2, :qp3))',
                    [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
                ],
                [
                    [
                        'not in',
                        [new Expression('id'), 'name'],
                        [['id' => 1, 'name' => 'foo'], ['id' => 2, 'name' => 'bar']],
                    ],
                    '([[id]], [[name]]) NOT IN ((:qp0, :qp1), (:qp2, :qp3))',
                    [':qp0' => 1, ':qp1' => 'foo', ':qp2' => 2, ':qp3' => 'bar'],
                ],
                [
                    [
                        'in',
                        ['id', 'name'],
                        (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1]),
                    ],
                    '([[id]], [[name]]) IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)',
                    [':qp0' => 1],
                ],
                [
                    [
                        'not in',
                        ['id', 'name'],
                        (new Query())->select(['id', 'name'])->from('users')->where(['active' => 1]),
                    ],
                    '([[id]], [[name]]) NOT IN (SELECT [[id]], [[name]] FROM [[users]] WHERE [[active]]=:qp0)',
                    [':qp0' => 1],
                ],
            ],
        );
    }

    public function conditionProvidertmp()
    {
        // test bc with commit
        // {@see https://github.com/yiisoft/yii2/commit/d16586334d7bea226a67aa8db28982848b5c92dd#diff-ae95e8cbf4e036860dd6b41011f9f8035a616a8f45d3c3167b3705d39879c95c}
        // should be fixed.
        return array_merge([], [
            [
                ['in', '[[id]]', range(0, 2500)],

                ' ('
                . '([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' OR ([[id]] IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', '[[id]]', range(0, 2500)],

                '('
                . '([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 0, 999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 1000, 1999)) . '))'
                . ' AND ([[id]] NOT IN (' . implode(', ', $this->generateSprintfSeries(':qp%d', 2000, 2500)) . '))'
                . ')',

                array_flip($this->generateSprintfSeries(':qp%d', 0, 2500)),
            ],
            [
                ['not in', '[[id]]', new TraversableObject(range(0, 2500))],

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

    public static function upsertProvider(): array
    {
        $concreteData = [
            'regular values' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"="EXCLUDED"."address", "status"="EXCLUDED"."status", "profile_id"="EXCLUDED"."profile_id" WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'regular values without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (SELECT :qp0 AS "email", :qp1 AS "address", :qp2 AS "status", :qp3 AS "profile_id" FROM "DUAL") "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN NOT MATCHED THEN INSERT ("email", "address", "status", "profile_id") VALUES ("EXCLUDED"."email", "EXCLUDED"."address", "EXCLUDED"."status", "EXCLUDED"."profile_id")',
            ],
            'query' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "status"="EXCLUDED"."status" WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")'
            ],
            'query with update part' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")'
            ],
            'query without update part' => [
                3 => 'MERGE INTO "T_upsert" USING (WITH USER_SQL AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0),
    PAGINATION AS (SELECT USER_SQL.*, rownum as rowNumId FROM USER_SQL)
SELECT *
FROM PAGINATION
WHERE rownum <= 1) "EXCLUDED" ON ("T_upsert"."email"="EXCLUDED"."email") WHEN NOT MATCHED THEN INSERT ("email", "status") VALUES ("EXCLUDED"."email", "EXCLUDED"."status")'
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
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) "EXCLUDED" ON ({{%T_upsert}}."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
            'query, values and expressions without update part' => [
                3 => 'MERGE INTO {{%T_upsert}} USING (SELECT :phEmail AS "email", now() AS [[time]]) "EXCLUDED" ON ({{%T_upsert}}."email"="EXCLUDED"."email") WHEN MATCHED THEN UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ("email", [[time]]) VALUES ("EXCLUDED"."email", "EXCLUDED".[[time]])',
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }

        // skip test
        unset($newData['no columns to update']);

        return $newData;
    }

    public static function batchInsertProvider(): array
    {
        $data = parent::batchInsertProvider();

        $data[0][3] = 'INSERT ALL  INTO "customer" ("email", "name", "address") ' .
            "VALUES ('test@example.com', 'silverfire', 'Kyiv {{city}}, Ukraine') SELECT 1 FROM SYS.DUAL";

        $data['escape-danger-chars']['expected'] = 'INSERT ALL  INTO "customer" ("address") ' .
            "VALUES ('SQL-danger chars are escaped: ''); --') SELECT 1 FROM SYS.DUAL";

        $data[2][3] = 'INSERT ALL  INTO "customer" () ' .
            "VALUES ('no columns passed') SELECT 1 FROM SYS.DUAL";

        $data['bool-false, bool2-null']['expected'] = 'INSERT ALL  INTO "type" ("bool_col", "bool_col2") ' .
            "VALUES ('', NULL) SELECT 1 FROM SYS.DUAL";

        $data[3][3] = 'INSERT ALL  INTO {{%type}} ({{%type}}.[[float_col]], [[time]]) ' .
            'VALUES (NULL, now()) SELECT 1 FROM SYS.DUAL';

        $data['bool-false, time-now()']['expected'] = 'INSERT ALL  INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) ' .
            'VALUES (0, now()) SELECT 1 FROM SYS.DUAL';

        return $data;
    }

    public static function batchUpdateProvider(): array
    {
        return [
            'sparse rows with expression' => [
                'customer',
                [
                    [
                        'id' => 1,
                        'status' => 1,
                        'name' => 'Tom',
                    ],
                    [
                        'id' => 2,
                        'status' => 0,
                    ],
                    [
                        'id' => 3,
                        'name' => new Expression('UPPER(name)'),
                    ],
                ],
                [],
                ['id'],
                '',
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_v0]], 1 AS [[_s0]], CAST(:qp2 AS VARCHAR2(128)) AS [[_v1]], 1 AS [[_s1]] FROM DUAL UNION ALL SELECT CAST(:qp3 AS NUMBER) AS [[_bk0]], CAST(:qp4 AS NUMBER) AS [[_v0]], 1 AS [[_s0]], CAST(NULL AS VARCHAR2(128)) AS [[_v1]], 0 AS [[_s1]] FROM DUAL UNION ALL SELECT CAST(:qp5 AS NUMBER) AS [[_bk0]], CAST(NULL AS NUMBER) AS [[_v0]], 0 AS [[_s0]], UPPER(name) AS [[_v1]], 1 AS [[_s1]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END, T.[[name]]=CASE WHEN S.[[_s1]]=1 THEN S.[[_v1]] ELSE T.[[name]] END',
                [
                    ':qp0' => 1,
                    ':qp1' => 1,
                    ':qp2' => 'Tom',
                    ':qp3' => 2,
                    ':qp4' => 0,
                    ':qp5' => 3,
                ],
            ],
            'null key value' => [
                'customer',
                [
                    [
                        'id' => null,
                        'status' => 1,
                    ],
                    [
                        'id' => 2,
                        'status' => 0,
                    ],
                ],
                [],
                ['id'],
                '',
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_v0]], 1 AS [[_s0]] FROM DUAL UNION ALL SELECT CAST(:qp2 AS NUMBER) AS [[_bk0]], CAST(:qp3 AS NUMBER) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END',
                [
                    ':qp0' => null,
                    ':qp1' => 1,
                    ':qp2' => 2,
                    ':qp3' => 0,
                ],
            ],
        ];
    }

    public function testBatchUpdateWithTraversableRow(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            new \ArrayObject([
                'id' => 1,
                'status' => 1,
            ]),
        ], [], ['id'], '', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $actualParams);
    }

    public function testBatchUpdateOnlyNullKeyValues(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            [
                'id' => null,
                'status' => 1,
            ],
        ], [], ['id'], '', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => null,
            ':qp1' => 1,
        ], $actualParams);
    }

    public function testBatchUpdateWithoutTableSchema(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('unknown_table', [
            [
                'id' => 'k1',
                'status' => 1,
            ],
        ], [], ['id'], '', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[unknown_table]] T USING (SELECT :qp0 AS [[_bk0]], :qp1 AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 'k1',
            ':qp1' => 1,
        ], $actualParams);
    }

    public function testBatchUpdateWithTableSchema(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder(true, true)->batchUpdate('type', [
            [
                'int_col' => '1',
                'float_col' => '2.5',
            ],
        ], [], ['int_col'], '', $actualParams);

        $this->assertStringStartsWith('MERGE INTO "type" T USING (SELECT CAST(:qp0 AS ', $actualSQL);
        $this->assertStringContainsString(') AS "_bk0", CAST(:qp1 AS ', $actualSQL);
        $this->assertStringContainsString(') AS "_v0", 1 AS "_s0" FROM DUAL) S ON ((T."int_col"=S."_bk0" OR (T."int_col" IS NULL AND S."_bk0" IS NULL))) WHEN MATCHED THEN UPDATE SET T."float_col"=CASE WHEN S."_s0"=1 THEN S."_v0" ELSE T."float_col" END', $actualSQL);
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 2.5,
        ], $actualParams);
    }

    public function testBatchUpdateCompositeKey(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            ['id' => 1, 'status' => 10, 'name' => 'Tom'],
            ['id' => 2, 'status' => 20, 'name' => 'Jerry'],
        ], [], ['id', 'status'], '', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_bk1]], CAST(:qp2 AS VARCHAR2(128)) AS [[_v0]], 1 AS [[_s0]] FROM DUAL UNION ALL SELECT CAST(:qp3 AS NUMBER) AS [[_bk0]], CAST(:qp4 AS NUMBER) AS [[_bk1]], CAST(:qp5 AS VARCHAR2(128)) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL)) AND (T.[[status]]=S.[[_bk1]] OR (T.[[status]] IS NULL AND S.[[_bk1]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[name]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[name]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 10,
            ':qp2' => 'Tom',
            ':qp3' => 2,
            ':qp4' => 20,
            ':qp5' => 'Jerry',
        ], $actualParams);
    }

    public function testBatchUpdateWithColumnsParameter(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            [1, 'active'],
            [2, 'inactive'],
        ], ['id', 'name'], ['id'], '', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS VARCHAR2(128)) AS [[_v0]], 1 AS [[_s0]] FROM DUAL UNION ALL SELECT CAST(:qp2 AS NUMBER) AS [[_bk0]], CAST(:qp3 AS VARCHAR2(128)) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL))) WHEN MATCHED THEN UPDATE SET T.[[name]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[name]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 'active',
            ':qp2' => 2,
            ':qp3' => 'inactive',
        ], $actualParams);
    }

    public function testBatchUpdateWithCondition(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            ['id' => 1, 'status' => 1],
        ], [], ['id'], 'active=1', $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS NUMBER) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL)) AND (active=1)) WHEN MATCHED THEN UPDATE SET T.[[status]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[status]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $actualParams);
    }

    public function testBatchUpdateWithConditionArray(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder()->batchUpdate('customer', [
            ['id' => 1, 'name' => 'Tom'],
        ], [], ['id'], ['status' => 1], $actualParams);

        $this->assertSame(
            $this->replaceQuotes(
                'MERGE INTO [[customer]] T USING (SELECT CAST(:qp0 AS NUMBER) AS [[_bk0]], CAST(:qp1 AS VARCHAR2(128)) AS [[_v0]], 1 AS [[_s0]] FROM DUAL) S ON ((T.[[id]]=S.[[_bk0]] OR (T.[[id]] IS NULL AND S.[[_bk0]] IS NULL)) AND ([[status]]=:qp2)) WHEN MATCHED THEN UPDATE SET T.[[name]]=CASE WHEN S.[[_s0]]=1 THEN S.[[_v0]] ELSE T.[[name]] END',
            ),
            $actualSQL,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 'Tom',
            ':qp2' => 1,
        ], $actualParams);
    }

    public function testBatchUpdateAutoDetectPrimaryKey(): void
    {
        $actualParams = [];
        $actualSQL = $this->getQueryBuilder(true, true)->batchUpdate('animal', [
            ['id' => 1, 'type' => 'cat'],
        ], [], [], '', $actualParams);

        $this->assertStringStartsWith('MERGE INTO "animal" T USING (SELECT CAST(:qp0 AS ', $actualSQL);
        $this->assertStringContainsString(') AS "_bk0", CAST(:qp1 AS ', $actualSQL);
        $this->assertStringContainsString(') AS "_v0", 1 AS "_s0" FROM DUAL) S ON ((T."id"=S."_bk0" OR (T."id" IS NULL AND S."_bk0" IS NULL))) WHEN MATCHED THEN UPDATE SET T."type"=CASE WHEN S."_s0"=1 THEN S."_v0" ELSE T."type" END', $actualSQL);
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 'cat',
        ], $actualParams);
    }

    /**
     * Dummy test to speed up QB's tests which rely on DB schema
     */
    public function testInitFixtures(): void
    {
        $this->assertInstanceOf('yii\db\QueryBuilder', $this->getQueryBuilder(true, true));
    }

    /**
     * @depends      testInitFixtures
     * @dataProvider upsertProvider
     * @param string $table
     * @param array $insertColumns
     * @param array|null $updateColumns
     * @param string|string[] $expectedSQL
     * @param array $expectedParams
     * @throws NotSupportedException
     * @throws Exception
     */
    public function testUpsert($table, $insertColumns, $updateColumns, $expectedSQL, $expectedParams): void
    {
        parent::testUpsert($table, $insertColumns, $updateColumns, $expectedSQL, $expectedParams);
    }

    /**
     * @dataProvider defaultValuesProvider
     * @param string $sql
     */
    public function testAddDropDefaultValue($sql, Closure $builder): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^oci does not support (adding|dropping) default value constraints\.$/',
        );

        parent::testAddDropDefaultValue($sql, $builder);
    }

    /**
     * @dataProvider likeConditionProvider
     * @param array $condition
     * @param string $expected
     * @param array $expectedParams
     */
    public function testBuildLikeCondition($condition, $expected, $expectedParams): void
    {
        /**
         * Different pdo_oci8 versions may or may not implement PDO::quote(), so
         * yii\db\Schema::quoteValue() may or may not quote \.
         */
        try {
            $encodedBackslash = substr($this->getDb()->quoteValue('\\\\'), 1, -1);
            $this->likeParameterReplacements[$encodedBackslash] = '\\';
        } catch (Exception $e) {
            $this->markTestSkipped('Could not execute Connection::quoteValue() method: ' . $e->getMessage());
        }

        parent::testBuildLikeCondition($condition, $expected, $expectedParams);
    }
}

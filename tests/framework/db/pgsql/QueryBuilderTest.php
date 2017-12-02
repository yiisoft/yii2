<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\db\Schema;

/**
 * @group db
 * @group pgsql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'pgsql';

    public function columnTypes()
    {
        return array_merge(parent::columnTypes(), [
            [
                Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT TRUE',
                $this->boolean()->notNull()->defaultValue(true),
                'boolean NOT NULL DEFAULT TRUE',
            ],
            [
                Schema::TYPE_CHAR . ' CHECK (value LIKE \'test%\')',
                $this->char()->check('value LIKE \'test%\''),
                'char(1) CHECK (value LIKE \'test%\')',
            ],
            [
                Schema::TYPE_CHAR . '(6) CHECK (value LIKE \'test%\')',
                $this->char(6)->check('value LIKE \'test%\''),
                'char(6) CHECK (value LIKE \'test%\')',
            ],
            [
                Schema::TYPE_CHAR . '(6)',
                $this->char(6)->unsigned(),
                'char(6)',
            ],
            [
                Schema::TYPE_INTEGER . '(8)',
                $this->integer(8)->unsigned(),
                'integer',
            ],
            [
                Schema::TYPE_TIMESTAMP . '(4)',
                $this->timestamp(4),
                'timestamp(4)',
            ],
        ]);
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            // adding conditions for ILIKE i.e. case insensitive LIKE
            // http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE

            // empty values
            [['ilike', 'name', []], '0=1', []],
            [['not ilike', 'name', []], '', []],
            [['or ilike', 'name', []], '0=1', []],
            [['or not ilike', 'name', []], '', []],

            // simple ilike
            [['ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%']],
            [['not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%']],
            [['or ilike', 'name', 'heyho'], '"name" ILIKE :qp0', [':qp0' => '%heyho%']],
            [['or not ilike', 'name', 'heyho'], '"name" NOT ILIKE :qp0', [':qp0' => '%heyho%']],

            // ilike for many values
            [['ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 AND "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 AND "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['or ilike', 'name', ['heyho', 'abc']], '"name" ILIKE :qp0 OR "name" ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
            [['or not ilike', 'name', ['heyho', 'abc']], '"name" NOT ILIKE :qp0 OR "name" NOT ILIKE :qp1', [':qp0' => '%heyho%', ':qp1' => '%abc%']],
        ]);
    }

    public function testAlterColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255)';
        $sql = $qb->alterColumn('foo1', 'bar', 'varchar(255)');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" SET NOT null';
        $sql = $qb->alterColumn('foo1', 'bar', 'SET NOT null');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" drop default';
        $sql = $qb->alterColumn('foo1', 'bar', 'drop default');
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" reset xyz';
        $sql = $qb->alterColumn('foo1', 'bar', 'reset xyz');
        $this->assertEquals($expected, $sql);
    }

    public function indexesProvider()
    {
        $result = parent::indexesProvider();
        $result['drop'][0] = 'DROP INDEX [[CN_constraints_2_single]]';
        return $result;
    }

    public function defaultValuesProvider()
    {
        $this->markTestSkipped('Adding/dropping default constraints is not supported in PostgreSQL.');
    }

    public function testCommentColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON COLUMN [[comment]].[[text]] IS 'This is my column.'";
        $sql = $qb->addCommentOnColumn('comment', 'text', 'This is my column.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = 'COMMENT ON COLUMN [[comment]].[[text]] IS NULL';
        $sql = $qb->dropCommentFromColumn('comment', 'text');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function testCommentTable()
    {
        $qb = $this->getQueryBuilder();

        $expected = "COMMENT ON TABLE [[comment]] IS 'This is my table.'";
        $sql = $qb->addCommentOnTable('comment', 'This is my table.');
        $this->assertEquals($this->replaceQuotes($expected), $sql);

        $expected = 'COMMENT ON TABLE [[comment]] IS NULL';
        $sql = $qb->dropCommentFromTable('comment');
        $this->assertEquals($this->replaceQuotes($expected), $sql);
    }

    public function batchInsertProvider()
    {
        $data = parent::batchInsertProvider();

        $data['escape-danger-chars']['expected'] = "INSERT INTO \"customer\" (\"address\") VALUES ('SQL-danger chars are escaped: ''); --')";
        $data['bool-false, bool2-null']['expected'] = 'INSERT INTO "type" ("bool_col", "bool_col2") VALUES (FALSE, NULL)';
        $data['bool-false, time-now()']['expected'] = 'INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (FALSE, now())';

        return $data;
    }

    public function testResetSequence()
    {
        $qb = $this->getQueryBuilder();

        $expected = "SELECT SETVAL('\"item_id_seq\"',(SELECT COALESCE(MAX(\"id\"),0) FROM \"item\")+1,false)";
        $sql = $qb->resetSequence('item');
        $this->assertEquals($expected, $sql);

        $expected = "SELECT SETVAL('\"item_id_seq\"',4,false)";
        $sql = $qb->resetSequence('item', 4);
        $this->assertEquals($expected, $sql);
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "address", "status", "profile_id") AS (VALUES (CAST(:qp0 AS varchar), CAST(:qp1 AS text), CAST(:qp2 AS int2), CAST(:qp3 AS int4))), "upsert" AS (UPDATE "T_upsert" SET "address"="EXCLUDED"."address", "status"="EXCLUDED"."status", "profile_id"="EXCLUDED"."profile_id" FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") SELECT "email", "address", "status", "profile_id" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
                4 => [
                    [
                        ':qp0' => 'test@example.com',
                        ':qp1' => 'bar {{city}}',
                        ':qp2' => 1,
                        ':qp3' => null,
                    ],
                ],
            ],
            'regular values with update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "address", "status", "profile_id") AS (VALUES (CAST(:qp0 AS varchar), CAST(:qp1 AS text), CAST(:qp2 AS int2), CAST(:qp3 AS int4))), "upsert" AS (UPDATE "T_upsert" SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") SELECT "email", "address", "status", "profile_id" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
                4 => [
                    [
                        ':qp0' => 'test@example.com',
                        ':qp1' => 'bar {{city}}',
                        ':qp2' => 1,
                        ':qp3' => null,
                        ':qp4' => 'foo {{city}}',
                        ':qp5' => 2,
                    ],
                ],
            ],
            'regular values without update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "address", "status", "profile_id") AS (VALUES (CAST(:qp0 AS varchar), CAST(:qp1 AS text), CAST(:qp2 AS int2), CAST(:qp3 AS int4))) INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") SELECT "email", "address", "status", "profile_id" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "T_upsert" WHERE (("T_upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
                4 => [
                    [
                        ':qp0' => 'test@example.com',
                        ':qp1' => 'bar {{city}}',
                        ':qp2' => 1,
                        ':qp3' => null,
                    ],
                ],
            ],
            'query' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "status") AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1), "upsert" AS (UPDATE "T_upsert" SET "status"="EXCLUDED"."status" FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "status") SELECT "email", "status" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
            ],
            'query with update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "status") AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1), "upsert" AS (UPDATE "T_upsert" SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "status") SELECT "email", "status" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
            ],
            'query without update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "status") AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1) INSERT INTO "T_upsert" ("email", "status") SELECT "email", "status" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "T_upsert" WHERE (("T_upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
            ],
            'values and expressions' => [
                3 => [
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
                    '',
                ],
            ],
            'values and expressions with update part' => [
                3 => [
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
                    '',
                ],
            ],
            'values and expressions without update part' => [
                3 => [
                    'INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())',
                    '',
                ],
            ],
            'query, values and expressions with update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", [[time]]) AS (SELECT :phEmail AS "email", now() AS [[time]]), "upsert" AS (UPDATE {{%T_upsert}} SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (({{%T_upsert}}."email"="EXCLUDED"."email")) RETURNING {{%T_upsert}}.*) INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT "email", [[time]] FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
            ],
            'query, values and expressions without update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", [[time]]) AS (SELECT :phEmail AS "email", now() AS [[time]]), "upsert" AS (UPDATE {{%T_upsert}} SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (({{%T_upsert}}."email"="EXCLUDED"."email")) RETURNING {{%T_upsert}}.*) INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT "email", [[time]] FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    '',
                ],
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }
}

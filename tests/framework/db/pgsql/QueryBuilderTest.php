<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use yii\base\DynamicModel;
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\Query;
use yii\db\Schema;
use yiiunit\data\base\TraversableObject;

/**
 * @group db
 * @group pgsql
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{
    public $driverName = 'pgsql';

    public function columnTypes()
    {
        $columns = [
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
            [
                Schema::TYPE_JSON,
                $this->json(),
                "jsonb",
            ],
        ];

        return array_merge(parent::columnTypes(), $columns);
    }

    public function conditionProvider()
    {
        return array_merge(parent::conditionProvider(), [
            // adding conditions for ILIKE i.e. case insensitive LIKE
            // https://www.postgresql.org/docs/8.3/functions-matching.html#FUNCTIONS-LIKE

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

            // array condition corner cases
            [['@>', 'id', new ArrayExpression([1])], '"id" @> ARRAY[:qp0]', [':qp0' => 1]],
            'scalar can not be converted to array #1' => [['@>', 'id', new ArrayExpression(1)], '"id" @> ARRAY[]', []],
            ['scalar can not be converted to array #2' => ['@>', 'id', new ArrayExpression(false)], '"id" @> ARRAY[]', []],
            [['&&', 'price', new ArrayExpression([12, 14], 'float')], '"price" && ARRAY[:qp0, :qp1]::float[]', [':qp0' => 12, ':qp1' => 14]],
            [['@>', 'id', new ArrayExpression([2, 3])], '"id" @> ARRAY[:qp0, :qp1]', [':qp0' => 2, ':qp1' => 3]],
            'array of arrays' => [['@>', 'id', new ArrayExpression([[1,2], [3,4]], 'float', 2)], '"id" @> ARRAY[ARRAY[:qp0, :qp1]::float[], ARRAY[:qp2, :qp3]::float[]\\]::float[][]', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3, ':qp3' => 4]],
            [['@>', 'id', new ArrayExpression([])], '"id" @> ARRAY[]', []],
            'array can contain nulls' => [['@>', 'id', new ArrayExpression([null])], '"id" @> ARRAY[:qp0]', [':qp0' => null]],
            'traversable objects are supported' => [['@>', 'id', new ArrayExpression(new TraversableObject([1, 2, 3]))], '[[id]] @> ARRAY[:qp0, :qp1, :qp2]', [':qp0' => 1, ':qp1' => 2, ':qp2' => 3]],
            [['@>', 'time', new ArrayExpression([new Expression('now()')])], '[[time]] @> ARRAY[now()]', []],
            [['@>', 'id', new ArrayExpression((new Query())->select('id')->from('users')->where(['active' => 1]))], '[[id]] @> ARRAY(SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)', [':qp0' => 1]],
            [['@>', 'id', new ArrayExpression([(new Query())->select('id')->from('users')->where(['active' => 1])], 'integer')], '[[id]] @> ARRAY[ARRAY(SELECT [[id]] FROM [[users]] WHERE [[active]]=:qp0)::integer[]]::integer[]', [':qp0' => 1]],

            // json conditions
            [['=', 'jsoncol', new JsonExpression(['lang' => 'uk', 'country' => 'UA'])], '[[jsoncol]] = :qp0', [':qp0' => '{"lang":"uk","country":"UA"}']],
            [['=', 'jsoncol', new JsonExpression([false])], '[[jsoncol]] = :qp0', [':qp0' => '[false]']],
            [['=', 'prices', new JsonExpression(['seeds' => 15, 'apples' => 25], 'jsonb')], '[[prices]] = :qp0::jsonb', [':qp0' => '{"seeds":15,"apples":25}']],
            'nested json' => [
                ['=', 'data', new JsonExpression(['user' => ['login' => 'silverfire', 'password' => 'c4ny0ur34d17?'], 'props' => ['mood' => 'good']])],
                '"data" = :qp0', [':qp0' => '{"user":{"login":"silverfire","password":"c4ny0ur34d17?"},"props":{"mood":"good"}}']
            ],
            'null value' => [['=', 'jsoncol', new JsonExpression(null)], '"jsoncol" = :qp0', [':qp0' => 'null']],
            'null as array value' => [['=', 'jsoncol', new JsonExpression([null])], '"jsoncol" = :qp0', [':qp0' => '[null]']],
            'null as object value' => [['=', 'jsoncol', new JsonExpression(['nil' => null])], '"jsoncol" = :qp0', [':qp0' => '{"nil":null}']],

            [['=', 'jsoncol', new JsonExpression(new DynamicModel(['a' => 1, 'b' => 2]))], '[[jsoncol]] = :qp0', [':qp0' => '{"a":1,"b":2}']],
            'query' => [['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]))], '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)', [':qp0' => 1]],
            'query with type' => [['=', 'jsoncol', new JsonExpression((new Query())->select('params')->from('user')->where(['id' => 1]), 'jsonb')], '[[jsoncol]] = (SELECT [[params]] FROM [[user]] WHERE [[id]]=:qp0)::jsonb', [':qp0' => 1]],

            'array of json expressions' => [
                ['=', 'colname', new ArrayExpression([new JsonExpression(['a' => null, 'b' => 123, 'c' => [4, 5]]), new JsonExpression([true])])],
                '"colname" = ARRAY[:qp0, :qp1]',
                [':qp0' => '{"a":null,"b":123,"c":[4,5]}', ':qp1' => '[true]']
            ],
            'Items in ArrayExpression of type json should be casted to Json' => [
                ['=', 'colname', new ArrayExpression([['a' => null, 'b' => 123, 'c' => [4, 5]], [true]], 'json')],
                '"colname" = ARRAY[:qp0, :qp1]::json[]',
                [':qp0' => '{"a":null,"b":123,"c":[4,5]}', ':qp1' => '[true]']
            ],
            'Two dimension array of text' => [
                ['=', 'colname', new ArrayExpression([['text1', 'text2'], ['text3', 'text4'], [null, 'text5']], 'text', 2)],
                '"colname" = ARRAY[ARRAY[:qp0, :qp1]::text[], ARRAY[:qp2, :qp3]::text[], ARRAY[:qp4, :qp5]::text[]]::text[][]',
                [':qp0' => 'text1', ':qp1' => 'text2', ':qp2' => 'text3', ':qp3' => 'text4', ':qp4' => null, ':qp5' => 'text5'],
            ],
            'Three dimension array of booleans' => [
                ['=', 'colname', new ArrayExpression([[[true], [false, null]], [[false], [true], [false]], [['t', 'f']]], 'bool', 3)],
                '"colname" = ARRAY[ARRAY[ARRAY[:qp0]::bool[], ARRAY[:qp1, :qp2]::bool[]]::bool[][], ARRAY[ARRAY[:qp3]::bool[], ARRAY[:qp4]::bool[], ARRAY[:qp5]::bool[]]::bool[][], ARRAY[ARRAY[:qp6, :qp7]::bool[]]::bool[][]]::bool[][][]',
                [':qp0' => true, ':qp1' => false, ':qp2' => null, ':qp3' => false, ':qp4' => true, ':qp5' => false, ':qp6' => 't', ':qp7' => 'f'],
            ],

            // Checks to verity that operators work correctly
            [['@>', 'id', new ArrayExpression([1])], '"id" @> ARRAY[:qp0]', [':qp0' => 1]],
            [['<@', 'id', new ArrayExpression([1])], '"id" <@ ARRAY[:qp0]', [':qp0' => 1]],
            [['=', 'id',  new ArrayExpression([1])], '"id" = ARRAY[:qp0]', [':qp0' => 1]],
            [['<>', 'id', new ArrayExpression([1])], '"id" <> ARRAY[:qp0]', [':qp0' => 1]],
            [['>', 'id',  new ArrayExpression([1])], '"id" > ARRAY[:qp0]', [':qp0' => 1]],
            [['<', 'id',  new ArrayExpression([1])], '"id" < ARRAY[:qp0]', [':qp0' => 1]],
            [['>=', 'id', new ArrayExpression([1])], '"id" >= ARRAY[:qp0]', [':qp0' => 1]],
            [['<=', 'id', new ArrayExpression([1])], '"id" <= ARRAY[:qp0]', [':qp0' => 1]],
            [['&&', 'id', new ArrayExpression([1])], '"id" && ARRAY[:qp0]', [':qp0' => 1]],
        ]);
    }

    public function testAlterColumn()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" DROP NOT NULL';
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

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" DROP NOT NULL';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255));
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" SET NOT NULL';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->notNull());
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" DROP NOT NULL, ADD CONSTRAINT foo1_bar_check CHECK (char_length(bar) > 5)';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->check('char_length(bar) > 5'));
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET DEFAULT \'\', ALTER COLUMN "bar" DROP NOT NULL';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->defaultValue(''));
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET DEFAULT \'AbCdE\', ALTER COLUMN "bar" DROP NOT NULL';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(255)->defaultValue('AbCdE'));
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE timestamp(0), ALTER COLUMN "bar" SET DEFAULT CURRENT_TIMESTAMP, ALTER COLUMN "bar" DROP NOT NULL';
        $sql = $qb->alterColumn('foo1', 'bar', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->assertEquals($expected, $sql);

        $expected = 'ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(30), ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" DROP NOT NULL, ADD UNIQUE ("bar")';
        $sql = $qb->alterColumn('foo1', 'bar', $this->string(30)->unique());
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

    public function testResetSequencePostgres12()
    {
        if (version_compare($this->getConnection(false)->getServerVersion(), '12.0', '<')) {
            $this->markTestSkipped('PostgreSQL < 12.0 does not support GENERATED AS IDENTITY columns.');
        }

        $config = $this->database;
        unset($config['fixture']);
        $this->prepareDatabase($config, realpath(__DIR__.'/../../../data') . '/postgres12.sql');

        $qb = $this->getQueryBuilder(false);

        $expected = "SELECT SETVAL('\"item_12_id_seq\"',(SELECT COALESCE(MAX(\"id\"),0) FROM \"item_12\")+1,false)";
        $sql = $qb->resetSequence('item_12');
        $this->assertEquals($expected, $sql);

        $expected = "SELECT SETVAL('\"item_12_id_seq\"',4,false)";
        $sql = $qb->resetSequence('item_12', 4);
        $this->assertEquals($expected, $sql);
    }

    public function upsertProvider()
    {
        $concreteData = [
            'regular values' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "address", "status", "profile_id") AS (VALUES (CAST(:qp0 AS varchar), CAST(:qp1 AS text), CAST(:qp2 AS int2), CAST(:qp3 AS int4))), "upsert" AS (UPDATE "T_upsert" SET "address"="EXCLUDED"."address", "status"="EXCLUDED"."status", "profile_id"="EXCLUDED"."profile_id" FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") SELECT "email", "address", "status", "profile_id" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    'INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT ("email") DO UPDATE SET "address"=EXCLUDED."address", "status"=EXCLUDED."status", "profile_id"=EXCLUDED."profile_id"',
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
                    'INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT ("email") DO UPDATE SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1',
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
                    'INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT DO NOTHING',
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
                    'INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1 ON CONFLICT ("email") DO UPDATE SET "status"=EXCLUDED."status"',
                ],
            ],
            'query with update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "status") AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1), "upsert" AS (UPDATE "T_upsert" SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (("T_upsert"."email"="EXCLUDED"."email")) RETURNING "T_upsert".*) INSERT INTO "T_upsert" ("email", "status") SELECT "email", "status" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    'INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1 ON CONFLICT ("email") DO UPDATE SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1',
                ],
            ],
            'query without update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", "status") AS (SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1) INSERT INTO "T_upsert" ("email", "status") SELECT "email", "status" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "T_upsert" WHERE (("T_upsert"."email"="EXCLUDED"."email")))',
                    'INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 LIMIT 1 ON CONFLICT DO NOTHING',
                ],
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
                3 => [
                    'WITH "EXCLUDED" ("email", [[time]]) AS (SELECT :phEmail AS "email", now() AS [[time]]), "upsert" AS (UPDATE {{%T_upsert}} SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (({{%T_upsert}}."email"="EXCLUDED"."email")) RETURNING {{%T_upsert}}.*) INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT "email", [[time]] FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    'INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT :phEmail AS "email", now() AS [[time]] ON CONFLICT ("email") DO UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1',
                ],
            ],
            'query, values and expressions without update part' => [
                3 => [
                    'WITH "EXCLUDED" ("email", [[time]]) AS (SELECT :phEmail AS "email", now() AS [[time]]), "upsert" AS (UPDATE {{%T_upsert}} SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1 FROM "EXCLUDED" WHERE (({{%T_upsert}}."email"="EXCLUDED"."email")) RETURNING {{%T_upsert}}.*) INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT "email", [[time]] FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "upsert" WHERE (("upsert"."email"="EXCLUDED"."email")))',
                    'INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT :phEmail AS "email", now() AS [[time]] ON CONFLICT ("email") DO UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1',
                ],
            ],
            'no columns to update' => [
                3 => [
                    'WITH "EXCLUDED" ("a") AS (VALUES (CAST(:qp0 AS int2))) INSERT INTO "T_upsert_1" ("a") SELECT "a" FROM "EXCLUDED" WHERE NOT EXISTS (SELECT 1 FROM "T_upsert_1" WHERE (("T_upsert_1"."a"="EXCLUDED"."a")))',
                    'INSERT INTO "T_upsert_1" ("a") VALUES (:qp0) ON CONFLICT DO NOTHING',
                ],
            ],
        ];
        $newData = parent::upsertProvider();
        foreach ($concreteData as $testName => $data) {
            $newData[$testName] = array_replace($newData[$testName], $data);
        }
        return $newData;
    }

    public function updateProvider()
    {
        $items = parent::updateProvider();

        $items[] = [
            'profile',
            [
                'description' => new JsonExpression(['abc' => 'def', 123, null]),
            ],
            [
                'id' => 1,
            ],
            $this->replaceQuotes('UPDATE [[profile]] SET [[description]]=:qp0 WHERE [[id]]=:qp1'),
            [
                ':qp0' => '{"abc":"def","0":123,"1":null}',
                ':qp1' => 1,
            ],
        ];

        return $items;
    }

    public function testDropIndex()
    {
        $qb = $this->getQueryBuilder();

        $expected = 'DROP INDEX "index"';
        $sql = $qb->dropIndex('index', '{{table}}');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP INDEX "schema"."index"';
        $sql = $qb->dropIndex('index', '{{schema.table}}');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP INDEX "schema"."index"';
        $sql = $qb->dropIndex('schema.index', '{{schema2.table}}');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP INDEX "schema"."index"';
        $sql = $qb->dropIndex('index', '{{schema.%table}}');
        $this->assertEquals($expected, $sql);

        $expected = 'DROP INDEX {{%schema.index}}';
        $sql = $qb->dropIndex('index', '{{%schema.table}}');
        $this->assertEquals($expected, $sql);
    }
}

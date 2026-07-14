<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

use Closure;
use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\pgsql\QueryBuilderTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderProvider
{
    /**
     * @return array<
     *   string,
     *   array{string, array<string, mixed>|Query, array<string, mixed>|bool, string, array<string, mixed>}
     * >
     */
    public static function upsert(): array
    {
        return [
            'composite unique constraint used whole' => [
                'T_upsert_3',
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 'c',
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert_3" ("a", "b", "c") VALUES (:qp0, :qp1, :qp2) ON CONFLICT ("a", "b") DO UPDATE SET "c"=EXCLUDED."c"
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 2,
                    ':qp2' => 'c',
                ],
            ],
            'composite unique constraint via declared table constraint' => [
                'T_constraints_2',
                [
                    'C_index_1' => 1,
                    'C_index_2_1' => 2,
                    'C_index_2_2' => 3,
                ],
                true,
                <<<SQL
                INSERT INTO "T_constraints_2" ("C_index_1", "C_index_2_1", "C_index_2_2") VALUES (:qp0, :qp1, :qp2) ON CONFLICT ("C_index_2_1", "C_index_2_2") DO UPDATE SET "C_index_1"=EXCLUDED."C_index_1"
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 2,
                    ':qp2' => 3,
                ],
            ],
            'multiple matching constraints without update part' => [
                'T_upsert',
                [
                    'id' => 1,
                    'email' => 'test@example.com',
                ],
                false,
                <<<SQL
                INSERT INTO "T_upsert" ("id", "email") VALUES (:qp0, :qp1) ON CONFLICT DO NOTHING
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 'test@example.com',
                ],
            ],
            'narrower constraint selected before name order' => [
                'T_upsert_3',
                [
                    'a' => 1,
                    'b' => 2,
                    'z' => 3,
                    'c' => 'c',
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert_3" ("a", "b", "z", "c") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT ("z") DO UPDATE SET "c"=EXCLUDED."c"
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 2,
                    ':qp2' => 3,
                    ':qp3' => 'c',
                ],
            ],
            'no columns to update' => [
                'T_upsert_1',
                [
                    'a' => 1,
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert_1" ("a") VALUES (:qp0) ON CONFLICT DO NOTHING
                SQL,
                [
                    ':qp0' => 1,
                ],
            ],
            'primary key arbiter preferred over unique constraint' => [
                'T_upsert',
                [
                    'id' => 1,
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert" ("id", "email", "address") VALUES (:qp0, :qp1, :qp2) ON CONFLICT ("id") DO UPDATE SET "address"=EXCLUDED."address"
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 'test@example.com',
                    ':qp2' => 'bar {{city}}',
                ],
            ],
            'query' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                true,
                <<<SQL
                INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 FETCH NEXT 1 ROWS ONLY ON CONFLICT ("email") DO UPDATE SET "status"=EXCLUDED."status"
                SQL,
                [
                    ':qp0' => 'user1',
                ],
            ],
            'query values and expressions with update part' => [
                '{{%T_upsert}}',
                (new Query())
                    ->select(
                        [
                            'email' => new Expression(':phEmail', [':phEmail' => 'dynamic@example.com']),
                            '[[time]]' => new Expression('now()'),
                        ],
                    ),
                [
                    'ts' => 0,
                    '[[orders]]' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT :phEmail AS "email", now() AS [[time]] ON CONFLICT ("email") DO UPDATE SET "ts"=:qp1, [[orders]]=T_upsert.orders + 1
                SQL,
                [
                    ':phEmail' => 'dynamic@example.com',
                    ':qp1' => 0,
                ],
            ],
            'query values and expressions without update part' => [
                '{{%T_upsert}}',
                (new Query())
                    ->select(
                        [
                            'email' => new Expression(':phEmail', [':phEmail' => 'dynamic@example.com']),
                            '[[time]]' => new Expression('now()'),
                        ],
                    ),
                false,
                <<<SQL
                INSERT INTO {{%T_upsert}} ("email", [[time]]) SELECT :phEmail AS "email", now() AS [[time]] ON CONFLICT DO NOTHING
                SQL,
                [
                    ':phEmail' => 'dynamic@example.com',
                ],
            ],
            'query with update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                [
                    'address' => 'foo {{city}}',
                    'status' => 2,
                    'orders' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 FETCH NEXT 1 ROWS ONLY ON CONFLICT ("email") DO UPDATE SET "address"=:qp1, "status"=:qp2, "orders"=T_upsert.orders + 1
                SQL,
                [
                    ':qp0' => 'user1',
                    ':qp1' => 'foo {{city}}',
                    ':qp2' => 2,
                ],
            ],
            'query without update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                false,
                <<<SQL
                INSERT INTO "T_upsert" ("email", "status") SELECT "email", 2 AS "status" FROM "customer" WHERE "name"=:qp0 FETCH NEXT 1 ROWS ONLY ON CONFLICT DO NOTHING
                SQL,
                [
                    ':qp0' => 'user1',
                ],
            ],
            'regular values' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT ("email") DO UPDATE SET "address"=EXCLUDED."address", "status"=EXCLUDED."status", "profile_id"=EXCLUDED."profile_id"
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'regular values with update part' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                [
                    'address' => 'foo {{city}}',
                    'status' => 2,
                    'orders' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT ("email") DO UPDATE SET "address"=:qp4, "status"=:qp5, "orders"=T_upsert.orders + 1
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                    ':qp4' => 'foo {{city}}',
                    ':qp5' => 2,
                ],
            ],
            'regular values without update part' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                false,
                <<<SQL
                INSERT INTO "T_upsert" ("email", "address", "status", "profile_id") VALUES (:qp0, :qp1, :qp2, :qp3) ON CONFLICT DO NOTHING
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'two overlapping unique constraints without matching primary key' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'recovery_email' => 'recovery@example.com',
                    'address' => 'bar {{city}}',
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert" ("email", "recovery_email", "address") VALUES (:qp0, :qp1, :qp2) ON CONFLICT ("email") DO UPDATE SET "address"=EXCLUDED."address"
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'recovery@example.com',
                    ':qp2' => 'bar {{city}}',
                ],
            ],
            'two independent unique constraints without primary key' => [
                'T_upsert_2',
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 'c',
                ],
                true,
                <<<SQL
                INSERT INTO "T_upsert_2" ("a", "b", "c") VALUES (:qp0, :qp1, :qp2) ON CONFLICT ("a") DO UPDATE SET "c"=EXCLUDED."c"
                SQL,
                [
                    ':qp0' => 1,
                    ':qp1' => 2,
                    ':qp2' => 'c',
                ],
            ],
            'values and expressions' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                true,
                <<<SQL
                INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
            'values and expressions with update part' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                [
                    '[[orders]]' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
            'values and expressions without update part' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                false,
                <<<SQL
                INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) VALUES (:qp0, now())
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
        ];
    }

    /**
     * Data provider for {@see \yiiunit\framework\db\pgsql\QueryBuilderTest::testAlterColumn()} test cases.
     *
     * Provides representative input/output pairs for the `ALTER COLUMN` statement.
     *
     * @return array<string, array{Closure|string, string}>
     */
    public static function alterColumn(): array
    {
        return [
            'abstract type string' => [
                'string',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255)
                SQL,
            ],
            'ADD GENERATED identity action' => [
                'ADD GENERATED ALWAYS AS IDENTITY',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" ADD GENERATED ALWAYS AS IDENTITY
                SQL,
            ],
            'builder append with USING conversion' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder('uuid')
                    ->append('USING bar::uuid'),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE uuid USING bar::uuid
                SQL,
            ],
            'builder boolean false default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN)
                    ->defaultValue(false),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE boolean, ALTER COLUMN "bar" SET DEFAULT FALSE
                SQL,
            ],
            'builder boolean true default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN)
                    ->defaultValue(true),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE boolean, ALTER COLUMN "bar" SET DEFAULT TRUE
                SQL,
            ],
            'builder check' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->check('char_length(bar) > 5'),
                <<<SQL
                ALTER TABLE "foo1" DROP CONSTRAINT IF EXISTS foo1_bar_check, ALTER COLUMN "bar" TYPE varchar(255), ADD CONSTRAINT foo1_bar_check CHECK (char_length(bar) > 5)
                SQL,
            ],
            'builder default expression function' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression('NOW()'),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE timestamp(0), ALTER COLUMN "bar" SET DEFAULT NOW()
                SQL,
            ],
            'builder default expression nextval' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->defaultExpression("nextval('public.sequence'::regclass)"),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE integer, ALTER COLUMN "bar" SET DEFAULT nextval('public.sequence'::regclass)
                SQL,
            ],
            'builder default expression with cast' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression("'now'::timestamp"),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE timestamp(0), ALTER COLUMN "bar" SET DEFAULT 'now'::timestamp
                SQL,
            ],
            'builder default expression with comma' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression("date_trunc('day', CURRENT_TIMESTAMP)"),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE timestamp(0), ALTER COLUMN "bar" SET DEFAULT date_trunc('day', CURRENT_TIMESTAMP)
                SQL,
            ],
            'builder float default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DOUBLE)
                    ->defaultValue(1.5),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE double precision, ALTER COLUMN "bar" SET DEFAULT 1.5
                SQL,
            ],
            'builder integer default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->defaultValue(42),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE integer, ALTER COLUMN "bar" SET DEFAULT 42
                SQL,
            ],
            'builder not null' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull(),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET NOT NULL
                SQL,
            ],
            'builder not null with default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull()
                    ->defaultValue('hello world'),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET NOT NULL, ALTER COLUMN "bar" SET DEFAULT 'hello world'
                SQL,
            ],
            'builder null' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->null(),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP NOT NULL
                SQL,
            ],
            'builder null default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue(null),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" DROP NOT NULL
                SQL,
            ],
            'builder scalar default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('hello world'),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET DEFAULT 'hello world'
                SQL,
            ],
            'builder scalar default with quote' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING)
                    ->defaultValue("O'Reilly"),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT, ALTER COLUMN "bar" TYPE varchar(255), ALTER COLUMN "bar" SET DEFAULT 'O''Reilly'
                SQL,
            ],
            'builder type only' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255),
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255)
                SQL,
            ],
            'builder unique' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 30)
                    ->unique(),
                <<<SQL
                ALTER TABLE "foo1" DROP CONSTRAINT IF EXISTS foo1_bar_key, ALTER COLUMN "bar" TYPE varchar(30), ADD CONSTRAINT foo1_bar_key UNIQUE ("bar")
                SQL,
            ],
            'compound definition string is not parsed' => [
                'timestamp NOT NULL DEFAULT NOW()',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE timestamp(0) NOT NULL DEFAULT NOW()
                SQL,
            ],
            'DROP DEFAULT action' => [
                'DROP DEFAULT',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP DEFAULT
                SQL,
            ],
            'DROP IDENTITY action' => [
                'DROP IDENTITY IF EXISTS',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP IDENTITY IF EXISTS
                SQL,
            ],
            'DROP NOT NULL action' => [
                'DROP NOT NULL',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" DROP NOT NULL
                SQL,
            ],
            'lowercase action passthrough' => [
                'drop default',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" drop default
                SQL,
            ],
            'lowercase ADD GENERATED action' => [
                'add generated by default as identity',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" add generated by default as identity
                SQL,
            ],
            'lowercase RESTART action' => [
                'restart with 100',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" restart with 100
                SQL,
            ],
            'native type string' => [
                'varchar(255)',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE varchar(255)
                SQL,
            ],
            'native multi-word type string' => [
                'timestamp with time zone',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE timestamp(0) with time zone
                SQL,
            ],
            'RESET attribute option action' => [
                'RESET (n_distinct)',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" RESET (n_distinct)
                SQL,
            ],
            'RESET attribute option without space action' => [
                'RESET(n_distinct)',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" RESET(n_distinct)
                SQL,
            ],
            'SET attribute option without space action' => [
                'SET(n_distinct=4)',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET(n_distinct=4)
                SQL,
            ],
            'SET COMPRESSION action' => [
                'SET COMPRESSION lz4',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET COMPRESSION lz4
                SQL,
            ],
            'SET DEFAULT expression action' => [
                'SET DEFAULT NOW()',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET DEFAULT NOW()
                SQL,
            ],
            'SET DEFAULT nextval action' => [
                "SET DEFAULT nextval('foo_seq'::regclass)",
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET DEFAULT nextval('foo_seq'::regclass)
                SQL,
            ],
            'SET DEFAULT parenthesized expression action' => [
                "SET DEFAULT (CURRENT_TIMESTAMP AT TIME ZONE 'UTC')",
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET DEFAULT (CURRENT_TIMESTAMP AT TIME ZONE 'UTC')
                SQL,
            ],
            'SET DEFAULT string literal action' => [
                "SET DEFAULT 'hello world'",
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET DEFAULT 'hello world'
                SQL,
            ],
            'SET GENERATED action' => [
                'SET GENERATED BY DEFAULT',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET GENERATED BY DEFAULT
                SQL,
            ],
            'SET NOT NULL action' => [
                'SET NOT NULL',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET NOT NULL
                SQL,
            ],
            'SET STATISTICS action' => [
                'SET STATISTICS 1000',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET STATISTICS 1000
                SQL,
            ],
            'SET STORAGE action' => [
                'SET STORAGE EXTENDED',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" SET STORAGE EXTENDED
                SQL,
            ],
            'type with USING conversion' => [
                'uuid USING bar::uuid',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE uuid USING bar::uuid
                SQL,
            ],
            'RESTART bare action' => [
                'RESTART',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" RESTART
                SQL,
            ],
            'RESTART WITH action' => [
                'RESTART WITH 100',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" RESTART WITH 100
                SQL,
            ],
            'unsupported ADD action treated as type string' => [
                'ADD UNIQUE',
                <<<SQL
                ALTER TABLE "foo1" ALTER COLUMN "bar" TYPE ADD UNIQUE
                SQL,
            ],
        ];
    }
}

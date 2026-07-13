<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

use yii\db\Expression;
use yii\db\Query;

/**
 * Data provider for {@see \yiiunit\framework\db\pgsql\QueryBuilderTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderProvider
{
    /**
     * @return array<string, array{string, array<string, mixed>|Query, array<string, mixed>|bool, string, array<string, mixed>}>
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
}

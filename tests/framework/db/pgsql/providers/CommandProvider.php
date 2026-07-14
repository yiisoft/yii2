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
 * Data provider for {@see \yiiunit\framework\db\pgsql\CommandTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CommandProvider
{
    /**
     * @return array<
     *   string,
     *   array{
     *     string,
     *     array<string, mixed>|Query,
     *     array<string, mixed>|Query,
     *     array<string, mixed>|bool,
     *     array<string, mixed>
     *   }
     * >
     */
    public static function upsert(): array
    {
        return [
            'composite unique constraint conflict' => [
                'T_upsert_3',
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 'first',
                ],
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 'second',
                ],
                true,
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 'second',
                ],
            ],
            'independent unique constraints conflict on arbiter' => [
                'T_upsert_2',
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 'first',
                ],
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 'second',
                ],
                true,
                [
                    'a' => 1,
                    'b' => 1,
                    'c' => 'second',
                ],
            ],
            'multiple matching constraints without update part' => [
                'T_upsert',
                [
                    'id' => 1,
                    'email' => 'a@example.com',
                ],
                [
                    'id' => 1,
                    'email' => 'b@example.com',
                ],
                false,
                [
                    'id' => 1,
                    'email' => 'a@example.com',
                ],
            ],
            'no columns to update' => [
                'T_upsert_1',
                ['a' => 1],
                ['a' => 1],
                true,
                ['a' => 1],
            ],
            'primary key and separate unique constraint' => [
                'T_upsert',
                [
                    'id' => 1,
                    'email' => 'a@example.com',
                    'address' => 'first address',
                ],
                [
                    'id' => 1,
                    'email' => 'b@example.com',
                    'address' => 'second address',
                ],
                [
                    'address' => 'updated address',
                ],
                [
                    'id' => 1,
                    'email' => 'a@example.com',
                    'address' => 'updated address',
                ],
            ],
            'query' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                true,
                [
                    'email' => 'user1@example.com',
                    'address' => 'address1',
                    'status' => 2,
                ],
            ],
            'query with update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('3'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                [
                    'address' => 'Moon',
                    'status' => 2,
                ],
                [
                    'email' => 'user1@example.com',
                    'address' => 'Moon',
                    'status' => 2,
                ],
            ],
            'query without update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('1'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                (new Query())
                    ->select(
                        [
                            'email',
                            'address',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                false,
                [
                    'email' => 'user1@example.com',
                    'address' => 'address1',
                    'status' => 1,
                ],
            ],
            'regular values' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                true,
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
            ],
            'regular values with update part' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                [
                    'address' => 'Moon',
                    'status' => 2,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Moon',
                    'status' => 2,
                ],
            ],
            'regular values without update part' => [
                'T_upsert',
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
                [
                    'email' => 'foo@example.com',
                    'address' => 'Universe',
                    'status' => 1,
                ],
                false,
                [
                    'email' => 'foo@example.com',
                    'address' => 'Earth',
                    'status' => 3,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<int, string>, Closure|string, array<string, mixed>}>
     */
    public static function alterColumn(): array
    {
        return [
            'abstract type string' => [
                'varchar(100)',
                [],
                'string',
                ['type' => 'string'],
            ],
            'ADD GENERATED identity action' => [
                'int NOT NULL',
                [],
                'ADD GENERATED ALWAYS AS IDENTITY',
                [],
            ],
            'builder check' => [
                'varchar(100) CHECK (char_length(bar) > 1)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->check('char_length(bar) > 5'),
                ['checkContains' => 'char_length', 'repeatable' => true],
            ],
            'builder default expression function' => [
                'timestamp',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression('NOW()'),
                ['defaultValueContains' => 'now'],
            ],
            'builder default expression nextval' => [
                'integer',
                ['DROP SEQUENCE IF EXISTS "sequence"', 'CREATE SEQUENCE "sequence"'],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->defaultExpression("nextval('public.sequence'::regclass)"),
                ['defaultValueContains' => 'nextval'],
            ],
            'builder default expression with cast' => [
                'timestamp',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression("'now'::timestamp"),
                // PostgreSQL evaluates `'now'::timestamp` once at DDL time; the reflected default is a frozen
                // timestamp literal, so only the time separator is a stable substring.
                ['defaultValueContains' => ':'],
            ],
            'builder default expression with comma' => [
                'timestamp',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression("date_trunc('day', CURRENT_TIMESTAMP)"),
                ['defaultValueContains' => 'date_trunc'],
            ],
            'builder not null' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull(),
                ['allowNull' => false],
            ],
            'builder not null with default' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull()
                    ->defaultValue('hello world'),
                ['allowNull' => false, 'defaultValue' => 'hello world'],
            ],
            'builder null' => [
                'varchar(100) NOT NULL',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->null(),
                ['allowNull' => true],
            ],
            'builder null default' => [
                "varchar(100) NOT NULL DEFAULT 'x'",
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue(null),
                ['allowNull' => true, 'defaultValue' => 'x'],
            ],
            'builder scalar default' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('hello world'),
                ['defaultValue' => 'hello world'],
            ],
            'builder scalar default with quote' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING)
                    ->defaultValue("O'Reilly"),
                // Reflected metadata keeps the SQL-escaped doubled quote; inserted rows receive the plain value.
                ['defaultValue' => "O''Reilly"],
            ],
            'builder type only' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255),
                ['type' => 'string'],
            ],
            'builder unique' => [
                'varchar(100) UNIQUE',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 30)
                    ->unique(),
                ['uniqueColumns' => ['bar'], 'repeatable' => true],
            ],
            'DROP DEFAULT action' => [
                "varchar(100) DEFAULT 'x'",
                [],
                'DROP DEFAULT',
                ['defaultValue' => null],
            ],
            'DROP IDENTITY action' => [
                'integer',
                [],
                'DROP IDENTITY IF EXISTS',
                [],
            ],
            'DROP NOT NULL action' => [
                'varchar(100) NOT NULL',
                [],
                'DROP NOT NULL',
                ['allowNull' => true],
            ],
            'lowercase action passthrough' => [
                "varchar(100) DEFAULT 'x'",
                [],
                'drop default',
                ['defaultValue' => null],
            ],
            'lowercase ADD GENERATED action' => [
                'int NOT NULL',
                [],
                'add generated by default as identity',
                [],
            ],
            'lowercase RESTART action' => [
                'int GENERATED ALWAYS AS IDENTITY',
                [],
                'restart with 100',
                [],
            ],
            'native type string' => [
                'varchar(100)',
                [],
                'varchar(255)',
                ['type' => 'string'],
            ],
            'native multi-word type string' => [
                'timestamp NOT NULL DEFAULT now()',
                [],
                'timestamp with time zone',
                ['allowNull' => false, 'defaultValueContains' => 'now'],
            ],
            'RESET attribute option action' => [
                'varchar(100)',
                [],
                'RESET (n_distinct)',
                [],
            ],
            'RESET attribute option without space action' => [
                'varchar(100)',
                [],
                'RESET(n_distinct)',
                [],
            ],
            'SET attribute option without space action' => [
                'varchar(100)',
                [],
                'SET(n_distinct=4)',
                [],
            ],
            'SET COMPRESSION action' => [
                'varchar(100)',
                [],
                'SET COMPRESSION lz4',
                [],
            ],
            'SET DEFAULT expression action' => [
                'timestamp',
                [],
                'SET DEFAULT NOW()',
                ['defaultValueContains' => 'now'],
            ],
            'SET DEFAULT nextval action' => [
                'integer',
                ['DROP SEQUENCE IF EXISTS foo_seq', 'CREATE SEQUENCE foo_seq'],
                "SET DEFAULT nextval('foo_seq'::regclass)",
                ['defaultValueContains' => 'foo_seq'],
            ],
            'SET DEFAULT parenthesized expression action' => [
                'timestamp',
                [],
                "SET DEFAULT (CURRENT_TIMESTAMP AT TIME ZONE 'UTC')",
                ['defaultValueContains' => 'AT TIME ZONE'],
            ],
            'SET DEFAULT string literal action' => [
                'varchar(100)',
                [],
                "SET DEFAULT 'hello world'",
                ['defaultValue' => 'hello world'],
            ],
            'SET GENERATED action' => [
                'int GENERATED ALWAYS AS IDENTITY',
                [],
                'SET GENERATED BY DEFAULT',
                [],
            ],
            'SET NOT NULL action' => [
                'varchar(100)',
                [],
                'SET NOT NULL',
                ['allowNull' => false],
            ],
            'SET STATISTICS action' => [
                'varchar(100)',
                [],
                'SET STATISTICS 1000',
                [],
            ],
            'SET STORAGE action' => [
                'varchar(100)',
                [],
                'SET STORAGE EXTENDED',
                [],
            ],
            'type with USING conversion' => [
                'varchar(36)',
                [],
                'uuid USING bar::uuid',
                ['dbType' => 'uuid'],
            ],
            'RESTART bare action' => [
                'int GENERATED ALWAYS AS IDENTITY',
                [],
                'RESTART',
                [],
            ],
            'RESTART WITH action' => [
                'int GENERATED ALWAYS AS IDENTITY',
                [],
                'RESTART WITH 100',
                [],
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function alterColumnFailing(): array
    {
        return [
            'compound definition string is not parsed' => [
                'timestamp NOT NULL DEFAULT NOW()',
                'syntax error at or near "NOT"',
            ],
            'unsupported ADD action treated as type string' => [
                'ADD UNIQUE',
                'syntax error at or near "UNIQUE"',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use Closure;
use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\CommandTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CommandProvider
{
    /**
     * @return array<string, array{Closure}>
     */
    public static function zeroLimitQueries(): array
    {
        return [
            'normal SELECT with parameters' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('customer')
                    ->where(['status' => 2])
                    ->limit(0),
            ],
            'SELECT with offset' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('customer')
                    ->limit(0)
                    ->offset(5),
            ],
            'SELECT DISTINCT' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->distinct()
                    ->from('customer')
                    ->limit(0),
            ],
            'unnamed expression' => [
                static fn (): Query => (new Query())
                    ->select(new Expression('1 + 1'))
                    ->limit(0),
            ],
            'unnamed aggregate' => [
                static fn (): Query => (new Query())
                    ->select(new Expression('COUNT(*)'))
                    ->from('customer')
                    ->limit(0),
            ],
            'self-join with duplicate column names' => [
                static fn (): Query => (new Query())
                    ->select(['c1.id', 'c2.id'])
                    ->from(['c1' => 'customer'])
                    ->innerJoin(
                        ['c2' => 'customer'],
                        ['c1.id' => new Expression('[c2].[id]')],
                    )
                    ->limit(0),
            ],
            'outer SELECT with CTE' => [
                static fn (): Query => (new Query())
                    ->withQuery(
                        (new Query())
                            ->select('id')
                            ->from('customer'),
                        'customers',
                    )
                    ->select('id')
                    ->from('customers')
                    ->limit(0),
            ],
        ];
    }

    /**
     * @return array<string, array{Closure}>
     */
    public static function rawUnionLimitZero(): array
    {
        return [
            'ALL preserved' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT ALL id FROM category')
                    ->unionLimit(0),
            ],
            'DISTINCT preserved' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT DISTINCT id FROM category')
                    ->unionLimit(0),
            ],
            'existing TOP replaced' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT TOP (5) id FROM category')
                    ->unionLimit(0),
            ],
            'legacy numeric TOP replaced' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT TOP 10 id FROM category')
                    ->unionLimit(0),
            ],
            'nested parentheses in TOP replaced' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT TOP ((5)) id FROM category')
                    ->unionLimit(0),
            ],
            'plain SELECT' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT id FROM category')
                    ->unionLimit(0),
            ],
            'TOP PERCENT preserved' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT DISTINCT TOP ((5)) PERCENT id FROM category')
                    ->unionLimit(0),
            ],
            'TOP with expression replaced' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('item')
                    ->union('SELECT TOP (ABS(5)) id FROM category')
                    ->unionLimit(0),
            ],
        ];
    }

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
            'no columns to update' => [
                'T_upsert_1',
                ['a' => 1],
                ['a' => 1],
                true,
                ['a' => 1],
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
     * @return array<string, array{string}>
     */
    public static function checkIntegrity(): array
    {
        return [
            'catalog-qualified table name' => ['yiitest.dbo.T_constraints_3'],
            'simple table name' => ['T_constraints_3'],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function addCommentOnColumn(): array
    {
        return [
            'catalog-qualified table name' => [
                'comment_column_catalog',
                'yiitest.dbo.comment_column_catalog',
                'comment_column',
            ],
            'column name with single quote' => [
                "comment_column'",
                "comment_column'",
                "comment column'",
            ],
            'simple table name' => [
                'comment_column',
                'comment_column',
                'comment_column',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string, mixed, string}>
     */
    public static function addDefaultValue(): array
    {
        return [
            'false bit default' => [
                'test_def_bit',
                'test_def_bit_constraint',
                'bit1',
                'bit',
                false,
                '/^.*0.*$/',
            ],
            'integer default' => [
                'test_def_int',
                'test_def_int_constraint',
                'int1',
                'integer',
                41,
                '/^.*41.*$/',
            ],
            'null default' => [
                'test_def_null',
                'test_def_null_constraint',
                'int1',
                'integer',
                null,
                '/^.*NULL.*$/i',
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
                ['type' => 'string', 'dbType' => 'nvarchar(255)'],
            ],
            'abstract type string not null passthrough' => [
                'varchar(100)',
                [],
                'string NOT NULL',
                ['allowNull' => false],
            ],
            'builder check' => [
                'varchar(100) CHECK (LEN(bar) > 1)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->check('LEN(bar) > 5'),
                ['checkContains' => 'len', 'repeatable' => true],
            ],
            'builder default expression function' => [
                'datetime',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DATETIME)
                    ->defaultExpression('GETDATE()'),
                ['defaultExpressionContains' => 'getdate'],
            ],
            'builder default expression with comma' => [
                'datetime',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DATETIME)
                    ->defaultExpression('DATEADD(day, 1, GETDATE())'),
                ['defaultExpressionContains' => 'dateadd'],
            ],
            'builder float default' => [
                'double',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DOUBLE)
                    ->defaultValue(1.5),
                ['defaultValue' => 1.5],
            ],
            'builder integer default' => [
                'integer',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->defaultValue(42),
                ['defaultValue' => 42],
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
                // MSSQL drops the old default and re-adds `DEFAULT NULL`, so the reflected default is `null`
                // (PostgreSQL keeps the pre-existing literal because it never re-adds a default).
                ['allowNull' => true, 'defaultValue' => null],
            ],
            'builder scalar default' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('hello world'),
                ['defaultValue' => 'hello world'],
            ],
            'builder type only' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255),
                ['type' => 'string', 'dbType' => 'nvarchar(255)'],
            ],
            'builder unique' => [
                'varchar(100)',
                [],
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 30)
                    ->unique(),
                ['uniqueColumns' => ['bar'], 'repeatable' => true],
            ],
            'native type string' => [
                'varchar(100)',
                [],
                'varchar(255)',
                ['type' => 'string', 'dbType' => 'varchar(255)'],
            ],
            'string type drops existing check' => [
                'varchar(100) CHECK (LEN(bar) > 1)',
                [],
                'varchar(255)',
                ['checkCount' => 0],
            ],
            'string type drops existing default' => [
                "varchar(100) DEFAULT 'x'",
                [],
                'varchar(255)',
                ['defaultValue' => null, 'defaultCount' => 0],
            ],
            'string unique start dropped' => [
                'varchar(100) UNIQUE',
                [],
                'varchar(255)',
                ['uniqueCount' => 0],
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function alterColumnFailing(): array
    {
        return [
            'inline CHECK clause' => [
                'varchar(255) CHECK (LEN(bar) > 1)',
                "Incorrect syntax near the keyword 'CHECK'.",
            ],
            'inline DEFAULT clause' => [
                "varchar(255) DEFAULT 'x'",
                "Incorrect syntax near the keyword 'DEFAULT'.",
            ],
            'inline UNIQUE clause' => [
                'varchar(255) UNIQUE',
                "Incorrect syntax near the keyword 'UNIQUE'.",
            ],
            'PostgreSQL DROP DEFAULT action' => [
                'DROP DEFAULT',
                "Incorrect syntax near the keyword 'DEFAULT'.",
            ],
            'PostgreSQL SET NOT NULL action' => [
                'SET NOT NULL',
                "Incorrect syntax near the keyword 'SET'.",
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, int, bool, int|null, int}>
     */
    public static function resetSequence(): array
    {
        return [
            'new table with default reset' => [
                'reset_sequence_new_default',
                'reset_sequence_new_default',
                0,
                false,
                null,
                1,
            ],
            'new table with explicit reset and quoted name' => [
                '[reset_sequence_quoted]',
                'reset_sequence_quoted',
                0,
                false,
                5,
                5,
            ],
            'new table with maximum integer reset' => [
                'reset_sequence_int_max',
                'reset_sequence_int_max',
                0,
                false,
                2147483647,
                2147483647,
            ],
            'used table with default reset' => [
                'reset_sequence_used_default',
                'reset_sequence_used_default',
                2,
                false,
                null,
                3,
            ],
            'used table with explicit reset' => [
                'reset_sequence_used_explicit',
                'reset_sequence_used_explicit',
                1,
                false,
                5,
                5,
            ],
            'deleted table with default reset' => [
                'reset_sequence_deleted_default',
                'reset_sequence_deleted_default',
                1,
                true,
                null,
                1,
            ],
            'deleted table with explicit reset and single quote name' => [
                "reset_sequence_quote'",
                "reset_sequence_quote'",
                1,
                true,
                5,
                5,
            ],
            'new table with explicit reset and spaces' => [
                'reset sequence',
                'reset sequence',
                0,
                false,
                5,
                5,
            ],
            'new table with explicit reset and unicode characters' => [
                'reset_sequence_ñ_表',
                'reset_sequence_ñ_表',
                0,
                false,
                5,
                5,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function renameTable(): array
    {
        return [
            'already quoted table names' => [
                '[dbo].[rename_quoted_from]',
                '[rename_quoted_to]',
                'rename_quoted_from',
                'rename_quoted_to',
            ],
            'curly brace table placeholders' => [
                '{{rename_curly_from}}',
                '{{rename_curly_to}}',
                'rename_curly_from',
                'rename_curly_to',
            ],
            'new table name with single quote' => [
                'rename_quote_from',
                "rename_quote_to'",
                'rename_quote_from',
                "rename_quote_to'",
            ],
            'schema qualified new table name' => [
                'rename_new_schema_from',
                'dbo.rename_new_schema_to',
                'rename_new_schema_from',
                'rename_new_schema_to',
            ],
            'schema qualified old table name' => [
                'dbo.rename_schema_from',
                'rename_schema_to',
                'rename_schema_from',
                'rename_schema_to',
            ],
            'simple table names' => [
                'rename_from',
                'rename_to',
                'rename_from',
                'rename_to',
            ],
            'square bracket placeholders' => [
                '[[rename_square_from]]',
                '[[rename_square_to]]',
                'rename_square_from',
                'rename_square_to',
            ],
            'table names with spaces' => [
                'rename from',
                'rename to',
                'rename from',
                'rename to',
            ],
            'table names with unicode characters' => [
                'rename_unicode_from',
                'rename_unicode_ñ_表',
                'rename_unicode_from',
                'rename_unicode_ñ_表',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string, string, string}>
     */
    public static function renameColumn(): array
    {
        return [
            'already quoted names' => [
                '[dbo].[rename_column_quoted]',
                '[old_col]',
                '[new_col]',
                'rename_column_quoted',
                'old_col',
                'new_col',
            ],
            'curly brace table and square bracket column placeholders' => [
                '{{rename_column_curly}}',
                '[[old_col]]',
                '[[new_col]]',
                'rename_column_curly',
                'old_col',
                'new_col',
            ],
            'names with single quotes' => [
                "rename_column_quote'from",
                "old'col",
                "new'col",
                "rename_column_quote'from",
                "old'col",
                "new'col",
            ],
            'names with spaces' => [
                'rename column',
                'old col',
                'new col',
                'rename column',
                'old col',
                'new col',
            ],
            'names with unicode characters' => [
                'rename_column_unicode',
                'old_ñ_表',
                'new_ñ_表',
                'rename_column_unicode',
                'old_ñ_表',
                'new_ñ_表',
            ],
            'schema qualified new column name' => [
                'rename_column_new_schema',
                'old_col',
                'dbo.new_col',
                'rename_column_new_schema',
                'old_col',
                'new_col',
            ],
            'schema qualified table name' => [
                'dbo.rename_column_schema',
                'old_col',
                'new_col',
                'rename_column_schema',
                'old_col',
                'new_col',
            ],
            'simple names' => [
                'rename_column',
                'old_col',
                'new_col',
                'rename_column',
                'old_col',
                'new_col',
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<string, mixed>, array<string, mixed>}>
     */
    public static function insert(): array
    {
        return [
            'catalog-qualified table name' => [
                'yiitest.dbo.customer',
                [
                    'email' => 'catalog@example.com',
                    'name' => 'catalog',
                    'address' => 'catalog address',
                    'status' => 1,
                ],
                [
                    'email' => 'catalog@example.com',
                    'name' => 'catalog',
                    'address' => 'catalog address',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<string, mixed>, array<string, mixed>, array<string, mixed>}>
     */
    public static function upsertWithCatalogQualifiedTableName(): array
    {
        return [
            'catalog-qualified table name' => [
                'yiitest.dbo.T_upsert',
                [
                    'email' => 'catalog@example.com',
                    'address' => 'first address',
                    'status' => 1,
                ],
                [
                    'email' => 'catalog@example.com',
                    'address' => 'second address',
                    'status' => 2,
                ],
                [
                    'email' => 'catalog@example.com',
                    'address' => 'second address',
                    'status' => 2,
                ],
            ],
        ];
    }
}

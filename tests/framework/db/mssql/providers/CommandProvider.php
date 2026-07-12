<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use yii\db\Expression;
use yii\db\Query;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\CommandTest} test cases.
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

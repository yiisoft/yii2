<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

use yii\db\Expression;
use yii\db\Query;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\CommandTest} test cases.
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
     * @return array<string, array{string, string, string}>
     */
    public static function addCommentOnColumn(): array
    {
        return [
            'column name with single quote' => [
                'comment_column_quote',
                'comment_column_quote',
                "comment column'",
            ],
            'database-qualified table name' => [
                'comment_column_qualified',
                'yiitest.comment_column_qualified',
                'comment_column',
            ],
            'simple table name' => [
                'comment_column',
                'comment_column',
                'comment_column',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, array<string>|string, bool, array<string>, bool}>
     */
    public static function createIndex(): array
    {
        return [
            'columns as comma-separated string' => [
                'create_index_string_columns',
                'create_index_string_columns',
                'idx_string_columns',
                'int1, int2',
                false,
                ['int1', 'int2'],
                false,
            ],
            'database-qualified name' => [
                'create_index_qualified',
                'yiitest.create_index_qualified',
                'idx_qualified',
                ['int1'],
                false,
                ['int1'],
                false,
            ],
            'database-qualified name unique' => [
                'create_index_qualified_unique',
                'yiitest.create_index_qualified_unique',
                'idx_qualified_unique',
                ['int1'],
                true,
                ['int1'],
                true,
            ],
            'multiple columns' => [
                'create_index_multi',
                'create_index_multi',
                'idx_multi',
                ['int1', 'int2'],
                false,
                ['int1', 'int2'],
                false,
            ],
            'single column' => [
                'create_index_single',
                'create_index_single',
                'idx_single',
                ['int1'],
                false,
                ['int1'],
                false,
            ],
            'unique multiple columns' => [
                'create_index_unique_multi',
                'create_index_unique_multi',
                'idx_unique_multi',
                ['int1', 'int2'],
                true,
                ['int1', 'int2'],
                true,
            ],
            'unique single column' => [
                'create_index_unique_single',
                'create_index_unique_single',
                'idx_unique_single',
                ['int1'],
                true,
                ['int1'],
                true,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, array<string>, array<string>}>
     */
    public static function dropForeignKey(): array
    {
        return [
            'database-qualified backtick-quoted name' => [
                'drop_fk_qualified_bt',
                '`yiitest`.`drop_fk_qualified_bt`',
                'fk_qualified_bt',
                ['int1'],
                ['int3'],
            ],
            'database-qualified name' => [
                'drop_fk_qualified',
                'yiitest.drop_fk_qualified',
                'fk_qualified',
                ['int1'],
                ['int3'],
            ],
            'multiple columns' => [
                'drop_fk_multi',
                'drop_fk_multi',
                'fk_multi',
                ['int1', 'int2'],
                ['int3', 'int4'],
            ],
            'single column' => [
                'drop_fk_single',
                'drop_fk_single',
                'fk_single',
                ['int1'],
                ['int3'],
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, array<string>}>
     */
    public static function dropPrimaryKey(): array
    {
        return [
            'database-qualified backtick-quoted name' => [
                'drop_pk_qualified_bt',
                '`yiitest`.`drop_pk_qualified_bt`',
                'pk_qualified_bt',
                ['int1'],
            ],
            'database-qualified name' => [
                'drop_pk_qualified',
                'yiitest.drop_pk_qualified',
                'pk_qualified',
                ['int1'],
            ],
            'multiple columns' => [
                'drop_pk_multi',
                'drop_pk_multi',
                'pk_multi',
                ['int1', 'int2'],
            ],
            'single column' => [
                'drop_pk_single',
                'drop_pk_single',
                'pk_single',
                ['int1'],
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
                '`reset_sequence_quoted`',
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
            'deleted table with explicit reset' => [
                'reset_sequence_deleted_explicit',
                'reset_sequence_deleted_explicit',
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
    public static function renameColumn(): array
    {
        return [
            'database-qualified table name' => [
                'column_qualified',
                'yiitest.column_qualified',
                'old_col',
                'new_col',
            ],
            'names with spaces' => [
                'rename_column_spaces',
                'rename_column_spaces',
                'old col',
                'new col',
            ],
            'names with unicode characters' => [
                'rename_column_unicode',
                'rename_column_unicode',
                'old_ñ_表',
                'new_ñ_表',
            ],
            'simple names' => [
                'rename_column_simple',
                'rename_column_simple',
                'old_col',
                'new_col',
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function commentSpecialCharacters(): array
    {
        return [
            'backslash' => ['path C:\\dir'],
            'double quote' => ['say "hello"'],
            'mixed quote and backslash' => ['It\'s a \\ path "q"'],
            'multiple single quotes' => ['\'a\' and \'b\''],
            'single quote' => ['It\'s a comment'],
            'sql injection attempt' => ['\'; DROP TABLE x; --'],
            'unicode accents' => ['café déjà vu'],
        ];
    }
}

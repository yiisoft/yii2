<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\CommandTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CommandProvider
{
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
     * @return array<string, array{string, string, int, bool, int|string|null, int}>
     */
    public static function resetSequence(): array
    {
        return [
            'new table with default reset' => [
                'yii2_mssql_reset_sequence_new_default',
                'yii2_mssql_reset_sequence_new_default',
                0,
                false,
                null,
                1,
            ],
            'new table with explicit reset and quoted name' => [
                '[yii2_mssql_reset_sequence_quoted]',
                'yii2_mssql_reset_sequence_quoted',
                0,
                false,
                5,
                5,
            ],
            'used table with default reset' => [
                'yii2_mssql_reset_sequence_used_default',
                'yii2_mssql_reset_sequence_used_default',
                2,
                false,
                null,
                3,
            ],
            'used table with explicit reset' => [
                'yii2_mssql_reset_sequence_used_explicit',
                'yii2_mssql_reset_sequence_used_explicit',
                1,
                false,
                5,
                5,
            ],
            'used table with numeric string reset' => [
                'yii2_mssql_reset_sequence_used_numeric_string',
                'yii2_mssql_reset_sequence_used_numeric_string',
                1,
                false,
                '6',
                6,
            ],
            'deleted table with default reset' => [
                'yii2_mssql_reset_sequence_deleted_default',
                'yii2_mssql_reset_sequence_deleted_default',
                1,
                true,
                null,
                1,
            ],
            'deleted table with explicit reset and single quote name' => [
                "yii2_mssql_reset_sequence_quote'",
                "yii2_mssql_reset_sequence_quote'",
                1,
                true,
                5,
                5,
            ],
            'new table with explicit reset and spaces' => [
                'yii2 mssql reset sequence',
                'yii2 mssql reset sequence',
                0,
                false,
                5,
                5,
            ],
            'new table with explicit reset and unicode characters' => [
                'yii2_mssql_reset_sequence_ñ_表',
                'yii2_mssql_reset_sequence_ñ_表',
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
                '[dbo].[yii2_mssql_rename_quoted_from]',
                '[yii2_mssql_rename_quoted_to]',
                'yii2_mssql_rename_quoted_from',
                'yii2_mssql_rename_quoted_to',
            ],
            'curly brace table placeholders' => [
                '{{yii2_mssql_rename_curly_from}}',
                '{{yii2_mssql_rename_curly_to}}',
                'yii2_mssql_rename_curly_from',
                'yii2_mssql_rename_curly_to',
            ],
            'new table name with single quote' => [
                'yii2_mssql_rename_quote_from',
                "yii2_mssql_rename_quote_to'",
                'yii2_mssql_rename_quote_from',
                "yii2_mssql_rename_quote_to'",
            ],
            'schema qualified new table name' => [
                'yii2_mssql_rename_new_schema_from',
                'dbo.yii2_mssql_rename_new_schema_to',
                'yii2_mssql_rename_new_schema_from',
                'yii2_mssql_rename_new_schema_to',
            ],
            'schema qualified old table name' => [
                'dbo.yii2_mssql_rename_schema_from',
                'yii2_mssql_rename_schema_to',
                'yii2_mssql_rename_schema_from',
                'yii2_mssql_rename_schema_to',
            ],
            'simple table names' => [
                'yii2_mssql_rename_from',
                'yii2_mssql_rename_to',
                'yii2_mssql_rename_from',
                'yii2_mssql_rename_to',
            ],
            'square bracket placeholders' => [
                '[[yii2_mssql_rename_square_from]]',
                '[[yii2_mssql_rename_square_to]]',
                'yii2_mssql_rename_square_from',
                'yii2_mssql_rename_square_to',
            ],
            'table names with spaces' => [
                'yii2 mssql rename from',
                'yii2 mssql rename to',
                'yii2 mssql rename from',
                'yii2 mssql rename to',
            ],
            'table names with unicode characters' => [
                'yii2_mssql_rename_unicode_from',
                'yii2_mssql_rename_unicode_ñ_表',
                'yii2_mssql_rename_unicode_from',
                'yii2_mssql_rename_unicode_ñ_表',
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
                '[dbo].[yii2_mssql_rename_column_quoted]',
                '[old_col]',
                '[new_col]',
                'yii2_mssql_rename_column_quoted',
                'old_col',
                'new_col',
            ],
            'curly brace table and square bracket column placeholders' => [
                '{{yii2_mssql_rename_column_curly}}',
                '[[old_col]]',
                '[[new_col]]',
                'yii2_mssql_rename_column_curly',
                'old_col',
                'new_col',
            ],
            'names with single quotes' => [
                "yii2_mssql_rename_column_quote'from",
                "old'col",
                "new'col",
                "yii2_mssql_rename_column_quote'from",
                "old'col",
                "new'col",
            ],
            'names with spaces' => [
                'yii2 mssql rename column',
                'old col',
                'new col',
                'yii2 mssql rename column',
                'old col',
                'new col',
            ],
            'names with unicode characters' => [
                'yii2_mssql_rename_column_unicode',
                'old_ñ_表',
                'new_ñ_表',
                'yii2_mssql_rename_column_unicode',
                'old_ñ_表',
                'new_ñ_表',
            ],
            'schema qualified new column name' => [
                'yii2_mssql_rename_column_new_schema',
                'old_col',
                'dbo.new_col',
                'yii2_mssql_rename_column_new_schema',
                'old_col',
                'new_col',
            ],
            'schema qualified table name' => [
                'dbo.yii2_mssql_rename_column_schema',
                'old_col',
                'new_col',
                'yii2_mssql_rename_column_schema',
                'old_col',
                'new_col',
            ],
            'simple names' => [
                'yii2_mssql_rename_column',
                'old_col',
                'new_col',
                'yii2_mssql_rename_column',
                'old_col',
                'new_col',
            ],
        ];
    }
}

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

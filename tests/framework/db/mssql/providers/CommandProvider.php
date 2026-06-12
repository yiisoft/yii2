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
            'simple table names' => [
                'yii2_mssql_rename_from',
                'yii2_mssql_rename_to',
                'yii2_mssql_rename_from',
                'yii2_mssql_rename_to',
            ],
            'schema qualified old table name' => [
                'dbo.yii2_mssql_rename_schema_from',
                'yii2_mssql_rename_schema_to',
                'yii2_mssql_rename_schema_from',
                'yii2_mssql_rename_schema_to',
            ],
            'curly brace table placeholders' => [
                '{{yii2_mssql_rename_curly_from}}',
                '{{yii2_mssql_rename_curly_to}}',
                'yii2_mssql_rename_curly_from',
                'yii2_mssql_rename_curly_to',
            ],
            'square bracket placeholders' => [
                '[[yii2_mssql_rename_square_from]]',
                '[[yii2_mssql_rename_square_to]]',
                'yii2_mssql_rename_square_from',
                'yii2_mssql_rename_square_to',
            ],
            'already quoted table names' => [
                '[dbo].[yii2_mssql_rename_quoted_from]',
                '[yii2_mssql_rename_quoted_to]',
                'yii2_mssql_rename_quoted_from',
                'yii2_mssql_rename_quoted_to',
            ],
            'table names with spaces' => [
                'yii2 mssql rename from',
                'yii2 mssql rename to',
                'yii2 mssql rename from',
                'yii2 mssql rename to',
            ],
            'new table name with single quote' => [
                'yii2_mssql_rename_quote_from',
                "yii2_mssql_rename_quote_to'",
                'yii2_mssql_rename_quote_from',
                "yii2_mssql_rename_quote_to'",
            ],
        ];
    }
}

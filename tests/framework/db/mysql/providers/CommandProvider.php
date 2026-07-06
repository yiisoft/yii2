<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\CommandTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CommandProvider
{
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

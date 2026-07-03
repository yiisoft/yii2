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

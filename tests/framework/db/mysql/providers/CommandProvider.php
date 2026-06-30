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
                'yii2_mysql_comment_column_quote',
                'yii2_mysql_comment_column_quote',
                "comment column'",
            ],
            'database-qualified table name' => [
                'yii2_mysql_comment_column_qualified',
                'yiitest.yii2_mysql_comment_column_qualified',
                'comment_column',
            ],
            'simple table name' => [
                'yii2_mysql_comment_column',
                'yii2_mysql_comment_column',
                'comment_column',
            ],
        ];
    }
}

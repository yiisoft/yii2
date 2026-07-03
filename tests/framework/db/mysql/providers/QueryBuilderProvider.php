<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\QueryBuilderTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderProvider
{
    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function addCommentOnColumn(): array
    {
        return [
            'column default containing CHECK literal' => [
                'qb_check_in_default',
                'description',
                <<<SQL
                CREATE TABLE `qb_check_in_default` (
                  `description` varchar(255) DEFAULT 'literal CHECK (x)'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'New comment.',
                <<<SQL
                ALTER TABLE `qb_check_in_default` CHANGE `description` `description` varchar(255) DEFAULT 'literal CHECK (x)' COMMENT 'New comment.'
                SQL,
            ],
            'column default containing COMMENT literal' => [
                'qb_comment_in_default',
                'description',
                <<<SQL
                CREATE TABLE `qb_comment_in_default` (
                  `description` varchar(255) DEFAULT 'see COMMENT text'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'New comment.',
                <<<SQL
                ALTER TABLE `qb_comment_in_default` CHANGE `description` `description` varchar(255) DEFAULT 'see COMMENT text' COMMENT 'New comment.'
                SQL,
            ],
            'column without existing comment' => [
                'qb_comment_add',
                'description',
                <<<SQL
                CREATE TABLE `qb_comment_add` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'Column comment.',
                <<<SQL
                ALTER TABLE `qb_comment_add` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'Column comment.'
                SQL,
            ],
            'comment with single quote' => [
                'qb_comment_quote',
                'description',
                <<<SQL
                CREATE TABLE `qb_comment_quote` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                "It's a column comment.",
                <<<SQL
                ALTER TABLE `qb_comment_quote` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'It\'s a column comment.'
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.qb_comment_qualified',
                'description',
                <<<SQL
                CREATE TABLE `yiitest`.`qb_comment_qualified` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'Qualified table comment.',
                <<<SQL
                ALTER TABLE `yiitest`.`qb_comment_qualified` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'Qualified table comment.'
                SQL,
            ],
            'replace existing comment' => [
                'qb_comment_replace',
                'description',
                <<<SQL
                CREATE TABLE `qb_comment_replace` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'New comment.',
                <<<SQL
                ALTER TABLE `qb_comment_replace` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT 'New comment.'
                SQL,
            ],
            'replace existing comment with single quote' => [
                'qb_comment_replace_quote',
                'description',
                <<<'SQL'
                CREATE TABLE `qb_comment_replace_quote` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'It\'s old.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                "It's a new column comment.",
                <<<SQL
                ALTER TABLE `qb_comment_replace_quote` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT 'It\'s a new column comment.'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function renameColumn(): array
    {
        return [
            'column preserves comment' => [
                'qb_rename_comment',
                'old_col',
                'new_col',
                <<<SQL
                CREATE TABLE `qb_rename_comment` (
                  `old_col` varchar(255) NOT NULL COMMENT 'Keep me.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `qb_rename_comment` CHANGE `old_col` `new_col` varchar(255) NOT NULL COMMENT 'Keep me.'
                SQL,
            ],
            'column with default' => [
                'qb_rename_default',
                'old_col',
                'new_col',
                <<<SQL
                CREATE TABLE `qb_rename_default` (
                  `old_col` varchar(255) DEFAULT 'something'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `qb_rename_default` CHANGE `old_col` `new_col` varchar(255) DEFAULT 'something'
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.qb_rename_qualified',
                'old_col',
                'new_col',
                <<<SQL
                CREATE TABLE `yiitest`.`qb_rename_qualified` (
                  `old_col` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `yiitest`.`qb_rename_qualified` CHANGE `old_col` `new_col` varchar(255) NOT NULL
                SQL,
            ],
            'not null column' => [
                'qb_rename_notnull',
                'old_col',
                'new_col',
                <<<SQL
                CREATE TABLE `qb_rename_notnull` (
                  `old_col` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `qb_rename_notnull` CHANGE `old_col` `new_col` varchar(255) NOT NULL
                SQL,
            ],
            'simple column' => [
                'qb_rename_simple',
                'old_col',
                'new_col',
                <<<SQL
                CREATE TABLE `qb_rename_simple` (
                  `old_col` varchar(255)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `qb_rename_simple` CHANGE `old_col` `new_col` varchar(255) DEFAULT NULL
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function dropCommentFromColumn(): array
    {
        return [
            'column with existing comment' => [
                'qb_comment_drop',
                'description',
                <<<SQL
                CREATE TABLE `qb_comment_drop` (
                  `description` varchar(255) NOT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `qb_comment_drop` CHANGE `description` `description` varchar(255) NOT NULL COMMENT ''
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.qb_comment_drop_qualified',
                'description',
                <<<SQL
                CREATE TABLE `yiitest`.`qb_comment_drop_qualified` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `yiitest`.`qb_comment_drop_qualified` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT ''
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function addCommentOnTable(): array
    {
        return [
            'comment with single quote' => [
                'qb_comment_table_quote',
                "It's a table comment.",
                <<<SQL
                ALTER TABLE `qb_comment_table_quote` COMMENT 'It\'s a table comment.'
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.qb_comment_table_qualified',
                'Qualified table comment.',
                <<<SQL
                ALTER TABLE `yiitest`.`qb_comment_table_qualified` COMMENT 'Qualified table comment.'
                SQL,
            ],
            'simple table name' => [
                'qb_comment_table',
                'A table comment.',
                <<<SQL
                ALTER TABLE `qb_comment_table` COMMENT 'A table comment.'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dropCommentFromTable(): array
    {
        return [
            'database-qualified table name' => [
                'yiitest.qb_comment_table_drop_qualified',
                <<<SQL
                ALTER TABLE `yiitest`.`qb_comment_table_drop_qualified` COMMENT ''
                SQL,
            ],
            'simple table name' => [
                'qb_comment_table_drop',
                <<<SQL
                ALTER TABLE `qb_comment_table_drop` COMMENT ''
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function addCommentOnTableSpecialCharacters(): array
    {
        return [
            'backslash' => [
                'path C:\\dir',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'path C:\\dir'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'path C:\dir'
                SQL,
            ],
            'double quote' => [
                'say "hello"',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'say \"hello\"'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'say "hello"'
                SQL,
            ],
            'mixed quote and backslash' => [
                'It\'s a \\ path "q"',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'It\'s a \\ path \"q\"'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'It''s a \ path "q"'
                SQL,
            ],
            'multiple single quotes' => [
                '\'a\' and \'b\'',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT '\'a\' and \'b\''
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT '''a'' and ''b'''
                SQL,
            ],
            'single quote' => [
                'It\'s a comment',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'It\'s a comment'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'It''s a comment'
                SQL,
            ],
            'sql injection attempt' => [
                '\'; DROP TABLE x; --',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT '\'; DROP TABLE x; --'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT '''; DROP TABLE x; --'
                SQL,
            ],
            'unicode accents' => [
                'café déjà vu',
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'café déjà vu'
                SQL,
                <<<'SQL'
                ALTER TABLE `profile` COMMENT 'café déjà vu'
                SQL,
            ],
        ];
    }
}

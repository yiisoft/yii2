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
            'column without existing comment' => [
                'yii2_mysql_qb_comment_add',
                'description',
                <<<SQL
                CREATE TABLE `yii2_mysql_qb_comment_add` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'Column comment.',
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_add` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'Column comment.'
                SQL,
            ],
            'comment with single quote' => [
                'yii2_mysql_qb_comment_quote',
                'description',
                <<<SQL
                CREATE TABLE `yii2_mysql_qb_comment_quote` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                "It's a column comment.",
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_quote` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'It\'s a column comment.'
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.yii2_mysql_qb_comment_qualified',
                'description',
                <<<SQL
                CREATE TABLE `yiitest`.`yii2_mysql_qb_comment_qualified` (
                  `description` varchar(255) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'Qualified table comment.',
                <<<SQL
                ALTER TABLE `yiitest`.`yii2_mysql_qb_comment_qualified` CHANGE `description` `description` varchar(255) NOT NULL COMMENT 'Qualified table comment.'
                SQL,
            ],
            'replace existing comment' => [
                'yii2_mysql_qb_comment_replace',
                'description',
                <<<SQL
                CREATE TABLE `yii2_mysql_qb_comment_replace` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                'New comment.',
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_replace` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT 'New comment.'
                SQL,
            ],
            'replace existing comment with single quote' => [
                'yii2_mysql_qb_comment_replace_quote',
                'description',
                <<<'SQL'
                CREATE TABLE `yii2_mysql_qb_comment_replace_quote` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'It\'s old.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                "It's a new column comment.",
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_replace_quote` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT 'It\'s a new column comment.'
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
                'yii2_mysql_qb_comment_drop',
                'description',
                <<<SQL
                CREATE TABLE `yii2_mysql_qb_comment_drop` (
                  `description` varchar(255) NOT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_drop` CHANGE `description` `description` varchar(255) NOT NULL COMMENT ''
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.yii2_mysql_qb_comment_drop_qualified',
                'description',
                <<<SQL
                CREATE TABLE `yiitest`.`yii2_mysql_qb_comment_drop_qualified` (
                  `description` varchar(255) DEFAULT NULL COMMENT 'Old comment.'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                SQL,
                <<<SQL
                ALTER TABLE `yiitest`.`yii2_mysql_qb_comment_drop_qualified` CHANGE `description` `description` varchar(255) DEFAULT NULL COMMENT ''
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
                'yii2_mysql_qb_comment_table_quote',
                "It's a table comment.",
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_table_quote` COMMENT 'It\'s a table comment.'
                SQL,
            ],
            'simple table name' => [
                'yii2_mysql_qb_comment_table',
                'A table comment.',
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_table` COMMENT 'A table comment.'
                SQL,
            ],
            'database-qualified table name' => [
                'yiitest.yii2_mysql_qb_comment_table_qualified',
                'Qualified table comment.',
                <<<SQL
                ALTER TABLE `yiitest`.`yii2_mysql_qb_comment_table_qualified` COMMENT 'Qualified table comment.'
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
                'yiitest.yii2_mysql_qb_comment_table_drop_qualified',
                <<<SQL
                ALTER TABLE `yiitest`.`yii2_mysql_qb_comment_table_drop_qualified` COMMENT ''
                SQL,
            ],
            'simple table name' => [
                'yii2_mysql_qb_comment_table_drop',
                <<<SQL
                ALTER TABLE `yii2_mysql_qb_comment_table_drop` COMMENT ''
                SQL,
            ],
        ];
    }
}

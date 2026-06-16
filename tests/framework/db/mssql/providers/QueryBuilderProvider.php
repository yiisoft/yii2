<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use Closure;
use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;
use yii\db\Expression;
use yii\db\QueryBuilder;
use yii\db\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\QueryBuilderTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderProvider
{
    /**
     * @return array<string, array{string, Closure}>
     */
    public static function defaultValuesProvider(): array
    {
        return [
            'add' => [
                <<<SQL
                ALTER TABLE [T_constraints_1] ADD CONSTRAINT [CN_default] DEFAULT 0 FOR [C_default]
                SQL,
                fn (QueryBuilder $qb): string => $qb->addDefaultValue(
                    'CN_default',
                    'T_constraints_1',
                    'C_default',
                    0,
                ),
            ],
            'drop' => [
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[T_constraints_1]'
                DECLARE @constraintName SYSNAME = N'CN_default'
                DECLARE @dropSql NVARCHAR(MAX)

                SELECT @dropSql = N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name])
                FROM [sys].[default_constraints] AS [dc]
                WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName, N'U')
                    AND [dc].[name] = @constraintName

                IF @dropSql IS NULL
                BEGIN
                    THROW 50000, 'Default constraint not found on table.', 1;
                END

                EXEC (@dropSql)
                SQL,
                fn (QueryBuilder $qb): string => $qb->dropDefaultValue(
                    'CN_default',
                    'T_constraints_1',
                ),
            ],
            'drop catalog-qualified' => [
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[yiitest].[dbo].[T_constraints_1]'
                DECLARE @constraintName SYSNAME = N'CN_default'
                DECLARE @dropSql NVARCHAR(MAX)

                SELECT @dropSql = N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name])
                FROM [sys].[default_constraints] AS [dc]
                WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName, N'U')
                    AND [dc].[name] = @constraintName

                IF @dropSql IS NULL
                BEGIN
                    THROW 50000, 'Default constraint not found on table.', 1;
                END

                EXEC (@dropSql)
                SQL,
                fn (QueryBuilder $qb): string => $qb->dropDefaultValue(
                    'CN_default',
                    'yiitest.dbo.T_constraints_1',
                ),
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function renameTable(): array
    {
        return [
            'already quoted table names' => [
                '[dbo].[old_table]',
                '[new_table]',
                <<<SQL
                EXEC sp_rename @objname = N'[dbo].[old_table]', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'curly brace table placeholders' => [
                '{{old_table}}',
                '{{new_table}}',
                <<<SQL
                EXEC sp_rename @objname = N'{{old_table}}', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'schema qualified new table name' => [
                'old_table',
                'dbo.new_table',
                <<<SQL
                EXEC sp_rename @objname = N'[old_table]', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'schema qualified old table name' => [
                'dbo.old_table',
                'new_table',
                <<<SQL
                EXEC sp_rename @objname = N'[dbo].[old_table]', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'simple table names' => [
                'old_table',
                'new_table',
                <<<SQL
                EXEC sp_rename @objname = N'[old_table]', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'square bracket placeholders' => [
                '[[old_table]]',
                '[[new_table]]',
                <<<SQL
                EXEC sp_rename @objname = N'[[old_table]]', @newname = N'new_table', @objtype = N'OBJECT'
                SQL,
            ],
            'table names with single quotes' => [
                "old'table",
                "new'table",
                <<<SQL
                EXEC sp_rename @objname = N'[old''table]', @newname = N'new''table', @objtype = N'OBJECT'
                SQL,
            ],
            'table names with spaces' => [
                'old table',
                'new table',
                <<<SQL
                EXEC sp_rename @objname = N'[old table]', @newname = N'new table', @objtype = N'OBJECT'
                SQL,
            ],
            'table names with unicode characters' => [
                'old_table',
                'new_ñ_表',
                <<<SQL
                EXEC sp_rename @objname = N'[old_table]', @newname = N'new_ñ_表', @objtype = N'OBJECT'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function renameColumn(): array
    {
        return [
            'already quoted names' => [
                '[dbo].[test_table]',
                '[old_col]',
                '[new_col]',
                <<<SQL
                EXEC sp_rename @objname = N'[dbo].[test_table].[old_col]', @newname = N'new_col', @objtype = N'COLUMN'
                SQL,
            ],
            'curly brace table and square bracket column placeholders' => [
                '{{test_table}}',
                '[[old_col]]',
                '[[new_col]]',
                <<<SQL
                EXEC sp_rename @objname = N'{{test_table}}.[[old_col]]', @newname = N'new_col', @objtype = N'COLUMN'
                SQL,
            ],
            'names with single quotes' => [
                "test'table",
                "old'col",
                "new'col",
                <<<SQL
                EXEC sp_rename @objname = N'[test''table].[old''col]', @newname = N'new''col', @objtype = N'COLUMN'
                SQL,
            ],
            'names with spaces' => [
                'test table',
                'old col',
                'new col',
                <<<SQL
                EXEC sp_rename @objname = N'[test table].[old col]', @newname = N'new col', @objtype = N'COLUMN'
                SQL,
            ],
            'names with unicode characters' => [
                'test_table',
                'old_ñ_表',
                'new_ñ_表',
                <<<SQL
                EXEC sp_rename @objname = N'[test_table].[old_ñ_表]', @newname = N'new_ñ_表', @objtype = N'COLUMN'
                SQL,
            ],
            'schema qualified new column name' => [
                'test_table',
                'old_col',
                'dbo.new_col',
                <<<SQL
                EXEC sp_rename @objname = N'[test_table].[old_col]', @newname = N'new_col', @objtype = N'COLUMN'
                SQL,
            ],
            'schema qualified table name' => [
                'dbo.test_table',
                'old_col',
                'new_col',
                <<<SQL
                EXEC sp_rename @objname = N'[dbo].[test_table].[old_col]', @newname = N'new_col', @objtype = N'COLUMN'
                SQL,
            ],
            'simple names' => [
                'test_table',
                'old_col',
                'new_col',
                <<<SQL
                EXEC sp_rename @objname = N'[test_table].[old_col]', @newname = N'new_col', @objtype = N'COLUMN'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, mixed, string}>
     */
    public static function addDefaultValue(): array
    {
        return [
            'expression default' => [
                'CN_default',
                'T_constraints_1',
                'C_timestamp',
                new Expression('CURRENT_TIMESTAMP'),
                <<<SQL
                ALTER TABLE [T_constraints_1] ADD CONSTRAINT [CN_default] DEFAULT CURRENT_TIMESTAMP FOR [C_timestamp]
                SQL,
            ],
            'false default' => [
                'CN_default',
                'T_constraints_1',
                'C_bit',
                false,
                <<<SQL
                ALTER TABLE [T_constraints_1] ADD CONSTRAINT [CN_default] DEFAULT 0 FOR [C_bit]
                SQL,
            ],
            'integer default' => [
                'CN_default',
                'T_constraints_1',
                'C_default',
                1,
                <<<SQL
                ALTER TABLE [T_constraints_1] ADD CONSTRAINT [CN_default] DEFAULT 1 FOR [C_default]
                SQL,
            ],
            'null default' => [
                'CN_default',
                'T_constraints_1',
                'C_default',
                null,
                <<<SQL
                ALTER TABLE [T_constraints_1] ADD CONSTRAINT [CN_default] DEFAULT NULL FOR [C_default]
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{Closure|string, string}>
     */
    public static function alterColumn(): array
    {
        return [
            'integer null with default expression' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->null()
                    ->defaultValue(new Expression('CAST(GETDATE() AS INT)')),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] int NULL
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT CAST(GETDATE() AS INT) FOR [bar]
                SQL,
            ],
            'integer null with null default value' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->null()
                    ->defaultValue(null),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] int NULL
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT NULL FOR [bar]
                SQL,
            ],
            'plain string type' => [
                'varchar(255)',
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] varchar(255)
                SQL,
            ],
            'string not null' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull(),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255) NOT NULL
                SQL,
            ],
            'string unique' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 30)
                    ->unique(),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(30)
                ALTER TABLE [foo1] ADD CONSTRAINT [UQ_foo1_bar] UNIQUE ([bar])
                SQL,
            ],
            'string with check constraint' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->check('LEN(bar) > 5'),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
                ALTER TABLE [foo1] ADD CONSTRAINT [CK_foo1_bar] CHECK (LEN(bar) > 5)
                SQL,
            ],
            'string with default value' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('AbCdE'),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 'AbCdE' FOR [bar]
                SQL,
            ],
            'string with empty default value' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue(''),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255)
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT '' FOR [bar]
                SQL,
            ],
            'timestamp with default expression' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_TIMESTAMP)
                    ->defaultExpression('CURRENT_TIMESTAMP'),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] ALTER COLUMN [bar] datetime
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT CURRENT_TIMESTAMP FOR [bar]
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, Closure|string, string}>
     */
    public static function alterColumnQualifiedTableNames(): array
    {
        return [
            'catalog-qualified plain string type' => [
                'yiitest.dbo.foo1',
                'varchar(255)',
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[yiitest].[dbo].[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [yiitest].[dbo].[foo1] ALTER COLUMN [bar] varchar(255)
                SQL,
            ],
            'catalog-qualified string with default value' => [
                'yiitest.dbo.foo1',
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('AbCdE'),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[yiitest].[dbo].[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [yiitest].[dbo].[foo1] ALTER COLUMN [bar] nvarchar(255)
                ALTER TABLE [yiitest].[dbo].[foo1] ADD CONSTRAINT [DF_yiitestdbofoo1_bar] DEFAULT 'AbCdE' FOR [bar]
                SQL,
            ],
            'catalog-qualified string with default check and unique constraints' => [
                'yiitest.dbo.foo1',
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                    ->defaultValue('AbCdE')
                    ->check('LEN(bar) > 3')
                    ->unique(),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[yiitest].[dbo].[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [yiitest].[dbo].[foo1] ALTER COLUMN [bar] nvarchar(64)
                ALTER TABLE [yiitest].[dbo].[foo1] ADD CONSTRAINT [DF_yiitestdbofoo1_bar] DEFAULT 'AbCdE' FOR [bar]
                ALTER TABLE [yiitest].[dbo].[foo1] ADD CONSTRAINT [CK_yiitestdbofoo1_bar] CHECK (LEN(bar) > 3)
                ALTER TABLE [yiitest].[dbo].[foo1] ADD CONSTRAINT [UQ_yiitestdbofoo1_bar] UNIQUE ([bar])
                SQL,
            ],
            'schema-qualified plain string type' => [
                'dbo.foo1',
                'varchar(255)',
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[dbo].[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [dbo].[foo1] ALTER COLUMN [bar] varchar(255)
                SQL,
            ],
            'schema-qualified string with default value' => [
                'dbo.foo1',
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('AbCdE'),
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[dbo].[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [dbo].[foo1] ALTER COLUMN [bar] nvarchar(255)
                ALTER TABLE [dbo].[foo1] ADD CONSTRAINT [DF_dbofoo1_bar] DEFAULT 'AbCdE' FOR [bar]
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dropColumn(): array
    {
        return [
            'column name with single quote' => [
                'foo1',
                "my'col",
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'my''col'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT DISTINCT N'ALTER TABLE '
                        + QUOTENAME(OBJECT_SCHEMA_NAME([fk].[parent_object_id])) + N'.'
                        + QUOTENAME(OBJECT_NAME([fk].[parent_object_id]))
                        + N' DROP CONSTRAINT ' + QUOTENAME([fk].[name]) AS [sql], 0 AS [ord]
                    FROM [sys].[foreign_keys] AS [fk]
                    JOIN [sys].[foreign_key_columns] AS [fkc] ON [fkc].[constraint_object_id]=[fk].[object_id]
                    JOIN [sys].[columns] AS [pc] ON [pc].[object_id]=[fkc].[parent_object_id] AND [pc].[column_id]=[fkc].[parent_column_id]
                    JOIN [sys].[columns] AS [rc] ON [rc].[object_id]=[fkc].[referenced_object_id] AND [rc].[column_id]=[fkc].[referenced_column_id]
                    WHERE ([fkc].[parent_object_id]=OBJECT_ID(@tableName) AND [pc].[name]=@columnName)
                        OR ([fkc].[referenced_object_id]=OBJECT_ID(@tableName) AND [rc].[name]=@columnName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'PK'
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] DROP COLUMN [my'col]
                SQL,
            ],
            'drops all constraint types for column' => [
                'foo1',
                'bar',
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[foo1]'
                DECLARE @columnName NVARCHAR(MAX) = N'bar'
                DECLARE @dropCommands NVARCHAR(MAX)

                SELECT @dropCommands = STRING_AGG(CONVERT(NVARCHAR(MAX), [cons].[sql]), N'; ') WITHIN GROUP (ORDER BY [cons].[ord], [cons].[sql])
                FROM (
                    SELECT DISTINCT N'ALTER TABLE '
                        + QUOTENAME(OBJECT_SCHEMA_NAME([fk].[parent_object_id])) + N'.'
                        + QUOTENAME(OBJECT_NAME([fk].[parent_object_id]))
                        + N' DROP CONSTRAINT ' + QUOTENAME([fk].[name]) AS [sql], 0 AS [ord]
                    FROM [sys].[foreign_keys] AS [fk]
                    JOIN [sys].[foreign_key_columns] AS [fkc] ON [fkc].[constraint_object_id]=[fk].[object_id]
                    JOIN [sys].[columns] AS [pc] ON [pc].[object_id]=[fkc].[parent_object_id] AND [pc].[column_id]=[fkc].[parent_column_id]
                    JOIN [sys].[columns] AS [rc] ON [rc].[object_id]=[fkc].[referenced_object_id] AND [rc].[column_id]=[fkc].[referenced_column_id]
                    WHERE ([fkc].[parent_object_id]=OBJECT_ID(@tableName) AND [pc].[name]=@columnName)
                        OR ([fkc].[referenced_object_id]=OBJECT_ID(@tableName) AND [rc].[name]=@columnName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([dc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[default_constraints] AS [dc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[dc].[parent_object_id] AND [c].[column_id]=[dc].[parent_column_id] AND [c].[name]=@columnName
                    WHERE [dc].[parent_object_id] = OBJECT_ID(@tableName)
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([cc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[check_constraints] AS [cc]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[cc].[parent_object_id] AND [c].[name]=@columnName
                    WHERE [cc].[parent_object_id] = OBJECT_ID(@tableName)
                        AND (
                            [cc].[parent_column_id]=[c].[column_id]
                            OR EXISTS (
                                SELECT 1
                                FROM [sys].[sql_expression_dependencies] AS [sed]
                                WHERE [sed].[referencing_class]=1 AND [sed].[referencing_id]=[cc].[object_id]
                                    AND [sed].[referenced_class]=1 AND [sed].[referenced_id]=[cc].[parent_object_id]
                                    AND [sed].[referenced_minor_id]=[c].[column_id]
                            )
                        )
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'PK'
                    UNION
                    SELECT N'ALTER TABLE ' + @tableName + N' DROP CONSTRAINT ' + QUOTENAME([kc].[name]) AS [sql], 1 AS [ord]
                    FROM [sys].[key_constraints] AS [kc]
                    JOIN [sys].[index_columns] AS [ic] ON [ic].[object_id]=[kc].[parent_object_id] AND [ic].[index_id]=[kc].[unique_index_id]
                    JOIN [sys].[columns] AS [c] ON [c].[object_id]=[kc].[parent_object_id] AND [c].[column_id]=[ic].[column_id] AND [c].[name]=@columnName
                    WHERE [kc].[parent_object_id] = OBJECT_ID(@tableName) AND [kc].[type] = N'UQ'
                ) AS [cons]

                IF @dropCommands IS NOT NULL
                    EXEC (@dropCommands)
                ALTER TABLE [foo1] DROP COLUMN [bar]
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{list<string>, list<string>, string, string}>
     */
    public static function dropColumnConstraintsOnDb(): array
    {
        return [
            'column check constraint' => [
                ['yii2_mssql_drop_column_check'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_column_check] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL CONSTRAINT [CK_yii2_mssql_drop_column_check_bar] CHECK ([bar] > 0)
                    )
                    SQL,
                ],
                'yii2_mssql_drop_column_check',
                'bar',
            ],
            'default constraint' => [
                ['yii2_mssql_drop_default'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_default] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL CONSTRAINT [DF_yii2_mssql_drop_default_bar] DEFAULT 1
                    )
                    SQL,
                ],
                'yii2_mssql_drop_default',
                'bar',
            ],
            'default constraint on column with single quote' => [
                ['yii2_mssql_drop_quote'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_quote] (
                        [id] [int] NOT NULL,
                        [my'col] [int] NULL CONSTRAINT [DF_yii2_mssql_drop_quote_col] DEFAULT 1
                    )
                    SQL,
                ],
                'yii2_mssql_drop_quote',
                "my'col",
            ],
            'foreign key constraint' => [
                ['yii2_mssql_drop_fk_child', 'yii2_mssql_drop_fk_parent'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_fk_parent] (
                        [id] [int] NOT NULL,
                        CONSTRAINT [PK_yii2_mssql_drop_fk_parent_id] PRIMARY KEY ([id])
                    )
                    SQL,
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_fk_child] (
                        [id] [int] NOT NULL,
                        [parent_id] [int] NULL,
                        CONSTRAINT [FK_yii2_mssql_drop_fk_child_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [yii2_mssql_drop_fk_parent] ([id])
                    )
                    SQL,
                ],
                'yii2_mssql_drop_fk_child',
                'parent_id',
            ],
            'multi column check constraint' => [
                ['yii2_mssql_drop_multi_check'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_multi_check] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL,
                        CONSTRAINT [CK_yii2_mssql_drop_multi_check_bar] CHECK ([bar] > [id])
                    )
                    SQL,
                ],
                'yii2_mssql_drop_multi_check',
                'bar',
            ],
            'primary key constraint' => [
                ['yii2_mssql_drop_pk'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_pk] (
                        [id] [int] NULL,
                        [bar] [int] NOT NULL,
                        CONSTRAINT [PK_yii2_mssql_drop_pk_bar] PRIMARY KEY ([bar])
                    )
                    SQL,
                ],
                'yii2_mssql_drop_pk',
                'bar',
            ],
            'referencing foreign key constraint' => [
                ['yii2_mssql_drop_ref_fk_child', 'yii2_mssql_drop_ref_fk_parent'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_ref_fk_parent] (
                        [id] [int] NOT NULL,
                        [name] [varchar](32) NULL,
                        CONSTRAINT [PK_yii2_mssql_drop_ref_fk_parent_id] PRIMARY KEY ([id])
                    )
                    SQL,
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_ref_fk_child] (
                        [id] [int] NOT NULL,
                        [parent_id] [int] NULL,
                        CONSTRAINT [FK_yii2_mssql_drop_ref_fk_child_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [yii2_mssql_drop_ref_fk_parent] ([id])
                    )
                    SQL,
                ],
                'yii2_mssql_drop_ref_fk_parent',
                'id',
            ],
            'unique constraint' => [
                ['yii2_mssql_drop_unique'],
                [
                    <<<SQL
                    CREATE TABLE [yii2_mssql_drop_unique] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL,
                        CONSTRAINT [UQ_yii2_mssql_drop_unique_bar] UNIQUE ([bar])
                    )
                    SQL,
                ],
                'yii2_mssql_drop_unique',
                'bar',
            ],
        ];
    }
}

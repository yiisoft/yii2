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
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\db\Schema;
use yiiunit\base\db\BaseQueryBuilder;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\QueryBuilderTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class QueryBuilderProvider
{
    /**
     * @return array<string, array{Closure, string, array<string, mixed>}>
     */
    public static function zeroLimitQueries(): array
    {
        return [
            'normal SELECT with parameters' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('customer')
                    ->where(['status' => 2])
                    ->limit(0),
                <<<SQL
                SELECT TOP (0) [id] FROM [customer] WHERE [status]=:qp0
                SQL,
                [':qp0' => 2],
            ],
            'SELECT with offset' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->from('customer')
                    ->limit(0)
                    ->offset(5),
                <<<SQL
                SELECT TOP (0) [id] FROM [customer]
                SQL,
                [],
            ],
            'SELECT DISTINCT' => [
                static fn (): Query => (new Query())
                    ->select('id')
                    ->distinct()
                    ->from('customer')
                    ->limit(0),
                <<<SQL
                SELECT DISTINCT TOP (0) [id] FROM [customer]
                SQL,
                [],
            ],
            'unnamed expression' => [
                static fn (): Query => (new Query())
                    ->select(new Expression('1 + 1'))
                    ->limit(0),
                <<<SQL
                SELECT TOP (0) 1 + 1
                SQL,
                [],
            ],
            'unnamed aggregate' => [
                static fn (): Query => (new Query())
                    ->select(new Expression('COUNT(*)'))
                    ->from('customer')
                    ->limit(0),
                <<<SQL
                SELECT TOP (0) COUNT(*) FROM [customer]
                SQL,
                [],
            ],
            'self-join with duplicate column names' => [
                static fn (): Query => (new Query())
                    ->select(['c1.id', 'c2.id'])
                    ->from(['c1' => 'customer'])
                    ->innerJoin(
                        ['c2' => 'customer'],
                        ['c1.id' => new Expression('[c2].[id]')],
                    )
                    ->limit(0),
                <<<SQL
                SELECT TOP (0) [c1].[id], [c2].[id] FROM [customer] [c1] INNER JOIN [customer] [c2] ON [c1].[id]=[c2].[id]
                SQL,
                [],
            ],
            'outer SELECT with CTE' => [
                static fn (): Query => (new Query())
                    ->withQuery(
                        (new Query())
                            ->select('id')
                            ->from('customer'),
                        'customers',
                    )
                    ->select('id')
                    ->from('customers')
                    ->limit(0),
                <<<SQL
                WITH customers AS (SELECT [id] FROM [customer]) SELECT TOP (0) [id] FROM [customers]
                SQL,
                [],
            ],
        ];
    }

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
     * @return array<string, array{bool, string, string, string}>
     */
    public static function checkIntegrity(): array
    {
        return [
            'all tables, catalog-qualified schema, enable' => [
                true,
                'yiitest.dbo',
                '',
                <<<SQL
                DECLARE @catalogName SYSNAME = N'yiitest'
                DECLARE @schemaName SYSNAME = N'dbo'
                DECLARE @sql NVARCHAR(MAX)

                SELECT @sql = STRING_AGG(
                    CONVERT(
                        NVARCHAR(MAX),
                        N'ALTER TABLE '
                            + COALESCE(QUOTENAME(@catalogName) + N'.', N'')
                            + QUOTENAME(@schemaName)
                            + N'.'
                            + QUOTENAME([t].[name])
                            + N' WITH CHECK CHECK CONSTRAINT ALL'
                    ),
                    N'; '
                )
                FROM [yiitest].[sys].[tables] AS [t]
                INNER JOIN [yiitest].[sys].[schemas] AS [s] ON [s].[schema_id] = [t].[schema_id]
                WHERE [s].[name] = @schemaName

                IF @sql IS NOT NULL
                    EXEC (@sql)
                SQL,
            ],
            'all tables, default schema, disable' => [
                false,
                '',
                '',
                <<<SQL
                DECLARE @catalogName SYSNAME = NULL
                DECLARE @schemaName SYSNAME = N'dbo'
                DECLARE @sql NVARCHAR(MAX)

                SELECT @sql = STRING_AGG(
                    CONVERT(
                        NVARCHAR(MAX),
                        N'ALTER TABLE '
                            + COALESCE(QUOTENAME(@catalogName) + N'.', N'')
                            + QUOTENAME(@schemaName)
                            + N'.'
                            + QUOTENAME([t].[name])
                            + N' NOCHECK CONSTRAINT ALL'
                    ),
                    N'; '
                )
                FROM [sys].[tables] AS [t]
                INNER JOIN [sys].[schemas] AS [s] ON [s].[schema_id] = [t].[schema_id]
                WHERE [s].[name] = @schemaName

                IF @sql IS NOT NULL
                    EXEC (@sql)
                SQL,
            ],
            'all tables, default schema, enable' => [
                true,
                '',
                '',
                <<<SQL
                DECLARE @catalogName SYSNAME = NULL
                DECLARE @schemaName SYSNAME = N'dbo'
                DECLARE @sql NVARCHAR(MAX)

                SELECT @sql = STRING_AGG(
                    CONVERT(
                        NVARCHAR(MAX),
                        N'ALTER TABLE '
                            + COALESCE(QUOTENAME(@catalogName) + N'.', N'')
                            + QUOTENAME(@schemaName)
                            + N'.'
                            + QUOTENAME([t].[name])
                            + N' WITH CHECK CHECK CONSTRAINT ALL'
                    ),
                    N'; '
                )
                FROM [sys].[tables] AS [t]
                INNER JOIN [sys].[schemas] AS [s] ON [s].[schema_id] = [t].[schema_id]
                WHERE [s].[name] = @schemaName

                IF @sql IS NOT NULL
                    EXEC (@sql)
                SQL,
            ],
            'single table, catalog-qualified' => [
                true,
                '',
                'yiitest.dbo.customer',
                <<<SQL
                ALTER TABLE [yiitest].[dbo].[customer] WITH CHECK CHECK CONSTRAINT ALL
                SQL,
            ],
            'single table, disable' => [
                false,
                '',
                'customer',
                <<<SQL
                ALTER TABLE [customer] NOCHECK CONSTRAINT ALL
                SQL,
            ],
            'single table, enable' => [
                true,
                '',
                'customer',
                <<<SQL
                ALTER TABLE [customer] WITH CHECK CHECK CONSTRAINT ALL
                SQL,
            ],
            'single table, explicit schema' => [
                true,
                'dbo',
                'customer',
                <<<SQL
                ALTER TABLE [dbo].[customer] WITH CHECK CHECK CONSTRAINT ALL
                SQL,
            ],
            'single table, single-quoted identifiers' => [
                true,
                '',
                "yiit's.dbo.customer's",
                <<<SQL
                ALTER TABLE [yiit's].[dbo].[customer's] WITH CHECK CHECK CONSTRAINT ALL
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, int|null, string}>
     */
    public static function resetSequence(): array
    {
        return [
            'already quoted table name' => [
                '[item]',
                2,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = 2
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'catalog-qualified table name' => [
                'yiitest.dbo.item',
                null,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[yiitest].[dbo].[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = NULL
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [yiitest].[sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'default next value from existing rows' => [
                'item',
                null,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = NULL
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'explicit first value after identity use' => [
                'item',
                1,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = 1
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'explicit next value' => [
                'item',
                4,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = 4
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'explicit maximum integer next value' => [
                'item',
                9223372036854775807,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = 9223372036854775807
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
                SQL,
            ],
            'schema-qualified table name' => [
                'dbo.item',
                7,
                <<<SQL
                DECLARE @tableName NVARCHAR(MAX) = N'[dbo].[item]'
                DECLARE @requestedNextValue DECIMAL(38, 0) = 7
                DECLARE @identityColumn SYSNAME
                DECLARE @seedValue DECIMAL(38, 0)
                DECLARE @incrementValue DECIMAL(38, 0)
                DECLARE @lastValue DECIMAL(38, 0)
                DECLARE @maxValue DECIMAL(38, 0)
                DECLARE @reseedValue DECIMAL(38, 0)
                DECLARE @maxSql NVARCHAR(MAX)
                DECLARE @checkIdentSql NVARCHAR(MAX)

                SELECT
                    @identityColumn = [name],
                    @seedValue = CONVERT(DECIMAL(38, 0), [seed_value]),
                    @incrementValue = CONVERT(DECIMAL(38, 0), [increment_value]),
                    @lastValue = CONVERT(DECIMAL(38, 0), [last_value])
                FROM [sys].[identity_columns]
                WHERE [object_id] = OBJECT_ID(@tableName, N'U')

                IF @identityColumn IS NULL
                BEGIN
                    THROW 50000, 'Identity column not found on table.', 1;
                END

                SET @maxSql = N'SELECT @maxValue = CONVERT(DECIMAL(38, 0), MAX('
                    + QUOTENAME(@identityColumn)
                    + N')) FROM '
                    + @tableName

                EXEC sp_executesql
                    @maxSql,
                    N'@maxValue DECIMAL(38, 0) OUTPUT',
                    @maxValue OUTPUT

                SET @reseedValue = CASE
                    WHEN @requestedNextValue IS NOT NULL AND (@maxValue IS NOT NULL OR @lastValue IS NOT NULL)
                        THEN @requestedNextValue - @incrementValue
                    WHEN @requestedNextValue IS NOT NULL
                        THEN @requestedNextValue
                    WHEN @maxValue IS NOT NULL
                        THEN @maxValue
                    WHEN @lastValue IS NOT NULL
                        THEN @seedValue - @incrementValue
                    ELSE @seedValue
                END

                SET @checkIdentSql = N'DBCC CHECKIDENT (N'''
                    + REPLACE(@tableName, '''', '''''')
                    + N''', RESEED, '
                    + CONVERT(NVARCHAR(50), @reseedValue)
                    + N')'

                EXEC (@checkIdentSql)
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
            'catalog-qualified table name' => [
                'yiitest.dbo.profile',
                'A profile comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM [yiitest].sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC [yiitest].sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                ELSE
                    EXEC [yiitest].sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'schema-qualified table name' => [
                'myschema.profile',
                'A profile comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'myschema',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'simple table name' => [
                'profile',
                'A profile comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A profile comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'table name and comment with single quotes' => [
                'stranger\'s table',
                'It\'s a table comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'stranger''s table',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'It''s a table comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'It''s a table comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function addCommentOnColumn(): array
    {
        return [
            'catalog-qualified table name' => [
                'yiitest.dbo.profile',
                'description',
                'A column comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM [yiitest].sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC [yiitest].sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                ELSE
                    EXEC [yiitest].sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                SQL,
            ],
            'column and comment with single quotes' => [
                'stranger\'s table',
                'stranger\'s field',
                'It\'s a column comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'stranger''s table',
                        'COLUMN', N'stranger''s field'
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'It''s a column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table',
                        @level2type = 'COLUMN', @level2name = N'stranger''s field'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'It''s a column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table',
                        @level2type = 'COLUMN', @level2name = N'stranger''s field'
                SQL,
            ],
            'schema-qualified table name' => [
                'myschema.profile',
                'description',
                'A column comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'myschema',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                SQL,
            ],
            'simple table name' => [
                'profile',
                'description',
                'A column comment.',
                <<<SQL
                IF NOT EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC sys.sp_addextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                ELSE
                    EXEC sys.sp_updateextendedproperty
                        @name = N'MS_Description',
                        @value = N'A column comment.',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
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
            'catalog-qualified table name' => [
                'yiitest.dbo.profile',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM [yiitest].sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC [yiitest].sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'schema-qualified table name' => [
                'myschema.profile',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'myschema',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'simple table name' => [
                'profile',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile'
                SQL,
            ],
            'table name with single quote' => [
                'stranger\'s table',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'stranger''s table',
                        DEFAULT, DEFAULT
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table'
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dropCommentFromColumn(): array
    {
        return [
            'catalog-qualified table name' => [
                'yiitest.dbo.profile',
                'description',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM [yiitest].sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC [yiitest].sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                SQL,
            ],
            'column with single quote' => [
                'stranger\'s table',
                'stranger\'s field',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'stranger''s table',
                        'COLUMN', N'stranger''s field'
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'stranger''s table',
                        @level2type = 'COLUMN', @level2name = N'stranger''s field'
                SQL,
            ],
            'simple table name' => [
                'profile',
                'description',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'dbo',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'dbo',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                SQL,
            ],
            'schema-qualified table name' => [
                'myschema.profile',
                'description',
                <<<SQL
                IF EXISTS (
                    SELECT 1
                    FROM sys.fn_listextendedproperty(
                        N'MS_Description',
                        'SCHEMA', N'myschema',
                        'TABLE', N'profile',
                        'COLUMN', N'description'
                    )
                )
                    EXEC sys.sp_dropextendedproperty
                        @name = N'MS_Description',
                        @level0type = 'SCHEMA', @level0name = N'myschema',
                        @level1type = 'TABLE', @level1name = N'profile',
                        @level2type = 'COLUMN', @level2name = N'description'
                SQL,
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
            'abstract type string' => [
                'string',
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
                SQL,
            ],
            'abstract type string not null passthrough' => [
                'string NOT NULL',
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
            'builder check' => [
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
            'builder default expression function' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DATETIME)
                    ->defaultExpression('GETDATE()'),
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
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT GETDATE() FOR [bar]
                SQL,
            ],
            'builder default expression with comma' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DATETIME)
                    ->defaultExpression('DATEADD(day, 1, GETDATE())'),
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
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT DATEADD(day, 1, GETDATE()) FOR [bar]
                SQL,
            ],
            'builder empty string default' => [
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
            'builder expression object default' => [
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
            'builder float default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_DOUBLE)
                    ->defaultValue(1.5),
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
                ALTER TABLE [foo1] ALTER COLUMN [bar] float
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 1.5 FOR [bar]
                SQL,
            ],
            'builder integer default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                    ->defaultValue(42),
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
                ALTER TABLE [foo1] ALTER COLUMN [bar] int
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 42 FOR [bar]
                SQL,
            ],
            'builder not null' => [
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
            'builder not null with default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->notNull()
                    ->defaultValue('hello world'),
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
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 'hello world' FOR [bar]
                SQL,
            ],
            'builder null' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->null(),
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
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255) NULL
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT NULL FOR [bar]
                SQL,
            ],
            'builder null default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
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
                ALTER TABLE [foo1] ALTER COLUMN [bar] nvarchar(255) NULL
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT NULL FOR [bar]
                SQL,
            ],
            'builder scalar default' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255)
                    ->defaultValue('hello world'),
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
                ALTER TABLE [foo1] ADD CONSTRAINT [DF_foo1_bar] DEFAULT 'hello world' FOR [bar]
                SQL,
            ],
            'builder type only' => [
                static fn (Connection $db): ColumnSchemaBuilder => $db->getSchema()
                    ->createColumnSchemaBuilder(Schema::TYPE_STRING, 255),
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
                SQL,
            ],
            'builder unique' => [
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
            'native type string' => [
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
            'string type drops existing check' => [
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
            'string type drops existing default' => [
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
            'string unique start dropped' => [
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
                ['drop_column_check'],
                [
                    <<<SQL
                    CREATE TABLE [drop_column_check] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL CONSTRAINT [CK_drop_column_check_bar] CHECK ([bar] > 0)
                    )
                    SQL,
                ],
                'drop_column_check',
                'bar',
            ],
            'default constraint' => [
                ['drop_default'],
                [
                    <<<SQL
                    CREATE TABLE [drop_default] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL CONSTRAINT [DF_drop_default_bar] DEFAULT 1
                    )
                    SQL,
                ],
                'drop_default',
                'bar',
            ],
            'default constraint on column with single quote' => [
                ['drop_quote'],
                [
                    <<<SQL
                    CREATE TABLE [drop_quote] (
                        [id] [int] NOT NULL,
                        [my'col] [int] NULL CONSTRAINT [DF_drop_quote_col] DEFAULT 1
                    )
                    SQL,
                ],
                'drop_quote',
                "my'col",
            ],
            'foreign key constraint' => [
                ['drop_fk_child', 'drop_fk_parent'],
                [
                    <<<SQL
                    CREATE TABLE [drop_fk_parent] (
                        [id] [int] NOT NULL,
                        CONSTRAINT [PK_drop_fk_parent_id] PRIMARY KEY ([id])
                    )
                    SQL,
                    <<<SQL
                    CREATE TABLE [drop_fk_child] (
                        [id] [int] NOT NULL,
                        [parent_id] [int] NULL,
                        CONSTRAINT [FK_drop_fk_child_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [drop_fk_parent] ([id])
                    )
                    SQL,
                ],
                'drop_fk_child',
                'parent_id',
            ],
            'multi column check constraint' => [
                ['drop_multi_check'],
                [
                    <<<SQL
                    CREATE TABLE [drop_multi_check] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL,
                        CONSTRAINT [CK_drop_multi_check_bar] CHECK ([bar] > [id])
                    )
                    SQL,
                ],
                'drop_multi_check',
                'bar',
            ],
            'primary key constraint' => [
                ['drop_pk'],
                [
                    <<<SQL
                    CREATE TABLE [drop_pk] (
                        [id] [int] NULL,
                        [bar] [int] NOT NULL,
                        CONSTRAINT [PK_drop_pk_bar] PRIMARY KEY ([bar])
                    )
                    SQL,
                ],
                'drop_pk',
                'bar',
            ],
            'referencing foreign key constraint' => [
                ['drop_ref_fk_child', 'drop_ref_fk_parent'],
                [
                    <<<SQL
                    CREATE TABLE [drop_ref_fk_parent] (
                        [id] [int] NOT NULL,
                        [name] [varchar](32) NULL,
                        CONSTRAINT [PK_drop_ref_fk_parent_id] PRIMARY KEY ([id])
                    )
                    SQL,
                    <<<SQL
                    CREATE TABLE [drop_ref_fk_child] (
                        [id] [int] NOT NULL,
                        [parent_id] [int] NULL,
                        CONSTRAINT [FK_drop_ref_fk_child_parent_id] FOREIGN KEY ([parent_id]) REFERENCES [drop_ref_fk_parent] ([id])
                    )
                    SQL,
                ],
                'drop_ref_fk_parent',
                'id',
            ],
            'unique constraint' => [
                ['drop_unique'],
                [
                    <<<SQL
                    CREATE TABLE [drop_unique] (
                        [id] [int] NOT NULL,
                        [bar] [int] NULL,
                        CONSTRAINT [UQ_drop_unique_bar] UNIQUE ([bar])
                    )
                    SQL,
                ],
                'drop_unique',
                'bar',
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<string, mixed>|Query, array<string, mixed>|bool, string, array<string, mixed>}>
     */
    public static function upsert(): array
    {
        return [
            'catalog-qualified table name' => [
                'yiitest.dbo.T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                true,
                <<<SQL
                MERGE [yiitest].[dbo].[T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([yiitest].[dbo].[T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=[EXCLUDED].[address], [status]=[EXCLUDED].[status], [profile_id]=[EXCLUDED].[profile_id] WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'no columns to update' => [
                'T_upsert_1',
                [
                    'a' => 1,
                ],
                true,
                <<<SQL
                MERGE [T_upsert_1] WITH (HOLDLOCK) USING (VALUES (:qp0)) AS [EXCLUDED] ([a]) ON ([T_upsert_1].[a]=[EXCLUDED].[a]) WHEN NOT MATCHED THEN INSERT ([a]) VALUES ([EXCLUDED].[a]);
                SQL,
                [
                    ':qp0' => 1,
                ],
            ],
            'query' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                true,
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [status]=[EXCLUDED].[status] WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);
                SQL,
                [
                    ':qp0' => 'user1',
                ],
            ],
            'query values and expressions with update part' => [
                '{{%T_upsert}}',
                (new Query())
                    ->select(
                        [
                            'email' => new Expression(':phEmail', [':phEmail' => 'dynamic@example.com']),
                            '[[time]]' => new Expression('now()'),
                        ],
                    ),
                [
                    'ts' => 0,
                    '[[orders]]' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [ts]=:qp1, [[orders]]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);
                SQL,
                [
                    ':phEmail' => 'dynamic@example.com',
                    ':qp1' => 0,
                ],
            ],
            'query values and expressions without update part' => [
                '{{%T_upsert}}',
                (new Query())
                    ->select(
                        [
                            'email' => new Expression(':phEmail', [':phEmail' => 'dynamic@example.com']),
                            '[[time]]' => new Expression('now()'),
                        ],
                    ),
                false,
                <<<SQL
                MERGE {{%T_upsert}} WITH (HOLDLOCK) USING (SELECT :phEmail AS [email], now() AS [[time]]) AS [EXCLUDED] ([email], [[time]]) ON ({{%T_upsert}}.[email]=[EXCLUDED].[email]) WHEN NOT MATCHED THEN INSERT ([email], [[time]]) VALUES ([EXCLUDED].[email], [EXCLUDED].[[time]]);
                SQL,
                [
                    ':phEmail' => 'dynamic@example.com',
                ],
            ],
            'query with update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                [
                    'address' => 'foo {{city}}',
                    'status' => 2,
                    'orders' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=:qp1, [status]=:qp2, [orders]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);
                SQL,
                [
                    ':qp0' => 'user1',
                    ':qp1' => 'foo {{city}}',
                    ':qp2' => 2,
                ],
            ],
            'query without update part' => [
                'T_upsert',
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer')
                    ->where(['name' => 'user1'])
                    ->limit(1),
                false,
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (SELECT [email], 2 AS [status] FROM [customer] WHERE [name]=:qp0 ORDER BY (SELECT NULL) OFFSET 0 ROWS FETCH NEXT 1 ROWS ONLY) AS [EXCLUDED] ([email], [status]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN NOT MATCHED THEN INSERT ([email], [status]) VALUES ([EXCLUDED].[email], [EXCLUDED].[status]);
                SQL,
                [
                    ':qp0' => 'user1',
                ],
            ],
            'regular values' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                true,
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=[EXCLUDED].[address], [status]=[EXCLUDED].[status], [profile_id]=[EXCLUDED].[profile_id] WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'regular values with update part' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                [
                    'address' => 'foo {{city}}',
                    'status' => 2,
                    'orders' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN MATCHED THEN UPDATE SET [address]=:qp4, [status]=:qp5, [orders]=T_upsert.orders + 1 WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                    ':qp4' => 'foo {{city}}',
                    ':qp5' => 2,
                ],
            ],
            'regular values without update part' => [
                'T_upsert',
                [
                    'email' => 'test@example.com',
                    'address' => 'bar {{city}}',
                    'status' => 1,
                    'profile_id' => null,
                ],
                false,
                <<<SQL
                MERGE [T_upsert] WITH (HOLDLOCK) USING (VALUES (:qp0, :qp1, :qp2, :qp3)) AS [EXCLUDED] ([email], [address], [status], [profile_id]) ON ([T_upsert].[email]=[EXCLUDED].[email]) WHEN NOT MATCHED THEN INSERT ([email], [address], [status], [profile_id]) VALUES ([EXCLUDED].[email], [EXCLUDED].[address], [EXCLUDED].[status], [EXCLUDED].[profile_id]);
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'bar {{city}}',
                    ':qp2' => 1,
                    ':qp3' => null,
                ],
            ],
            'values and expressions' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                true,
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
            'values and expressions with update part' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                [
                    '[[orders]]' => new Expression('T_upsert.orders + 1'),
                ],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
            'values and expressions without update part' => [
                '{{%T_upsert}}',
                [
                    '{{%T_upsert}}.[[email]]' => 'dynamic@example.com',
                    '[[ts]]' => new Expression('now()'),
                ],
                false,
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [ts] int NULL, [email] varchar(128) , [recovery_email] varchar(128) NULL, [address] text NULL, [status] tinyint , [orders] int , [profile_id] int NULL);INSERT INTO {{%T_upsert}} ({{%T_upsert}}.[[email]], [[ts]]) OUTPUT INSERTED.[id],INSERTED.[ts],INSERTED.[email],INSERTED.[recovery_email],INSERTED.[address],INSERTED.[status],INSERTED.[orders],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, now());SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => 'dynamic@example.com',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<string, mixed>|Query, array<string, mixed>, string, array<string, mixed>, 5?: bool}>
     */
    public static function insert(): array
    {
        return [
            'carry passed params' => [
                'customer',
                [
                    'email' => 'test@example.com',
                    'name' => 'sergeymakinen',
                    'address' => '{{city}}',
                    'is_active' => false,
                    'related_id' => null,
                    'col' => new Expression('CONCAT(:phFoo, :phBar)', [':phFoo' => 'foo']),
                ],
                [':phBar' => 'bar'],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id], [col]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp1, :qp2, :qp3, :qp4, :qp5, CONCAT(:phFoo, :phBar));SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':phBar' => 'bar',
                    ':qp1' => 'test@example.com',
                    ':qp2' => 'sergeymakinen',
                    ':qp3' => '{{city}}',
                    ':qp4' => false,
                    ':qp5' => null,
                    ':phFoo' => 'foo',
                ],
            ],
            'carry passed params (query)' => [
                'customer',
                (new Query())
                    ->select([
                        'email',
                        'name',
                        'address',
                        'is_active',
                        'related_id',
                    ])
                    ->from('customer')
                    ->where([
                        'email' => 'test@example.com',
                        'name' => 'sergeymakinen',
                        'address' => '{{city}}',
                        'is_active' => false,
                        'related_id' => null,
                        'col' => new Expression('CONCAT(:phFoo, :phBar)', [':phFoo' => 'foo']),
                    ]),
                [':phBar' => 'bar'],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted SELECT [email], [name], [address], [is_active], [related_id] FROM [customer] WHERE ([email]=:qp1) AND ([name]=:qp2) AND ([address]=:qp3) AND ([is_active]=:qp4) AND ([related_id] IS NULL) AND ([col]=CONCAT(:phFoo, :phBar));SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':phBar' => 'bar',
                    ':qp1' => 'test@example.com',
                    ':qp2' => 'sergeymakinen',
                    ':qp3' => '{{city}}',
                    ':qp4' => false,
                    ':phFoo' => 'foo',
                ],
            ],
            'catalog-qualified table name' => [
                'yiitest.dbo.customer',
                [
                    'email' => 'test@example.com',
                    'name' => 'silverfire',
                    'address' => 'Kyiv {{city}}, Ukraine',
                    'is_active' => false,
                    'related_id' => null,
                ],
                [],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);INSERT INTO [yiitest].[dbo].[customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, :qp1, :qp2, :qp3, :qp4);SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'silverfire',
                    ':qp2' => 'Kyiv {{city}}, Ukraine',
                    ':qp3' => false,
                    ':qp4' => null,
                ],
            ],
            'params-and-expressions' => [
                '{{%type}}',
                [
                    '{{%type}}.[[related_id]]' => null,
                    '[[time]]' => new Expression('now()'),
                ],
                [],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([int_col] int , [int_col2] int NULL, [tinyint_col] tinyint NULL, [smallint_col] smallint NULL, [char_col] char(100) , [char_col2] varchar(100) NULL, [char_col3] text NULL, [float_col] decimal(4,3) , [float_col2] float NULL, [blob_col] varbinary(max) NULL, [numeric_col] decimal(5,2) NULL, [time] datetime , [bool_col] tinyint , [bool_col2] tinyint NULL);INSERT INTO {{%type}} ({{%type}}.[[related_id]], [[time]]) OUTPUT INSERTED.[int_col],INSERTED.[int_col2],INSERTED.[tinyint_col],INSERTED.[smallint_col],INSERTED.[char_col],INSERTED.[char_col2],INSERTED.[char_col3],INSERTED.[float_col],INSERTED.[float_col2],INSERTED.[blob_col],INSERTED.[numeric_col],INSERTED.[time],INSERTED.[bool_col],INSERTED.[bool_col2] INTO @temporary_inserted VALUES (:qp0, now());SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => null,
                ],
                false,
            ],
            'regular-values' => [
                'customer',
                [
                    'email' => 'test@example.com',
                    'name' => 'silverfire',
                    'address' => 'Kyiv {{city}}, Ukraine',
                    'is_active' => false,
                    'related_id' => null,
                ],
                [],
                <<<SQL
                SET NOCOUNT ON;DECLARE @temporary_inserted TABLE ([id] int , [email] varchar(128) , [name] varchar(128) NULL, [address] text NULL, [status] int NULL, [profile_id] int NULL);INSERT INTO [customer] ([email], [name], [address], [is_active], [related_id]) OUTPUT INSERTED.[id],INSERTED.[email],INSERTED.[name],INSERTED.[address],INSERTED.[status],INSERTED.[profile_id] INTO @temporary_inserted VALUES (:qp0, :qp1, :qp2, :qp3, :qp4);SELECT * FROM @temporary_inserted
                SQL,
                [
                    ':qp0' => 'test@example.com',
                    ':qp1' => 'silverfire',
                    ':qp2' => 'Kyiv {{city}}, Ukraine',
                    ':qp3' => false,
                    ':qp4' => null,
                ],
            ],
        ];
    }

    /**
     * @return array<array-key, array{string, list<string>, array<int, list<mixed>>, string, 4?: bool}>
     */
    public static function batchInsert(): array
    {
        $data = BaseQueryBuilder::batchInsertProvider();

        $data['bool-false, bool2-null'][3] = <<<SQL
        INSERT INTO [type] ([bool_col], [bool_col2]) VALUES (0, NULL)
        SQL;
        $data['bool-false, time-now()'][3] = <<<SQL
        INSERT INTO {{%type}} ({{%type}}.[[bool_col]], [[time]]) VALUES (0, now())
        SQL;
        $data['escape-danger-chars'][3] = <<<SQL
        INSERT INTO [customer] ([address]) VALUES ('SQL-danger chars are escaped: ''); --')
        SQL;

        return $data;
    }

    /**
     * @return list<array{string, string}>
     */
    public static function buildFrom(): array
    {
        $data = BaseQueryBuilder::buildFromDataProvider();

        $data[] = ['[test]', '[[test]]'];
        $data[] = ['[test] [t1]', '[[test]] [[t1]]'];
        $data[] = ['[table.name]', '[[table.name]]'];
        $data[] = ['[table.name.with.dots]', '[[table.name.with.dots]]'];
        $data[] = ['[table name]', '[[table name]]'];
        $data[] = ['[table name with spaces]', '[[table name with spaces]]'];

        return $data;
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function createTableWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified name' => [
                'yiitest.dbo.T_migration',
                <<<SQL
                CREATE TABLE [yiitest].[dbo].[T_migration] (
                \t[id] int IDENTITY PRIMARY KEY
                )
                SQL,
            ],
            'schema-qualified bracket-quoted name' => [
                '[dbo].[T_migration]',
                <<<SQL
                CREATE TABLE [dbo].[T_migration] (
                \t[id] int IDENTITY PRIMARY KEY
                )
                SQL,
            ],
            'schema-qualified plain name' => [
                'dbo.T_migration',
                <<<SQL
                CREATE TABLE [dbo].[T_migration] (
                \t[id] int IDENTITY PRIMARY KEY
                )
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function addColumnWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified name' => [
                'yiitest.dbo.T_migration',
                <<<SQL
                ALTER TABLE [yiitest].[dbo].[T_migration] ADD [label] nvarchar(255)
                SQL,
            ],
            'schema-qualified bracket-quoted name' => [
                '[dbo].[T_migration]',
                <<<SQL
                ALTER TABLE [dbo].[T_migration] ADD [label] nvarchar(255)
                SQL,
            ],
            'schema-qualified plain name' => [
                'dbo.T_migration',
                <<<SQL
                ALTER TABLE [dbo].[T_migration] ADD [label] nvarchar(255)
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function createIndexWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified name' => [
                'yiitest.dbo.T_migration',
                <<<SQL
                CREATE INDEX [idx_label] ON [yiitest].[dbo].[T_migration] ([label])
                SQL,
            ],
            'schema-qualified bracket-quoted name' => [
                '[dbo].[T_migration]',
                <<<SQL
                CREATE INDEX [idx_label] ON [dbo].[T_migration] ([label])
                SQL,
            ],
            'schema-qualified plain name' => [
                'dbo.T_migration',
                <<<SQL
                CREATE INDEX [idx_label] ON [dbo].[T_migration] ([label])
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function addPrimaryKeyWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified name' => [
                'yiitest.dbo.T_migration',
                <<<SQL
                ALTER TABLE [yiitest].[dbo].[T_migration] ADD CONSTRAINT [pk_T_migration] PRIMARY KEY ([id])
                SQL,
            ],
            'schema-qualified bracket-quoted name' => [
                '[dbo].[T_migration]',
                <<<SQL
                ALTER TABLE [dbo].[T_migration] ADD CONSTRAINT [pk_T_migration] PRIMARY KEY ([id])
                SQL,
            ],
            'schema-qualified plain name' => [
                'dbo.T_migration',
                <<<SQL
                ALTER TABLE [dbo].[T_migration] ADD CONSTRAINT [pk_T_migration] PRIMARY KEY ([id])
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function addForeignKeyWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified names' => [
                'yiitest.dbo.T_child',
                'yiitest.dbo.T_parent',
                <<<SQL
                ALTER TABLE [yiitest].[dbo].[T_child] ADD CONSTRAINT [fk_child_parent] FOREIGN KEY ([parent_id]) REFERENCES [yiitest].[dbo].[T_parent] ([id])
                SQL,
            ],
            'schema-qualified bracket-quoted names' => [
                '[dbo].[T_child]',
                '[dbo].[T_parent]',
                <<<SQL
                ALTER TABLE [dbo].[T_child] ADD CONSTRAINT [fk_child_parent] FOREIGN KEY ([parent_id]) REFERENCES [dbo].[T_parent] ([id])
                SQL,
            ],
            'schema-qualified plain names' => [
                'dbo.T_child',
                'dbo.T_parent',
                <<<SQL
                ALTER TABLE [dbo].[T_child] ADD CONSTRAINT [fk_child_parent] FOREIGN KEY ([parent_id]) REFERENCES [dbo].[T_parent] ([id])
                SQL,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dropTableWithQualifiedTableNames(): array
    {
        return [
            'catalog-qualified name' => [
                'yiitest.dbo.T_migration',
                <<<SQL
                DROP TABLE [yiitest].[dbo].[T_migration]
                SQL,
            ],
            'schema-qualified bracket-quoted name' => [
                '[dbo].[T_migration]',
                <<<SQL
                DROP TABLE [dbo].[T_migration]
                SQL,
            ],
            'schema-qualified plain name' => [
                'dbo.T_migration',
                <<<SQL
                DROP TABLE [dbo].[T_migration]
                SQL,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\SchemaColumnsTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class SchemaColumnsProvider
{
    /**
     * @return array<string, array{array<string, string>, string, array<string, string>}>
     */
    public static function findColumnsQuery(): array
    {
        return [
            'simple table reads from the default sys catalog' => [
                ['name' => 'profile'],
                <<<SQL
                SELECT
                    [c].[name] AS [column_name],
                    CASE WHEN [c].[is_nullable] = 1 THEN 'YES' ELSE 'NO' END AS [is_nullable],
                    CASE
                        WHEN [t].[name] IN ('char','varchar','nchar','nvarchar','binary','varbinary') THEN
                            CASE
                                WHEN [c].[max_length] = -1 AND [t].[name] IN ('varchar','nvarchar','varbinary') THEN
                                    [t].[name] + '(max)'
                                WHEN [t].[name] IN ('nchar','nvarchar') THEN
                                    [t].[name] + '(' + CAST([c].[max_length] / 2 AS VARCHAR) + ')'
                                ELSE
                                    [t].[name] + '(' + CAST([c].[max_length] AS VARCHAR) + ')'
                            END
                        WHEN [t].[name] IN ('decimal','numeric') THEN
                            [t].[name] + '(' + CAST([c].[precision] AS VARCHAR) + ',' + CAST([c].[scale] AS VARCHAR) + ')'
                        ELSE [t].[name]
                    END AS [data_type],
                    [dc].[definition] AS [column_default],
                    [c].[is_identity],
                    [c].[is_computed],
                    CAST([ep].[value] AS NVARCHAR(MAX)) AS [comment]
                FROM [sys].[columns] AS [c]
                INNER JOIN [sys].[types] AS [t]
                    ON [c].[system_type_id] = [t].[system_type_id]
                    AND [t].[user_type_id] = [t].[system_type_id]
                LEFT JOIN [sys].[default_constraints] AS [dc]
                    ON [dc].[object_id] = [c].[default_object_id]
                LEFT JOIN [sys].[extended_properties] AS [ep]
                    ON [ep].[major_id] = [c].[object_id]
                    AND [ep].[minor_id] = [c].[column_id]
                    AND [ep].[class] = 1
                    AND [ep].[name] = 'MS_Description'
                WHERE [c].[object_id] = OBJECT_ID(:fullName)
                ORDER BY [c].[column_id]
                SQL,
                [':fullName' => '[profile]'],
            ],
            'catalog-qualified table reads from the catalog sys schema' => [
                [
                    'catalogName' => 'yiitest',
                    'schemaName' => 'dbo',
                    'name' => 'table.with.special.characters',
                ],
                <<<SQL
                SELECT
                    [c].[name] AS [column_name],
                    CASE WHEN [c].[is_nullable] = 1 THEN 'YES' ELSE 'NO' END AS [is_nullable],
                    CASE
                        WHEN [t].[name] IN ('char','varchar','nchar','nvarchar','binary','varbinary') THEN
                            CASE
                                WHEN [c].[max_length] = -1 AND [t].[name] IN ('varchar','nvarchar','varbinary') THEN
                                    [t].[name] + '(max)'
                                WHEN [t].[name] IN ('nchar','nvarchar') THEN
                                    [t].[name] + '(' + CAST([c].[max_length] / 2 AS VARCHAR) + ')'
                                ELSE
                                    [t].[name] + '(' + CAST([c].[max_length] AS VARCHAR) + ')'
                            END
                        WHEN [t].[name] IN ('decimal','numeric') THEN
                            [t].[name] + '(' + CAST([c].[precision] AS VARCHAR) + ',' + CAST([c].[scale] AS VARCHAR) + ')'
                        ELSE [t].[name]
                    END AS [data_type],
                    [dc].[definition] AS [column_default],
                    [c].[is_identity],
                    [c].[is_computed],
                    CAST([ep].[value] AS NVARCHAR(MAX)) AS [comment]
                FROM [yiitest].[sys].[columns] AS [c]
                INNER JOIN [yiitest].[sys].[types] AS [t]
                    ON [c].[system_type_id] = [t].[system_type_id]
                    AND [t].[user_type_id] = [t].[system_type_id]
                LEFT JOIN [yiitest].[sys].[default_constraints] AS [dc]
                    ON [dc].[object_id] = [c].[default_object_id]
                LEFT JOIN [yiitest].[sys].[extended_properties] AS [ep]
                    ON [ep].[major_id] = [c].[object_id]
                    AND [ep].[minor_id] = [c].[column_id]
                    AND [ep].[class] = 1
                    AND [ep].[name] = 'MS_Description'
                WHERE [c].[object_id] = OBJECT_ID(:fullName)
                ORDER BY [c].[column_id]
                SQL,
                [':fullName' => '[yiitest].[dbo].[table.with.special.characters]'],
            ],
        ];
    }
}

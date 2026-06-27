<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\SchemaTest} and {@see \yiiunit\framework\db\mssql\SchemaQuoteTest}
 * test cases.
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
    /**
     * @return list<array{string, string}>
     */
    public static function quoteTableName(): array
    {
        return [
            ...parent::quoteTableName(),
            ['[[test]].[[test.test]]', '[[test]].[[test.test]]'],
            ['test.[[test.test]]', '[[test]].[[test.test]]'],
            ['test.test.[[test.test]]', '[[test]].[[test]].[[test.test]]'],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function quoteTableNameWithDefaultSchema(): array
    {
        return [
            '`dbo` default leaves the unqualified name unchanged' => [
                'dbo',
                'T_migration',
                '[T_migration]',
            ],
            'already schema-qualified name is preserved' => [
                'ecbox',
                'ecbox.T_migration',
                '[ecbox].[T_migration]',
            ],
            'bracket-quoted single name gains the configured schema prefix' => [
                'ecbox',
                '[T_migration]',
                '[ecbox].[T_migration]',
            ],
            'explicit foreign schema is preserved' => [
                'ecbox',
                'other.T_migration',
                '[other].[T_migration]',
            ],
            'placeholder syntax is left untouched' => [
                'ecbox',
                '{{%T_migration}}',
                '{{%T_migration}}',
            ],
            'subquery expression is left untouched' => [
                'ecbox',
                '(SELECT 1)',
                '(SELECT 1)',
            ],
            'unqualified name gains the configured schema prefix' => [
                'ecbox',
                'T_migration',
                '[ecbox].[T_migration]',
            ],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteColumnName(): array
    {
        return [
            ...parent::quoteColumnName(),
            ['[[already_quoted]]', '[[already_quoted]]'],
            ['[[a.b]]', '[[a.b]]'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteSimpleTableName(): array
    {
        return [
            ...parent::quoteSimpleTableName(),
            ['a[b', 'a[b'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function getTableSchema(): array
    {
        return [
            ['[dbo].[profile]', 'profile'],
            ['dbo.[table.with.special.characters]', 'table.with.special.characters'],
            ['dbo.profile', 'profile'],
            ['profile', 'profile'],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string, string|null}>
     */
    public static function catalogSchemaNames(): array
    {
        return [
            'across database' => [
                'tempdb.dbo',
                'tempdb.dbo',
                'test_sys_catalog_table_cross_db',
                'test_sys_catalog_view_cross_db',
                'tempdb',
            ],
            'current database' => [
                'dbo',
                'dbo',
                'test_sys_catalog_table_current',
                'test_sys_catalog_view_current',
                null,
            ],
            'default schema' => [
                '',
                'dbo',
                'test_sys_catalog_table_default',
                'test_sys_catalog_view_default',
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{list<string>, list<string>, list<string>}>
     */
    public static function systemSchemaNames(): array
    {
        return [
            'custom configured system schemas' => [
                ['guest', 'yii_custom_system_schema', 'yii custom system schema'],
                ['yii_custom_system_schema', 'yii custom system schema'],
                ['dbo'],
            ],
            'default system schemas' => [
                ['guest'],
                [],
                ['dbo'],
            ],
            'empty configured system schemas' => [
                [],
                [],
                ['dbo', 'guest'],
            ],
        ];
    }

    /**
     * @return array<string, array{string, string|null, string, string, string}>
     */
    public static function resolveTableName(): array
    {
        return [
            'single part' => [
                'customer',
                null,
                'dbo',
                'customer',
                'customer',
            ],
            'two parts' => [
                'sales.customer',
                null,
                'sales',
                'customer',
                'sales.customer',
            ],
            'two parts default schema' => [
                'dbo.customer',
                null,
                'dbo',
                'customer',
                'customer',
            ],
            'three parts' => [
                'catalog1.sales.customer',
                'catalog1',
                'sales',
                'customer',
                'catalog1.sales.customer',
            ],
            'four parts' => [
                '[server1].catalog1.sales.customer',
                'catalog1',
                'sales',
                'customer',
                'catalog1.sales.customer',
            ],
        ];
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use yii\db\DefaultValueConstraint;
use yiiunit\framework\db\AnyValue;

use function array_walk;
use function in_array;

/**
 * Data provider for MSSQL schema constraint metadata test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function expectedColumns(): array
    {
        $columns = parent::expectedColumns();

        unset($columns['enum_col']);
        unset($columns['ts_default']);
        unset($columns['bit_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'int';
        $columns['int_col2']['dbType'] = 'int';
        $columns['tinyint_col']['dbType'] = 'tinyint';
        $columns['smallint_col']['dbType'] = 'smallint';
        $columns['float_col']['dbType'] = 'decimal';
        $columns['float_col']['phpType'] = 'string';
        $columns['float_col']['type'] = 'decimal';
        $columns['float_col']['scale'] = null;
        $columns['float_col2']['dbType'] = 'float';
        $columns['float_col2']['phpType'] = 'double';
        $columns['float_col2']['type'] = 'float';
        $columns['float_col2']['scale'] = null;
        $columns['blob_col']['dbType'] = 'varbinary';
        $columns['numeric_col']['dbType'] = 'decimal';
        $columns['numeric_col']['scale'] = null;
        $columns['time']['dbType'] = 'datetime';
        $columns['time']['type'] = 'datetime';
        $columns['bool_col']['dbType'] = 'tinyint';
        $columns['bool_col2']['dbType'] = 'tinyint';

        array_walk(
            $columns,
            static function (&$item): void {
                $item['enumValues'] = [];
            },
        );

        array_walk(
            $columns,
            static function (&$item, $name): void {
                if (!in_array($name, ['char_col', 'char_col2', 'char_col3'])) {
                    $item['size'] = null;
                }
            },
        );

        array_walk(
            $columns,
            static function (&$item, $name): void {
                if (!in_array($name, ['char_col', 'char_col2', 'char_col3'])) {
                    $item['precision'] = null;
                }
            },
        );

        return $columns;
    }

    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->expression = '([C_check]<>\'\')';
        $result['1: default'][2] = [];
        $result['1: default'][2][] = new DefaultValueConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_default'],
                'value' => '((0))',
            ],
        );
        $result['2: default'][2] = [];
        $result['3: foreign key'][2][0]->foreignSchemaName = 'dbo';
        $result['3: index'][2] = [];
        $result['3: default'][2] = [];
        $result['4: default'][2] = [];

        return $result;
    }

    /**
     * Provides MSSQL table names and their quoted equivalents.
     *
     * @return list<array{string, string}>
     */
    public static function quoteTableName(): array
    {
        return [
            ['[test]', '[test]'],
            ['[test].[test.test]', '[test].[test.test]'],
            ['[test].[test]', '[test].[test]'],
            ['test', '[test]'],
            ['test.[test.test]', '[test].[test.test]'],
            ['test.test', '[test].[test]'],
            ['test.test.[test.test]', '[test].[test].[test.test]'],
            ['test.test.test', '[test].[test].[test]'],
        ];
    }

    /**
     * Provides MSSQL table names and their resolved schema names.
     *
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
     * Provides MSSQL table names and their resolved table metadata.
     *
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

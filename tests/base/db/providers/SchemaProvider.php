<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\providers;

use PDO;

/**
 * Data provider for {@see \yiiunit\base\db\BaseSchema} and {@see \yiiunit\base\db\BaseSchemaQuote} test cases.
 */
class SchemaProvider
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function getTableSchema(): array
    {
        return [
            'plain name' => ['profile', 'profile'],
            'prefix placeholder' => ['{{%profile}}', 'profile'],
        ];
    }

    /**
     * @return array<array{array<int, bool>}>
     */
    public static function pdoAttributes(): array
    {
        return [
            [[PDO::ATTR_EMULATE_PREPARES => true]],
            [[PDO::ATTR_EMULATE_PREPARES => false]],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteTableName(): array
    {
        return [
            ['{{table}}', '{{table}}'],
            ['(table)', '(table)'],
            ['[[test]]', '[[test]]'],
            ['[[test]].[[test]]', '[[test]].[[test]]'],
            ['test', '[[test]]'],
            ['test.[[test]].test', '[[test]].[[test]].[[test]]'],
            ['test.test', '[[test]].[[test]]'],
            ['test.test.test', '[[test]].[[test]].[[test]]'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteColumnName(): array
    {
        return [
            ['', ''],
            ['*', '*'],
            ['(*)', '(*)'],
            ['(column)', '(column)'],
            ['{{column}}', '{{column}}'],
            ['[[*]]', '[[*]]'],
            ['[[column]]', '[[column]]'],
            ['column', '[[column]]'],
            ['table.*', '[[table]].*'],
            ['[[table]].*', '[[table]].*'],
            ['table.column', '[[table]].[[column]]'],
            ['table.[[column]]', '[[table]].[[column]]'],
            ['[[table]].column', '[[table]].[[column]]'],
            ['[[table]].[[column]]', '[[table]].[[column]]'],
            ['schema.table.column', '[[schema]].[[table]].[[column]]'],
            ['{{table}}.column', '{{table}}.[[column]]'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteSimpleTableName(): array
    {
        return [
            ['[[test]]', '[[test]]'],
            ['test', '[[test]]'],
            ['test.test', '[[test.test]]'],
            ['(test)', '[[(test)]]'],
            ['current-table-name', '[[current-table-name]]'],
            ["te'st", "[[te'st]]"],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteSimpleColumnName(): array
    {
        return [
            ['*', '*'],
            ['[[column]]', '[[column]]'],
            ['column', '[[column]]'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function unquoteSimpleTableName(): array
    {
        return [
            ['[[test]]', 'test'],
            ['test', 'test'],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function unquoteSimpleColumnName(): array
    {
        return [
            ['*', '*'],
            ['[[column]]', 'column'],
            ['column', 'column'],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function quoteValueNotString(): array
    {
        return [
            'false' => [false],
            'float' => [1.5],
            'int' => [123],
            'negative int' => [-5],
            'null' => [null],
            'true' => [true],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function quoteValue(): array
    {
        return [
            ["It's interesting", "'It''s interesting'"],
            ['string', "'string'"],
        ];
    }
}

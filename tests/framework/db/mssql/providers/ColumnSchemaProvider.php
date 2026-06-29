<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use PDO;
use yii\db\Expression;
use yii\db\mssql\Schema;
use yii\db\PdoValue;

use function array_walk;
use function bin2hex;
use function hex2bin;
use function in_array;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\ColumnSchemaTest} test cases.
 */
final class ColumnSchemaProvider extends \yiiunit\base\db\providers\ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, bool, mixed, mixed}>
     */
    public static function dbTypecast(): array
    {
        $pdoValue = new PdoValue('binary data', PDO::PARAM_LOB);

        return [
            'non-varbinary PDO LOB falls through to parent' => [
                Schema::TYPE_BINARY,
                'binary(8)',
                true,
                $pdoValue,
                $pdoValue,
            ],
            'non-varbinary string falls through to parent' => [
                Schema::TYPE_STRING,
                'varchar',
                true,
                'test',
                'test',
            ],
            'rowversion integer to binary literal' => [
                Schema::TYPE_TIMESTAMP,
                'timestamp',
                false,
                4841,
                new Expression('0x00000000000012e9'),
            ],
            'rowversion raw 8-byte binary to binary literal' => [
                Schema::TYPE_TIMESTAMP,
                'timestamp',
                false,
                hex2bin('00000000000012e9'),
                new Expression('0x00000000000012e9'),
            ],
            'varbinary integer value falls through to parent' => [
                Schema::TYPE_BINARY,
                'varbinary',
                true,
                123,
                123,
            ],
            'varbinary null when not nullable' => [
                Schema::TYPE_BINARY,
                'varbinary',
                false,
                null,
                null,
            ],
            'varbinary null when nullable' => [
                Schema::TYPE_BINARY,
                'varbinary',
                true,
                null,
                new Expression('CAST(NULL AS VARBINARY(MAX))'),
            ],
            'varbinary string value' => [
                Schema::TYPE_BINARY,
                'varbinary',
                true,
                'binary data',
                new Expression('CONVERT(VARBINARY(MAX), 0x' . bin2hex('binary data') . ')'),
            ],
            'varbinary max string value' => [
                Schema::TYPE_BINARY,
                'varbinary(max)',
                true,
                'binary data',
                new Expression('CONVERT(VARBINARY(MAX), 0x' . bin2hex('binary data') . ')'),
            ],
            'varbinary PDO LOB string value' => [
                Schema::TYPE_BINARY,
                'varbinary(max)',
                true,
                new PdoValue('binary data', PDO::PARAM_LOB),
                new Expression('CONVERT(VARBINARY(MAX), 0x' . bin2hex('binary data') . ')'),
            ],
            'varbinary nullable PDO LOB null value' => [
                Schema::TYPE_BINARY,
                'varbinary(max)',
                true,
                new PdoValue(null, PDO::PARAM_LOB),
                new Expression('CAST(NULL AS VARBINARY(MAX))'),
            ],
        ];
    }

    /**
     * @return array<string, array{string, mixed, mixed}>
     */
    public static function defaultPhpTypecast(): array
    {
        return [
            'CURRENT_TIMESTAMP on timestamp column returns null' => [
                Schema::TYPE_TIMESTAMP,
                'CURRENT_TIMESTAMP',
                null,
            ],
            'decimal default value unwrapped' => [
                Schema::TYPE_DECIMAL,
                '((3.14))',
                '3.14',
            ],
            'expression default getdate returns null' => [
                Schema::TYPE_STRING,
                '(getdate())',
                null,
            ],
            'expression default newid returns null' => [
                Schema::TYPE_STRING,
                '(newid())',
                null,
            ],
            'expression default sysdatetime returns null' => [
                Schema::TYPE_STRING,
                '(sysdatetime())',
                null,
            ],
            'integer default value unwrapped' => [
                Schema::TYPE_INTEGER,
                '((0))',
                0,
            ],
            'null string returns null' => [
                Schema::TYPE_STRING,
                '(NULL)',
                null,
            ],
            'null value returns null' => [
                Schema::TYPE_STRING,
                null,
                null,
            ],
            'string default value unwrapped' => [
                Schema::TYPE_STRING,
                "('hello')",
                'hello',
            ],
            'string with escaped single quotes' => [
                Schema::TYPE_STRING,
                "('it''s')",
                "it's",
            ],
            'unicode string default value unwrapped' => [
                Schema::TYPE_STRING,
                "(N'unicode')",
                'unicode',
            ],
            'unicode string with escaped single quotes' => [
                Schema::TYPE_STRING,
                "(N'it''s')",
                "it's",
            ],
        ];
    }

    /**
     * @return array<string, array{string, bool, int|null, string}>
     */
    public static function getOutputColumnDeclaration(): array
    {
        return [
            'bigint returns as-is' => [
                'bigint',
                false,
                null,
                'bigint',
            ],
            'binary appends size' => [
                'binary',
                false,
                16,
                'binary(16)',
            ],
            'binary with embedded size stays as-is' => [
                'binary(16)',
                false,
                16,
                'binary(16)',
            ],
            'char appends size' => [
                'char',
                false,
                10,
                'char(10)',
            ],
            'char with embedded size stays as-is' => [
                'char(10)',
                false,
                10,
                'char(10)',
            ],
            'int returns as-is' => [
                'int',
                false,
                null,
                'int',
            ],
            'nchar appends size' => [
                'nchar',
                false,
                20,
                'nchar(20)',
            ],
            'nchar with embedded size stays as-is' => [
                'nchar(20)',
                false,
                20,
                'nchar(20)',
            ],
            'nvarchar appends MAX' => [
                'nvarchar',
                false,
                null,
                'nvarchar(MAX)',
            ],
            'nvarchar(max) returns as-is' => [
                'nvarchar(max)',
                false,
                null,
                'nvarchar(max)',
            ],
            'nvarchar with embedded size stays as-is' => [
                'nvarchar(100)',
                false,
                100,
                'nvarchar(100)',
            ],
            'timestamp not nullable' => [
                Schema::TYPE_TIMESTAMP,
                false,
                null,
                'binary(8)',
            ],
            'timestamp nullable' => [
                Schema::TYPE_TIMESTAMP,
                true,
                null,
                'varbinary(8)',
            ],
            'varbinary appends MAX' => [
                'varbinary',
                false,
                null,
                'varbinary(MAX)',
            ],
            'varbinary(max) returns as-is' => [
                'varbinary(max)',
                false,
                null,
                'varbinary(max)',
            ],
            'varbinary with embedded size stays as-is' => [
                'varbinary(50)',
                false,
                50,
                'varbinary(50)',
            ],
            'varchar appends MAX' => [
                'varchar',
                false,
                null,
                'varchar(MAX)',
            ],
            'varchar(max) returns as-is' => [
                'varchar(max)',
                false,
                null,
                'varchar(max)',
            ],
            'varchar with embedded size stays as-is' => [
                'varchar(128)',
                false,
                128,
                'varchar(128)',
            ],
        ];
    }

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
        $columns['float_col']['dbType'] = 'decimal(4,3)';
        $columns['float_col']['phpType'] = 'string';
        $columns['float_col']['type'] = 'decimal';
        $columns['float_col2']['dbType'] = 'float';
        $columns['float_col2']['phpType'] = 'double';
        $columns['float_col2']['type'] = 'float';
        $columns['float_col2']['scale'] = null;
        $columns['blob_col']['dbType'] = 'varbinary(max)';
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
                if (!in_array($name, ['char_col', 'char_col2', 'float_col', 'numeric_col'], true)) {
                    $item['size'] = null;
                    $item['precision'] = null;
                }
            },
        );

        return $columns;
    }
}

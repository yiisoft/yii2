<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use yii\db\Expression;
use yii\db\mssql\Schema;

use function bin2hex;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\ColumnSchemaTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, bool, mixed, mixed}>
     */
    public static function dbTypecast(): array
    {
        return [
            'non-varbinary string falls through to parent' => [
                Schema::TYPE_STRING,
                'varchar',
                true,
                'test',
                'test',
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
            'varchar with embedded size stays as-is' => [
                'varchar(128)',
                false,
                128,
                'varchar(128)',
            ],
        ];
    }
}

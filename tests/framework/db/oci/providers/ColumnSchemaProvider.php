<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\providers;

use yii\db\Expression;
use yii\db\oci\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\oci\ColumnSchemaTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, mixed, mixed}>
     */
    public static function dbTypecast(): array
    {
        return [
            'BLOB integer value falls through to parent' => [
                Schema::TYPE_BINARY,
                'BLOB',
                123,
                123,
            ],
            'BLOB null value falls through to parent' => [
                Schema::TYPE_BINARY,
                'BLOB',
                null,
                null,
            ],
            'BLOB string value returns Expression' => [
                Schema::TYPE_BINARY,
                'BLOB',
                'binary data',
                Expression::class,
            ],
            'non-BLOB string falls through to parent' => [
                Schema::TYPE_STRING,
                'VARCHAR2',
                'test',
                'test',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, mixed, mixed}>
     */
    public static function defaultPhpTypecast(): array
    {
        return [
            'CURRENT_TIMESTAMP on non-timestamp column stays a literal' => [
                'string',
                'VARCHAR2',
                'string',
                'CURRENT_TIMESTAMP',
                'CURRENT_TIMESTAMP',
            ],
            'CURRENT_TIMESTAMP on timestamp column returns Expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP with surrounding spaces is trimmed' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                '  CURRENT_TIMESTAMP  ',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP with trailing newline is trimmed' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                "CURRENT_TIMESTAMP\n",
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP(0) preserves zero precision' => [
                'timestamp',
                'TIMESTAMP(0)',
                'string',
                'CURRENT_TIMESTAMP(0)',
                new Expression('CURRENT_TIMESTAMP(0)'),
            ],
            'CURRENT_TIMESTAMP(6) preserves precision' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'CURRENT_TIMESTAMP(6)',
                new Expression('CURRENT_TIMESTAMP(6)'),
            ],
            'decimal default kept as string' => [
                'decimal',
                'NUMBER',
                'string',
                '33.22',
                '33.22',
            ],
            'doubled single quotes inside string are collapsed' => [
                'string',
                'VARCHAR2',
                'string',
                "'it''s'",
                "it's",
            ],
            'double default cast to float' => [
                'double',
                'FLOAT',
                'double',
                '1.23',
                1.23,
            ],
            'empty string returns null' => [
                'string',
                'VARCHAR2',
                'string',
                '',
                null,
            ],
            'escaped single quote literal is collapsed' => [
                'string',
                'VARCHAR2',
                'string',
                "''''",
                "'",
            ],
            'LOCALTIMESTAMP on timestamp column returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'LOCALTIMESTAMP',
                null,
            ],
            'lowercase current_timestamp on timestamp column returns Expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'current_timestamp',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'negative decimal default kept as string' => [
                'decimal',
                'NUMBER',
                'string',
                '-33.22',
                '-33.22',
            ],
            'negative integer default cast to int' => [
                'integer',
                'NUMBER',
                'integer',
                '-123',
                -123,
            ],
            'null value returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                null,
                null,
            ],
            'NULL literal returns null' => [
                'string',
                'VARCHAR2',
                'string',
                'NULL',
                null,
            ],
            'lowercase null literal returns null' => [
                'string',
                'VARCHAR2',
                'string',
                'null',
                null,
            ],
            'mixed-case Null literal returns null' => [
                'string',
                'VARCHAR2',
                'string',
                'Null',
                null,
            ],
            'quoted null string literal is preserved' => [
                'string',
                'VARCHAR2',
                'string',
                "'null'",
                'null',
            ],
            'numeric char default kept as string' => [
                'string',
                'CHAR',
                'string',
                '130',
                '130',
            ],
            'quoted string containing timestamp keyword is preserved' => [
                'string',
                'VARCHAR2',
                'string',
                "'update_timestamp_flag'",
                'update_timestamp_flag',
            ],
            'regular integer default cast to int' => [
                'integer',
                'NUMBER',
                'integer',
                '42',
                42,
            ],
            'single-quoted char default is unwrapped' => [
                'string',
                'CHAR',
                'string',
                "'1'",
                '1',
            ],
            'single-quoted string default is unwrapped' => [
                'string',
                'VARCHAR2',
                'string',
                "'something'",
                'something',
            ],
            'SYSTIMESTAMP on timestamp column returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'SYSTIMESTAMP',
                null,
            ],
            'TIMESTAMP literal on timestamp column returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                "TIMESTAMP '2002-01-01 00:00:00'",
                null,
            ],
            'to_timestamp expression on timestamp column returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                "to_timestamp('2002-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss')",
                null,
            ],
            'unquoted string default kept verbatim' => [
                'string',
                'VARCHAR2',
                'string',
                'hello',
                'hello',
            ],
            'whitespace-only string returns null' => [
                'string',
                'VARCHAR2',
                'string',
                '   ',
                null,
            ],
        ];
    }
}

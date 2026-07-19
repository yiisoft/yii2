<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\providers;

use yii\db\Expression;

/**
 * Data provider for {@see \yiiunit\framework\db\sqlite\ColumnSchemaTest} test cases.
 */
final class ColumnSchemaProvider extends \yiiunit\base\db\providers\ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, string, mixed, mixed}>
     */
    public static function defaultPhpTypecast(): array
    {
        return [
            'already typed boolean passes through' => [
                'boolean',
                'tinyint(1)',
                'boolean',
                true,
                true,
            ],
            'already typed double passes through' => [
                'double',
                'double',
                'double',
                1.5,
                1.5,
            ],
            'already typed integer passes through' => [
                'integer',
                'integer',
                'integer',
                42,
                42,
            ],
            'arithmetic expression returns Expression' => [
                'integer',
                'integer',
                'integer',
                '1 + 2',
                new Expression('1 + 2'),
            ],
            'backtick-quoted string resolves doubled quotes' => [
                'string',
                'varchar(32)',
                'string',
                '`do``uble`',
                'do`uble',
            ],
            'bareword default remains a string literal' => [
                'string',
                'text',
                'string',
                'pending',
                'pending',
            ],
            'blob literal remains an expression' => [
                'binary',
                'blob',
                'resource',
                "x'414243'",
                new Expression("x'414243'"),
            ],
            'boolean keyword FALSE returns false' => [
                'boolean',
                'boolean',
                'boolean',
                'FALSE',
                false,
            ],
            'boolean keyword true returns true' => [
                'boolean',
                'boolean',
                'boolean',
                'true',
                true,
            ],
            'boolean tinyint(1) one returns true' => [
                'boolean',
                'tinyint(1)',
                'boolean',
                '1',
                true,
            ],
            'boolean tinyint(1) zero returns false' => [
                'boolean',
                'tinyint(1)',
                'boolean',
                '0',
                false,
            ],
            'bracket-quoted string is unwrapped' => [
                'string',
                'varchar(32)',
                'string',
                '[hello world]',
                'hello world',
            ],
            'CURRENT_DATE on date column returns Expression' => [
                'date',
                'date',
                'string',
                'CURRENT_DATE',
                new Expression('CURRENT_DATE'),
            ],
            'CURRENT_TIME on time column returns Expression' => [
                'time',
                'time',
                'string',
                'CURRENT_TIME',
                new Expression('CURRENT_TIME'),
            ],
            'CURRENT_TIMESTAMP lowercase on timestamp column returns Expression' => [
                'timestamp',
                'timestamp',
                'string',
                'current_timestamp',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP mixed case on timestamp column returns Expression' => [
                'timestamp',
                'timestamp',
                'string',
                'Current_Timestamp',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on datetime column returns Expression' => [
                'datetime',
                'datetime',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on string column returns Expression' => [
                'string',
                'varchar',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on timestamp column returns Expression' => [
                'timestamp',
                'timestamp',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'decimal default keeps numeric string' => [
                'decimal',
                'decimal(5,2)',
                'string',
                '3.14',
                '3.14',
            ],
            'double default' => [
                'double',
                'double',
                'double',
                '1.5',
                1.5,
            ],
            'double-quoted string default is unwrapped' => [
                'string',
                'varchar(32)',
                'string',
                '"hello"',
                'hello',
            ],
            'double-quoted string resolves doubled quotes' => [
                'string',
                'varchar(32)',
                'string',
                '"do""uble"',
                'do"uble',
            ],
            'empty string literal is unwrapped to empty string' => [
                'string',
                'varchar(32)',
                'string',
                "''",
                '',
            ],
            'empty string returns null' => [
                'string',
                'varchar(32)',
                'string',
                '',
                null,
            ],
            'expression default returns Expression' => [
                'text',
                'text',
                'string',
                "datetime('now')",
                new Expression("datetime('now')"),
            ],
            'Expression object passes through without string coercion' => [
                'integer',
                'integer',
                'integer',
                new Expression('1 + 2'),
                new Expression('1 + 2'),
            ],
            'hexadecimal integer remains an expression' => [
                'integer',
                'integer',
                'integer',
                '0x10',
                new Expression('0x10'),
            ],
            'integer default' => [
                'integer',
                'integer',
                'integer',
                '42',
                42,
            ],
            'negative double default' => [
                'double',
                'double',
                'double',
                '-12345.6789',
                -12345.6789,
            ],
            'negative integer default' => [
                'integer',
                'integer',
                'integer',
                '-42',
                -42,
            ],
            'null literal lowercase returns null' => [
                'string',
                'varchar(32)',
                'string',
                'null',
                null,
            ],
            'NULL literal mixed case returns null' => [
                'integer',
                'integer',
                'integer',
                'Null',
                null,
            ],
            'NULL literal uppercase returns null' => [
                'string',
                'varchar(32)',
                'string',
                'NULL',
                null,
            ],
            'null value returns null' => [
                'timestamp',
                'timestamp',
                'string',
                null,
                null,
            ],
            'parenthesized blob literal remains an expression' => [
                'binary',
                'blob',
                'resource',
                "(x'414243')",
                new Expression("(x'414243')"),
            ],
            'parenthesized integer default is unwrapped and cast' => [
                'integer',
                'integer',
                'integer',
                '(42)',
                42,
            ],
            'parenthesized NULL literal returns null' => [
                'string',
                'varchar(32)',
                'string',
                '(NULL)',
                null,
            ],
            'parenthesized string default is unwrapped' => [
                'string',
                'varchar(32)',
                'string',
                "('hello')",
                'hello',
            ],
            'plus-signed integer default' => [
                'integer',
                'integer',
                'integer',
                '+5',
                5,
            ],
            'quote-only literal resolves to single quote' => [
                'string',
                'varchar(32)',
                'string',
                "''''",
                "'",
            ],
            'quoted integer default is unwrapped and cast' => [
                'integer',
                'integer',
                'integer',
                "'1'",
                1,
            ],
            'quoted negative integer default is unwrapped and cast' => [
                'integer',
                'integer',
                'integer',
                "'-123'",
                -123,
            ],
            'scientific numeric default is cast' => [
                'double',
                'double',
                'double',
                '1.25e2',
                125.0,
            ],
            'single-quoted string default is unwrapped' => [
                'string',
                'varchar(32)',
                'string',
                "'hello'",
                'hello',
            ],
            'single-quoted string resolves doubled quotes' => [
                'string',
                'varchar(32)',
                'string',
                "'it''s'",
                "it's",
            ],
            'string concatenation returns Expression' => [
                'text',
                'text',
                'string',
                "'a' || 'b'",
                new Expression("'a' || 'b'"),
            ],
            'timestamp literal default is unwrapped' => [
                'timestamp',
                'timestamp',
                'string',
                "'2002-01-01 00:00:00'",
                '2002-01-01 00:00:00',
            ],
            'whitespace-padded integer default is trimmed' => [
                'integer',
                'integer',
                'integer',
                ' 42 ',
                42,
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
        unset($columns['bit_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['bool_col']['type'] = 'boolean';
        $columns['bool_col']['phpType'] = 'boolean';
        $columns['bool_col2']['type'] = 'boolean';
        $columns['bool_col2']['phpType'] = 'boolean';
        $columns['bool_col2']['defaultValue'] = true;
        $columns['bit32'] = [
            'type' => 'integer',
            'dbType' => 'bit(32)',
            'phpType' => 'integer',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => 32,
            'precision' => 32,
            'scale' => null,
            'defaultValue' => null,
        ];
        $columns['bit33'] = [
            'type' => 'bigint',
            'dbType' => 'bit(33)',
            'phpType' => 'integer',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => 33,
            'precision' => 33,
            'scale' => null,
            'defaultValue' => null,
        ];

        return $columns;
    }
}

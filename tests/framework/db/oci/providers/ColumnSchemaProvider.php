<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\providers;

use yii\db\Expression;
use yii\db\oci\LobValue;
use yii\db\oci\Schema;

/**
 * Data provider for {@see \yiiunit\framework\db\oci\ColumnSchemaTest} test cases.
 */
final class ColumnSchemaProvider extends \yiiunit\base\db\providers\ColumnSchemaProvider
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
            'BLOB string value returns LobValue' => [
                Schema::TYPE_BINARY,
                'BLOB',
                'binary data',
                LobValue::class,
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
            'alternative-quoted string remains an expression' => [
                'string',
                'VARCHAR2',
                'string',
                "q'[O'Reilly]'",
                new Expression("q'[O'Reilly]'"),
            ],
            'binary double suffix is removed before typecasting' => [
                'double',
                'BINARY_DOUBLE',
                'double',
                '1.25D',
                1.25,
            ],
            'CURRENT_TIMESTAMP on non-timestamp column remains an expression' => [
                'string',
                'VARCHAR2',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
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
            'double default cast to float' => [
                'double',
                'FLOAT',
                'double',
                '1.23',
                1.23,
            ],
            'doubled single quotes inside string are collapsed' => [
                'string',
                'VARCHAR2',
                'string',
                "'it''s'",
                "it's",
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
            'LOCALTIMESTAMP on timestamp column remains an expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'LOCALTIMESTAMP',
                new Expression('LOCALTIMESTAMP'),
            ],
            'lowercase current_timestamp on timestamp column returns Expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'current_timestamp',
                new Expression('current_timestamp'),
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
            'national string default is unwrapped' => [
                'string',
                'NVARCHAR2',
                'string',
                "N'O''Reilly'",
                "O'Reilly",
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
            'NULL literal returns null' => [
                'string',
                'VARCHAR2',
                'string',
                'NULL',
                null,
            ],
            'null value returns null' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                null,
                null,
            ],
            'numeric char default kept as string' => [
                'string',
                'CHAR',
                'string',
                '130',
                '130',
            ],
            'operator expression remains an expression' => [
                'integer',
                'NUMBER',
                'integer',
                '(1 + 2)',
                new Expression('(1 + 2)'),
            ],
            'parenthesized NULL literal returns null' => [
                'string',
                'VARCHAR2',
                'string',
                '(NULL)',
                null,
            ],
            'quoted empty string returns null' => [
                'string',
                'VARCHAR2',
                'string',
                "''",
                null,
            ],
            'quoted null string literal is preserved' => [
                'string',
                'VARCHAR2',
                'string',
                "'null'",
                'null',
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
            'scientific numeric default is typecast' => [
                'double',
                'FLOAT',
                'double',
                '1.25e2',
                125.0,
            ],
            'sequence NEXTVAL remains an expression' => [
                'integer',
                'NUMBER',
                'integer',
                'test_sequence.NEXTVAL',
                new Expression('test_sequence.NEXTVAL'),
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
            'string concatenation is not mistaken for a quoted literal' => [
                'string',
                'VARCHAR2',
                'string',
                "'a' || 'b'",
                new Expression("'a' || 'b'"),
            ],
            'SYSDATE on date column remains an expression' => [
                'string',
                'DATE',
                'string',
                'SYSDATE',
                new Expression('SYSDATE'),
            ],
            'SYSTIMESTAMP on timestamp column remains an expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                'SYSTIMESTAMP',
                new Expression('SYSTIMESTAMP'),
            ],
            'TIMESTAMP literal on timestamp column remains an expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                "TIMESTAMP '2002-01-01 00:00:00'",
                new Expression("TIMESTAMP '2002-01-01 00:00:00'"),
            ],
            'to_timestamp expression on timestamp column remains an expression' => [
                'timestamp',
                'TIMESTAMP(6)',
                'string',
                "to_timestamp('2002-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss')",
                new Expression("to_timestamp('2002-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss')"),
            ],
            'unquoted string default remains an expression' => [
                'string',
                'VARCHAR2',
                'string',
                'hello',
                new Expression('hello'),
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

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function expectedColumns(): array
    {
        $columns = parent::expectedColumns();

        unset($columns['enum_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'NUMBER';
        $columns['int_col']['size'] = 22;
        $columns['int_col']['precision'] = null;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'NUMBER';
        $columns['int_col2']['size'] = 22;
        $columns['int_col2']['precision'] = null;
        $columns['int_col2']['scale'] = 0;
        $columns['tinyint_col']['dbType'] = 'NUMBER';
        $columns['tinyint_col']['type'] = 'integer';
        $columns['tinyint_col']['size'] = 22;
        $columns['tinyint_col']['precision'] = 3;
        $columns['tinyint_col']['scale'] = 0;
        $columns['smallint_col']['dbType'] = 'NUMBER';
        $columns['smallint_col']['type'] = 'integer';
        $columns['smallint_col']['size'] = 22;
        $columns['smallint_col']['precision'] = null;
        $columns['smallint_col']['scale'] = 0;
        $columns['char_col']['type'] = 'string';
        $columns['char_col']['dbType'] = 'CHAR';
        $columns['char_col']['precision'] = null;
        $columns['char_col']['size'] = 100;
        $columns['char_col2']['dbType'] = 'VARCHAR2';
        $columns['char_col2']['precision'] = null;
        $columns['char_col2']['size'] = 100;
        $columns['char_col3']['type'] = 'string';
        $columns['char_col3']['dbType'] = 'VARCHAR2';
        $columns['char_col3']['precision'] = null;
        $columns['char_col3']['size'] = 4000;
        $columns['float_col']['dbType'] = 'FLOAT';
        $columns['float_col']['precision'] = 126;
        $columns['float_col']['scale'] = null;
        $columns['float_col']['size'] = 22;
        $columns['float_col2']['dbType'] = 'FLOAT';
        $columns['float_col2']['precision'] = 126;
        $columns['float_col2']['scale'] = null;
        $columns['float_col2']['size'] = 22;
        $columns['blob_col']['dbType'] = 'BLOB';
        $columns['blob_col']['phpType'] = 'resource';
        $columns['blob_col']['type'] = 'binary';
        $columns['blob_col']['size'] = 4000;
        $columns['numeric_col']['dbType'] = 'NUMBER';
        $columns['numeric_col']['size'] = 22;
        $columns['time']['dbType'] = 'TIMESTAMP(6)';
        $columns['time']['size'] = 11;
        $columns['time']['scale'] = 6;
        $columns['time']['defaultValue'] = new Expression(
            "to_timestamp('2002-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss')",
        );
        $columns['bool_col']['type'] = 'string';
        $columns['bool_col']['phpType'] = 'string';
        $columns['bool_col']['dbType'] = 'CHAR';
        $columns['bool_col']['size'] = 1;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['type'] = 'string';
        $columns['bool_col2']['phpType'] = 'string';
        $columns['bool_col2']['dbType'] = 'CHAR';
        $columns['bool_col2']['size'] = 1;
        $columns['bool_col2']['precision'] = null;
        $columns['bool_col2']['defaultValue'] = '1';
        $columns['ts_default']['type'] = 'timestamp';
        $columns['ts_default']['phpType'] = 'string';
        $columns['ts_default']['dbType'] = 'TIMESTAMP(6)';
        $columns['ts_default']['scale'] = 6;
        $columns['ts_default']['size'] = 11;
        $columns['bit_col']['type'] = 'string';
        $columns['bit_col']['phpType'] = 'string';
        $columns['bit_col']['dbType'] = 'CHAR';
        $columns['bit_col']['size'] = 3;
        $columns['bit_col']['precision'] = null;
        $columns['bit_col']['defaultValue'] = '130';

        return $columns;
    }
}

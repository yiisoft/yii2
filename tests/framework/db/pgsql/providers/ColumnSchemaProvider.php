<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

use yii\db\Expression;

/**
 * Data provider for {@see \yiiunit\framework\db\pgsql\ColumnSchemaTest} test cases.
 */
final class ColumnSchemaProvider extends \yiiunit\base\db\providers\ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, string, mixed, mixed, 5?: int}>
     */
    public static function defaultPhpTypecast(): array
    {
        return [
            'array constructor becomes an Expression' => [
                'integer',
                'int4',
                'integer',
                'ARRAY[]::integer[]',
                new Expression('ARRAY[]::integer[]'),
                1,
            ],
            'array literal parses to typed array' => [
                'integer',
                'int4',
                'integer',
                "'{1,2}'::integer[]",
                [1, 2],
                1,
            ],
            'array literal with escaped quote parses to array' => [
                'text',
                'text',
                'string',
                "'{a''s,b}'::text[]",
                ["a's", 'b'],
                1,
            ],
            'bare integer cast to int' => [
                'integer',
                'int4',
                'integer',
                '42',
                42,
            ],
            'bare zero integer cast to int' => [
                'integer',
                'int4',
                'integer',
                '0',
                0,
            ],
            'binary bit B\'10101\' on bit(5)' => [
                'integer',
                'bit',
                'integer',
                "B'10101'::bit(5)",
                21,
            ],
            'bit concatenation becomes an Expression' => [
                'integer',
                'bit',
                'integer',
                "B'1'::bit(1) || B'0'::bit(1)",
                new Expression("B'1'::bit(1) || B'0'::bit(1)"),
            ],
            'boolean false bare' => [
                'boolean',
                'bool',
                'boolean',
                'false',
                false,
            ],
            'boolean false cast notation' => [
                'boolean',
                'bool',
                'boolean',
                "'false'::boolean",
                false,
            ],
            'boolean true bare' => [
                'boolean',
                'bool',
                'boolean',
                'true',
                true,
            ],
            'boolean true cast notation' => [
                'boolean',
                'bool',
                'boolean',
                "'true'::boolean",
                true,
            ],
            'cast notation integer' => [
                'integer',
                'int4',
                'integer',
                "'42'::integer",
                42,
            ],
            'cast notation numeric kept as string' => [
                'decimal',
                'numeric',
                'string',
                "'33.22'::numeric",
                '33.22',
            ],
            'cast notation string' => [
                'string',
                'varchar',
                'string',
                "'hello'::character varying",
                'hello',
            ],
            'cast notation string with typmod' => [
                'string',
                'varchar',
                'string',
                "'xy'::character varying(10)",
                'xy',
            ],
            'cast of cast NULL becomes an Expression' => [
                'string',
                'varchar',
                'string',
                '(NULL::text)::character varying',
                new Expression('(NULL::text)::character varying'),
            ],
            'complex timezone literal expression' => [
                'timestamp',
                'timestamp',
                'string',
                "timezone('UTC'::text, '1970-01-01 00:00:00+00'::timestamp with time zone)",
                new Expression("timezone('UTC'::text, '1970-01-01 00:00:00+00'::timestamp with time zone)"),
            ],
            'CURRENT_DATE on date' => [
                'date',
                'date',
                'string',
                'CURRENT_DATE',
                new Expression('CURRENT_DATE'),
            ],
            'CURRENT_TIME on time' => [
                'time',
                'time',
                'string',
                'CURRENT_TIME',
                new Expression('CURRENT_TIME'),
            ],
            'CURRENT_TIMESTAMP on timestamp' => [
                'timestamp',
                'timestamp',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'integer expression becomes an Expression' => [
                'integer',
                'int4',
                'integer',
                '(1 + 2)',
                new Expression('(1 + 2)'),
            ],
            'jsonb expression becomes an Expression' => [
                'json',
                'jsonb',
                'array',
                'jsonb_build_array()',
                new Expression('jsonb_build_array()'),
            ],
            'jsonb literal decodes to array' => [
                'json',
                'jsonb',
                'array',
                '\'{"a": 1}\'::jsonb',
                ['a' => 1],
            ],
            'nextval sequence becomes an Expression' => [
                'integer',
                'int4',
                'integer',
                "nextval('t_seq'::regclass)",
                new Expression("nextval('t_seq'::regclass)"),
            ],
            'now() lowercase preserves original casing' => [
                'timestamp',
                'timestamp',
                'string',
                'now()',
                new Expression('now()'),
            ],
            'NOW() uppercase on timestamp' => [
                'timestamp',
                'timestamp',
                'string',
                'NOW()',
                new Expression('NOW()'),
            ],
            'null returns null' => [
                'timestamp',
                'timestamp',
                'string',
                null,
                null,
            ],
            'NULL cast notation returns null' => [
                'string',
                'varchar',
                'string',
                'NULL::character varying',
                null,
            ],
            'parenthesized NULL returns null' => [
                'string',
                'varchar',
                'string',
                '(NULL)::character varying',
                null,
            ],
            'parenthesized numeric kept as string' => [
                'decimal',
                'numeric',
                'string',
                '(0)::numeric',
                '0',
            ],
            'quoted bit \'10000010\' matches type fixture' => [
                'integer',
                'bit',
                'integer',
                '\'10000010\'::"bit"',
                130,
            ],
            'quoted bit literal' => [
                'integer',
                'bit',
                'integer',
                '\'101\'::"bit"',
                5,
            ],
            'quoted literal unescapes doubled quotes' => [
                'string',
                'varchar',
                'string',
                "'O''Reilly'::character varying",
                "O'Reilly",
            ],
            'text expression becomes an Expression' => [
                'text',
                'text',
                'string',
                "upper('abc'::text)",
                new Expression("upper('abc'::text)"),
            ],
            'timestamp literal not wrapped in expression' => [
                'timestamp',
                'timestamp',
                'string',
                "'2002-01-01 00:00:00'::timestamp without time zone",
                '2002-01-01 00:00:00',
            ],
            'timezone now expression' => [
                'timestamp',
                'timestamp',
                'string',
                "timezone('UTC'::text, now())",
                new Expression("timezone('UTC'::text, now())"),
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, mixed, mixed}>
     */
    public static function phpTypecast(): array
    {
        return [
            'boolean native false returns false' => [
                'boolean',
                'bool',
                'boolean',
                false,
                false,
            ],
            'boolean native true returns true' => [
                'boolean',
                'bool',
                'boolean',
                true,
                true,
            ],
            'boolean other string casts to true' => [
                'boolean',
                'bool',
                'boolean',
                '1',
                true,
            ],
            'boolean string f returns false' => [
                'boolean',
                'bool',
                'boolean',
                'f',
                false,
            ],
            'boolean string false returns false' => [
                'boolean',
                'bool',
                'boolean',
                'false',
                false,
            ],
            'boolean string t returns true' => [
                'boolean',
                'bool',
                'boolean',
                't',
                true,
            ],
            'boolean string true returns true' => [
                'boolean',
                'bool',
                'boolean',
                'true',
                true,
            ],
            'boolean zero string casts to false' => [
                'boolean',
                'bool',
                'boolean',
                '0',
                false,
            ],
            'integer fallback to parent' => [
                'integer',
                'int4',
                'integer',
                '42',
                42,
            ],
            'json array decodes to list' => [
                'json',
                'json',
                'string',
                '[1,2,3]',
                [1, 2, 3],
            ],
            'json object decodes to array' => [
                'json',
                'json',
                'string',
                '{"a":1}',
                ['a' => 1],
            ],
            'null returns null' => [
                'boolean',
                'bool',
                'boolean',
                null,
                null,
            ],
            'string fallback to parent' => [
                'string',
                'varchar',
                'string',
                'hello',
                'hello',
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

        $columns['int_col']['dbType'] = 'int4';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = 32;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'int4';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = 32;
        $columns['int_col2']['scale'] = 0;
        $columns['tinyint_col']['type'] = 'smallint';
        $columns['tinyint_col']['dbType'] = 'int2';
        $columns['tinyint_col']['size'] = null;
        $columns['tinyint_col']['precision'] = 16;
        $columns['tinyint_col']['scale'] = 0;
        $columns['smallint_col']['dbType'] = 'int2';
        $columns['smallint_col']['size'] = null;
        $columns['smallint_col']['precision'] = 16;
        $columns['smallint_col']['scale'] = 0;
        $columns['char_col']['dbType'] = 'bpchar';
        $columns['char_col']['precision'] = null;
        $columns['char_col2']['dbType'] = 'varchar';
        $columns['char_col2']['precision'] = null;
        $columns['float_col']['dbType'] = 'float8';
        $columns['float_col']['precision'] = 53;
        $columns['float_col']['scale'] = null;
        $columns['float_col']['size'] = null;
        $columns['float_col2']['dbType'] = 'float8';
        $columns['float_col2']['precision'] = 53;
        $columns['float_col2']['scale'] = null;
        $columns['float_col2']['size'] = null;
        $columns['blob_col']['dbType'] = 'bytea';
        $columns['blob_col']['phpType'] = 'resource';
        $columns['blob_col']['type'] = 'binary';
        $columns['numeric_col']['dbType'] = 'numeric';
        $columns['numeric_col']['size'] = null;
        $columns['bool_col']['type'] = 'boolean';
        $columns['bool_col']['phpType'] = 'boolean';
        $columns['bool_col']['dbType'] = 'bool';
        $columns['bool_col']['size'] = null;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col']['scale'] = null;
        $columns['bool_col2']['type'] = 'boolean';
        $columns['bool_col2']['phpType'] = 'boolean';
        $columns['bool_col2']['dbType'] = 'bool';
        $columns['bool_col2']['size'] = null;
        $columns['bool_col2']['precision'] = null;
        $columns['bool_col2']['scale'] = null;
        $columns['bool_col2']['defaultValue'] = true;
        $columns['bit_col']['dbType'] = 'bit';
        $columns['bit_col']['size'] = 8;
        $columns['bit_col']['precision'] = null;
        $columns['bigint_col'] = [
            'type' => 'bigint',
            'dbType' => 'int8',
            'phpType' => 'integer',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => 64,
            'scale' => 0,
            'defaultValue' => null,
        ];
        $columns['intarray_col'] = [
            'type' => 'integer',
            'dbType' => 'int4',
            'phpType' => 'integer',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => null,
            'scale' => null,
            'defaultValue' => null,
            'dimension' => 1,
        ];
        $columns['textarray2_col'] = [
            'type' => 'text',
            'dbType' => 'text',
            'phpType' => 'string',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => null,
            'scale' => null,
            'defaultValue' => null,
            'dimension' => 2,
        ];
        $columns['json_col'] = [
            'type' => 'json',
            'dbType' => 'json',
            'phpType' => 'array',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => null,
            'scale' => null,
            'defaultValue' => ['a' => 1],
            'dimension' => 0,
        ];
        $columns['jsonb_col'] = [
            'type' => 'json',
            'dbType' => 'jsonb',
            'phpType' => 'array',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => null,
            'scale' => null,
            'defaultValue' => null,
            'dimension' => 0,
        ];
        $columns['jsonarray_col'] = [
            'type' => 'json',
            'dbType' => 'json',
            'phpType' => 'array',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => null,
            'precision' => null,
            'scale' => null,
            'defaultValue' => null,
            'dimension' => 1,
        ];

        return $columns;
    }
}

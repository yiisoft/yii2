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
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ColumnSchemaProvider
{
    /**
     * @return array<string, array{string, string, string, mixed, mixed}>
     */
    public static function defaultPhpTypecast(): array
    {
        return [
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
            'bare string kept verbatim' => [
                'string',
                'varchar',
                'string',
                'hello',
                'hello',
            ],
            'binary bit B\'10101\' on bit(5)' => [
                'integer',
                'bit',
                'integer',
                "B'10101'::bit(5)",
                21,
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
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

use yii\db\Expression;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\ColumnSchemaTest} test cases.
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
            'bit default b\'0\' on bit(1)' => [
                'boolean',
                'bit(1)',
                'boolean',
                "b'0'",
                0,
            ],
            'bit default b\'1\' on bit(1)' => [
                'boolean',
                'bit(1)',
                'boolean',
                "b'1'",
                1,
            ],
            'bit default b\'10000010\' on bit(8) matches fixture' => [
                'integer',
                'bit(8)',
                'integer',
                "b'10000010'",
                130,
            ],
            'bit default b\'10101\' on bit(5)' => [
                'integer',
                'bit(5)',
                'integer',
                "b'10101'",
                21,
            ],
            'bit default with uppercase BIT dbType' => [
                'integer',
                'BIT(8)',
                'integer',
                "b'10000010'",
                130,
            ],
            'current_timestamp() lowercase on timestamp column (MariaDB >= 10.2.3)' => [
                'timestamp',
                'timestamp',
                'string',
                'current_timestamp()',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'current_timestamp(3) lowercase preserves precision' => [
                'datetime',
                'datetime',
                'string',
                'current_timestamp(3)',
                new Expression('CURRENT_TIMESTAMP(3)'),
            ],
            'CURRENT_TIMESTAMP on date column' => [
                'date',
                'date',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on datetime column' => [
                'datetime',
                'datetime',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on non-temporal column stays a literal' => [
                'string',
                'varchar(255)',
                'string',
                'CURRENT_TIMESTAMP',
                'CURRENT_TIMESTAMP',
            ],
            'CURRENT_TIMESTAMP on time column' => [
                'time',
                'time',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP on timestamp column' => [
                'timestamp',
                'timestamp',
                'string',
                'CURRENT_TIMESTAMP',
                new Expression('CURRENT_TIMESTAMP'),
            ],
            'CURRENT_TIMESTAMP(0) preserves zero precision' => [
                'timestamp',
                'timestamp',
                'string',
                'CURRENT_TIMESTAMP(0)',
                new Expression('CURRENT_TIMESTAMP(0)'),
            ],
            'CURRENT_TIMESTAMP(3) preserves precision' => [
                'datetime',
                'datetime',
                'string',
                'CURRENT_TIMESTAMP(3)',
                new Expression('CURRENT_TIMESTAMP(3)'),
            ],
            'CURRENT_TIMESTAMP(6) preserves precision' => [
                'timestamp',
                'timestamp',
                'string',
                'CURRENT_TIMESTAMP(6)',
                new Expression('CURRENT_TIMESTAMP(6)'),
            ],
            'decimal default kept as string' => [
                'decimal',
                'decimal(5,2)',
                'string',
                '33.22',
                '33.22',
            ],
            'double default cast to float' => [
                'double',
                'double',
                'double',
                '1.23',
                1.23,
            ],
            'JSON array default decodes to list' => [
                'json',
                'json',
                'array',
                '[1, 2, 3]',
                [1, 2, 3],
            ],
            'JSON object default decodes to array' => [
                'json',
                'json',
                'array',
                '{"key":"value"}',
                ['key' => 'value'],
            ],
            'null value returns null' => [
                'timestamp',
                'timestamp',
                'string',
                null,
                null,
            ],
            'regular integer default cast to int' => [
                'integer',
                'int',
                'integer',
                '42',
                42,
            ],
            'regular string default kept verbatim' => [
                'string',
                'varchar(255)',
                'string',
                'hello',
                'hello',
            ],
        ];
    }
}

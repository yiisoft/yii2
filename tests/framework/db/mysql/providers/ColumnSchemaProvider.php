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
 */
final class ColumnSchemaProvider extends \yiiunit\base\db\providers\ColumnSchemaProvider
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

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function expectedColumns(): array
    {
        $columns = [
            ...parent::expectedColumns(),
            'int_col' => [
                'type' => 'integer',
                'dbType' => 'int',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'int_col2' => [
                'type' => 'integer',
                'dbType' => 'int',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'int_col3' => [
                'type' => 'integer',
                'dbType' => 'int unsigned',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'tinyint_col' => [
                'type' => 'tinyint',
                'dbType' => 'tinyint',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'smallint_col' => [
                'type' => 'smallint',
                'dbType' => 'smallint',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'bigint_col' => [
                'type' => 'bigint',
                'dbType' => 'bigint unsigned',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'bit32' => [
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
            ],
            'bit33' => [
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
            ],
        ];

        return $columns;
    }

    /**
     * Prepares expected column metadata for the current MySQL server family.
     *
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function prepareColumnSchema(bool $isMariaDb, array $columns): array
    {
        if (!$isMariaDb) {
            return $columns;
        }

        $mariaDbOverrides = [
            'int_col' => ['int(11)', 11],
            'int_col2' => ['int(11)', 11],
            'int_col3' => ['int(11) unsigned', 11],
            'tinyint_col' => ['tinyint(3)', 3],
            'smallint_col' => ['smallint(1)', 1],
            'bigint_col' => ['bigint(20) unsigned', 20],
        ];

        foreach ($mariaDbOverrides as $column => [$dbType, $size]) {
            $columns[$column]['dbType'] = $dbType;
            $columns[$column]['size'] = $size;
            $columns[$column]['precision'] = $size;
        }

        return $columns;
    }
}

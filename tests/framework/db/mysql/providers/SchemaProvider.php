<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

use yiiunit\framework\db\AnyCaseValue;

/**
 * Data provider for MySQL schema constraint metadata test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class SchemaProvider extends \yiiunit\base\db\providers\SchemaProvider
{
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
                ]
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

    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = "`C_check` <> ''";
        $result['2: primary key'][2]->name = null;
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');

        return $result;
    }

    /**
     * Prepares expected schema constraint metadata for the current MySQL server family.
     */
    public static function prepareConstraintsExpected(
        bool $isMariaDb,
        string $tableName,
        string $type,
        mixed $expected,
    ): mixed {
        if ($isMariaDb || $type !== 'checks') {
            return $expected;
        }

        if ($tableName === 'T_constraints_1') {
            $expected[0]->expression = "(`C_check` <> _utf8mb4\\'\\')";
        }

        return $expected;
    }
}

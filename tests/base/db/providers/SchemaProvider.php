<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\providers;

use PDO;
use yii\db\CheckConstraint;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yiiunit\framework\db\AnyValue;

use function sprintf;

/**
 * Data provider for database schema test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class SchemaProvider
{
    /**
     * @return array<array{array<int, bool>}>
     */
    public static function pdoAttributes(): array
    {
        return [
            [[PDO::ATTR_EMULATE_PREPARES => true]],
            [[PDO::ATTR_EMULATE_PREPARES => false]],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function tableSchemaCachePrefixes(): array
    {
        $configs = [
            [
                'prefix' => '',
                'name' => 'type',
            ],
            [
                'prefix' => '',
                'name' => '{{%type}}',
            ],
            [
                'prefix' => 'ty',
                'name' => '{{%pe}}',
            ],
        ];

        $data = [];

        foreach ($configs as $config) {
            foreach ($configs as $testConfig) {
                if ($config === $testConfig) {
                    continue;
                }

                $description = sprintf(
                    "%s (with '%s' prefix) against %s (with '%s' prefix)",
                    $config['name'],
                    $config['prefix'],
                    $testConfig['name'],
                    $testConfig['prefix']
                );
                $data[$description] = [
                    $config['prefix'],
                    $config['name'],
                    $testConfig['prefix'],
                    $testConfig['name'],
                ];
            }
        }

        return $data;
    }

    /**
     * @return array<string, array{array<string, array<string, mixed>>}>
     */
    public static function columnSchema(): array
    {
        return [
            'type table columns' => [static::expectedColumns()],
        ];
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function columnSchemaDbTypecastBooleanPhpType(): array
    {
        return [
            ['0', false],
            ['1', true],
            [0, false],
            [1, true],
            // https://github.com/yiisoft/yii2/issues/9006
            ["\0", false],
            ["\1", true],
            // https://github.com/yiisoft/yii2/pull/20122
            ['FALSE', false],
            ['false', false],
            ['False', false],
            ['TRUE', true],
            ['true', true],
            ['True', true],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function expectedColumns(): array
    {
        return [
            'int_col' => [
                'type' => 'integer',
                'dbType' => 'int(11)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 11,
                'precision' => 11,
                'scale' => null,
                'defaultValue' => null,
            ],
            'int_col2' => [
                'type' => 'integer',
                'dbType' => 'int(11)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 11,
                'precision' => 11,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'tinyint_col' => [
                'type' => 'tinyint',
                'dbType' => 'tinyint(3)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 3,
                'precision' => 3,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'smallint_col' => [
                'type' => 'smallint',
                'dbType' => 'smallint(1)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'char_col' => [
                'type' => 'char',
                'dbType' => 'char(100)',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 100,
                'precision' => 100,
                'scale' => null,
                'defaultValue' => null,
            ],
            'char_col2' => [
                'type' => 'string',
                'dbType' => 'varchar(100)',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 100,
                'precision' => 100,
                'scale' => null,
                'defaultValue' => 'something',
            ],
            'char_col3' => [
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
            ],
            'enum_col' => [
                'type' => 'string',
                'dbType' => "enum('a','B','c,D')",
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => ['a', 'B', 'c,D'],
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'float_col' => [
                'type' => 'double',
                'dbType' => 'double(4,3)',
                'phpType' => 'double',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 4,
                'precision' => 4,
                'scale' => 3,
                'defaultValue' => null,
            ],
            'float_col2' => [
                'type' => 'double',
                'dbType' => 'double',
                'phpType' => 'double',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1.23,
            ],
            'blob_col' => [
                'type' => 'binary',
                'dbType' => 'blob',
                'phpType' => 'resource',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'numeric_col' => [
                'type' => 'decimal',
                'dbType' => 'decimal(5,2)',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 5,
                'precision' => 5,
                'scale' => 2,
                'defaultValue' => '33.22',
            ],
            'time' => [
                'type' => 'timestamp',
                'dbType' => 'timestamp',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => '2002-01-01 00:00:00',
            ],
            'bool_col' => [
                'type' => 'tinyint',
                'dbType' => 'tinyint(1)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => null,
            ],
            'bool_col2' => [
                'type' => 'tinyint',
                'dbType' => 'tinyint(1)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'ts_default' => [
                'type' => 'timestamp',
                'dbType' => 'timestamp',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => new Expression('CURRENT_TIMESTAMP'),
            ],
            'bit_col' => [
                'type' => 'integer',
                'dbType' => 'bit(8)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 8,
                'precision' => 8,
                'scale' => null,
                'defaultValue' => 130, // b'10000010'
            ],
            'json_col' => [
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
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        return [
            '1: primary key' => [
                'T_constraints_1',
                'primaryKey',
                new Constraint(
                    [
                        'name' => AnyValue::getInstance(),
                        'columnNames' => ['C_id'],
                    ],
                ),
            ],
            '1: check' => [
                'T_constraints_1',
                'checks',
                [
                    new CheckConstraint(
                        [
                            'name' => AnyValue::getInstance(),
                            'columnNames' => ['C_check'],
                            'expression' => "C_check <> ''",
                        ],
                    ),
                ],
            ],
            '1: unique' => [
                'T_constraints_1',
                'uniques',
                [
                    new Constraint(
                        [
                            'name' => 'CN_unique',
                            'columnNames' => ['C_unique'],
                        ],
                    ),
                ],
            ],
            '1: index' => [
                'T_constraints_1',
                'indexes',
                [
                    new IndexConstraint(
                        [
                            'name' => AnyValue::getInstance(),
                            'columnNames' => ['C_id'],
                            'isUnique' => true,
                            'isPrimary' => true,
                        ],
                    ),
                    new IndexConstraint(
                        [
                            'name' => 'CN_unique',
                            'columnNames' => ['C_unique'],
                            'isPrimary' => false,
                            'isUnique' => true,
                        ],
                    ),
                ],
            ],
            '1: default' => [
                'T_constraints_1',
                'defaultValues',
                false,
            ],
            '2: primary key' => [
                'T_constraints_2',
                'primaryKey',
                new Constraint(
                    [
                        'name' => 'CN_pk',
                        'columnNames' => [
                            'C_id_1',
                            'C_id_2',
                        ],
                    ],
                ),
            ],
            '2: unique' => [
                'T_constraints_2',
                'uniques',
                [
                    new Constraint(
                        [
                            'name' => 'CN_constraints_2_multi',
                            'columnNames' => [
                                'C_index_2_1',
                                'C_index_2_2',
                            ],
                        ],
                    ),
                ],
            ],
            '2: index' => [
                'T_constraints_2',
                'indexes',
                [
                    new IndexConstraint(
                        [
                            'name' => AnyValue::getInstance(),
                            'columnNames' => [
                                'C_id_1',
                                'C_id_2',
                            ],
                            'isUnique' => true,
                            'isPrimary' => true,
                        ],
                    ),
                    new IndexConstraint(
                        [
                            'name' => 'CN_constraints_2_single',
                            'columnNames' => ['C_index_1'],
                            'isPrimary' => false,
                            'isUnique' => false,
                        ],
                    ),
                    new IndexConstraint(
                        [
                            'name' => 'CN_constraints_2_multi',
                            'columnNames' => [
                                'C_index_2_1',
                                'C_index_2_2',
                            ],
                            'isPrimary' => false,
                            'isUnique' => true,
                        ],
                    ),
                ],
            ],
            '2: check' => [
                'T_constraints_2',
                'checks',
                [],
            ],
            '2: default' => [
                'T_constraints_2',
                'defaultValues',
                false,
            ],
            '3: primary key' => [
                'T_constraints_3',
                'primaryKey',
                null,
            ],
            '3: foreign key' => [
                'T_constraints_3',
                'foreignKeys',
                [
                    new ForeignKeyConstraint(
                        [
                            'name' => 'CN_constraints_3',
                            'columnNames' => [
                                'C_fk_id_1',
                                'C_fk_id_2',
                            ],
                            'foreignTableName' => 'T_constraints_2',
                            'foreignColumnNames' => [
                                'C_id_1',
                                'C_id_2',
                            ],
                            'onDelete' => 'CASCADE',
                            'onUpdate' => 'CASCADE',
                        ],
                    ),
                ],
            ],
            '3: unique' => [
                'T_constraints_3',
                'uniques',
                [],
            ],
            '3: index' => [
                'T_constraints_3',
                'indexes',
                [
                    new IndexConstraint(
                        [
                            'name' => 'CN_constraints_3',
                            'columnNames' => [
                                'C_fk_id_1',
                                'C_fk_id_2',
                            ],
                            'isUnique' => false,
                            'isPrimary' => false,
                        ]
                    ),
                ],
            ],
            '3: check' => [
                'T_constraints_3',
                'checks',
                [],
            ],
            '3: default' => [
                'T_constraints_3',
                'defaultValues',
                false,
            ],
            '4: primary key' => [
                'T_constraints_4',
                'primaryKey',
                new Constraint(
                    [
                        'name' => AnyValue::getInstance(),
                        'columnNames' => ['C_id'],
                    ],
                ),
            ],
            '4: unique' => [
                'T_constraints_4',
                'uniques',
                [
                    new Constraint(
                        [
                            'name' => 'CN_constraints_4',
                            'columnNames' => ['C_col_1', 'C_col_2'],
                        ],
                    ),
                ],
            ],
            '4: check' => [
                'T_constraints_4',
                'checks',
                [],
            ],
            '4: default' => [
                'T_constraints_4',
                'defaultValues',
                false,
            ],
        ];
    }
}

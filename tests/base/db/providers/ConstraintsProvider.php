<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db\providers;

use yii\db\CheckConstraint;
use yii\db\Constraint;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yiiunit\framework\db\AnyValue;

/**
 * Data provider for {@see \yiiunit\base\db\BaseSchemaConstraints} test cases.
 */
class ConstraintsProvider
{
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

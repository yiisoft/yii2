<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\providers;

use yii\db\Constraint;
use yiiunit\framework\db\AnyCaseValue;

/**
 * Data provider for {@see \yiiunit\framework\db\mysql\SchemaConstraintsTest} test cases.
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
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
     * @return array<string, array{string, string[]}>
     */
    public static function schemaQualifiedTablePrimaryKey(): array
    {
        return [
            'current database qualified' => ['yiitest.T_constraints_2', ['C_id_1', 'C_id_2']],
            'cross database qualified' => ['yiitest_cross.T_constraints_2', ['C_cross_id']],
        ];
    }

    /**
     * @return array<string, array{string, string, string[]}>
     */
    public static function schemaQualifiedTableIndexes(): array
    {
        return [
            'current database qualified' => ['yiitest.T_constraints_2', 'CN_constraints_2_single', ['C_index_1']],
            'cross database qualified' => ['yiitest_cross.T_constraints_2', 'CN_cross_single', ['C_cross_index']],
        ];
    }

    /**
     * @return array<string, array{string, string, string[]}>
     */
    public static function schemaQualifiedTableUniques(): array
    {
        return [
            'current database qualified' => [
                'yiitest.T_constraints_2',
                'CN_constraints_2_multi',
                ['C_index_2_1', 'C_index_2_2'],
            ],
            'cross database qualified' => ['yiitest_cross.T_constraints_2', 'CN_cross_unique', ['C_cross_unique']],
        ];
    }

    /**
     * @return array<string, array{string, string, string[], string|null, string, string[]}>
     */
    public static function schemaQualifiedTableForeignKeys(): array
    {
        return [
            'unqualified referencing current database' => [
                'T_constraints_3',
                'CN_constraints_3',
                ['C_fk_id_1', 'C_fk_id_2'],
                null,
                'T_constraints_2',
                ['C_id_1', 'C_id_2'],
            ],
            'qualified referencing current database' => [
                'yiitest.T_constraints_3',
                'CN_constraints_3',
                ['C_fk_id_1', 'C_fk_id_2'],
                'yiitest',
                'T_constraints_2',
                ['C_id_1', 'C_id_2'],
            ],
            'unqualified referencing cross database' => [
                'T_constraints_cross_ref',
                'CN_constraints_cross_ref',
                ['C_cross_fk_id'],
                'yiitest_cross',
                'T_constraints_2',
                ['C_cross_id'],
            ],
            'qualified referencing cross database' => [
                'yiitest_cross.T_constraints_3',
                'CN_cross_constraints_3',
                ['C_fk_id'],
                'yiitest_cross',
                'T_constraints_2',
                ['C_cross_id'],
            ],
            'qualified cross database child referencing current database' => [
                'yiitest_cross.T_constraints_cross_ref',
                'CN_cross_constraints_cross_ref',
                ['C_fk_id_1', 'C_fk_id_2'],
                'yiitest',
                'T_constraints_2',
                ['C_id_1', 'C_id_2'],
            ],
        ];
    }

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

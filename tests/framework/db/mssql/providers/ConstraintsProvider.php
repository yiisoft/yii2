<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\providers;

use yii\db\Constraint;
use yii\db\DefaultValueConstraint;
use yiiunit\framework\db\AnyValue;

/**
 * Data provider for {@see \yiiunit\framework\db\mssql\SchemaConstraintsTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ConstraintsProvider extends \yiiunit\base\db\providers\ConstraintsProvider
{
    /**
     * @return array<string, array{string, string, string|null}>
     */
    public static function compositePrimaryKeyColumnOrder(): array
    {
        return [
            'across database' => [
                'tempdb.dbo.test_composite_pk_cross_db',
                'PK_test_composite_cross_db',
                'tempdb',
            ],
            'current database' => [
                'test_composite_pk',
                'PK_test_composite',
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string|null}>
     */
    public static function compositeUniqueConstraintColumnOrder(): array
    {
        return [
            'across database' => [
                'tempdb.dbo.test_composite_uq_cross_db',
                'UQ_test_composite_cross_db',
                'tempdb',
            ],
            'current database' => [
                'test_composite_uq',
                'UQ_test_composite',
                null,
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function catalogQualifiedTableMetadata(): array
    {
        return [
            'current database with bracketed special names' => [
                'dbo.[test.metadata parent]',
                'dbo.[test.metadata child]',
                'special',
            ],
            'current database with normal names' => [
                'test_metadata_parent_current',
                'test_metadata_child_current',
                'current',
            ],
            'tempdb catalog with normal names' => [
                'tempdb.dbo.test_metadata_parent_cross_db',
                'tempdb.dbo.test_metadata_child_cross_db',
                'cross_db',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function catalogQualifiedTableSchemaForeignKeys(): array
    {
        return [
            'current database with bracketed special names' => [
                'dbo.[test.metadata parent]',
                'dbo.[test.metadata child]',
                'test.metadata parent',
                'special',
            ],
            'current database with normal names' => [
                'test_metadata_parent_current',
                'test_metadata_child_current',
                'test_metadata_parent_current',
                'current',
            ],
            'tempdb catalog with normal names' => [
                'tempdb.dbo.test_metadata_parent_cross_db',
                'tempdb.dbo.test_metadata_child_cross_db',
                'test_metadata_parent_cross_db',
                'cross_db',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function catalogQualifiedTableForeignKeys(): array
    {
        return [
            'current database with bracketed special names' => [
                'dbo.[test.metadata parent]',
                'dbo.[test.metadata child]',
                'dbo',
                'test.metadata parent',
                'special',
            ],
            'current database with normal names' => [
                'test_metadata_parent_current',
                'test_metadata_child_current',
                'dbo',
                'test_metadata_parent_current',
                'current',
            ],
            'tempdb catalog with normal names' => [
                'tempdb.dbo.test_metadata_parent_cross_db',
                'tempdb.dbo.test_metadata_child_cross_db',
                'dbo',
                'test_metadata_parent_cross_db',
                'cross_db',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string, Constraint|bool|array<array-key, mixed>|null}>
     */
    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->expression = '([C_check]<>\'\')';
        $result['1: default'][2] = [];
        $result['1: default'][2][] = new DefaultValueConstraint(
            [
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_default'],
                'value' => '((0))',
            ],
        );
        $result['2: default'][2] = [];
        $result['3: foreign key'][2][0]->foreignSchemaName = 'dbo';
        $result['3: index'][2] = [];
        $result['3: default'][2] = [];
        $result['4: default'][2] = [];

        return $result;
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite\providers;

use yii\db\Constraint;
use yiiunit\framework\db\AnyValue;

/**
 * Data provider for SQLite schema constraint metadata test cases.
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
        $columns = parent::expectedColumns();

        unset($columns['enum_col']);
        unset($columns['bit_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['bool_col']['type'] = 'boolean';
        $columns['bool_col']['phpType'] = 'boolean';
        $columns['bool_col2']['type'] = 'boolean';
        $columns['bool_col2']['phpType'] = 'boolean';
        $columns['bool_col2']['defaultValue'] = true;

        return $columns;
    }

    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: primary key'][2]->name = null;
        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        $result['1: unique'][2][0]->name = AnyValue::getInstance();
        $result['1: index'][2][1]->name = AnyValue::getInstance();
        $result['2: primary key'][2]->name = null;
        $result['2: unique'][2][0]->name = AnyValue::getInstance();
        $result['2: index'][2][2]->name = AnyValue::getInstance();
        $result['3: foreign key'][2][0]->name = null;
        $result['3: index'][2] = [];
        $result['4: primary key'][2]->name = null;
        $result['4: unique'][2][0]->name = AnyValue::getInstance();
        $result['5: primary key'] = [
            'T_upsert', 'primaryKey',
            new Constraint(
                [
                    'name' => AnyValue::getInstance(),
                    'columnNames' => ['id'],
                ],
            ),
        ];

        return $result;
    }

    /**
     * Provides SQLite table names and their quoted equivalents.
     *
     * @return list<array{string, string}>
     */
    public static function quoteTableName(): array
    {
        return [
            ['`test`', '`test`'],
            ['`test`.`test`', '`test`.`test`'],
            ['test', '`test`'],
            ['test.`test`.test', '`test`.`test`.`test`'],
            ['test.test', '`test`.`test`'],
            ['test.test.test', '`test`.`test`.`test`'],
        ];
    }
}

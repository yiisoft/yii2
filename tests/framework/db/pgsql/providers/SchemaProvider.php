<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql\providers;

/**
 * Data provider for PostgreSQL schema constraint metadata test cases.
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

    public static function constraints(): array
    {
        $result = parent::constraints();

        $result['1: check'][2][0]->expression = 'CHECK ((("C_check")::text <> \'\'::text))';
        $result['3: foreign key'][2][0]->foreignSchemaName = 'public';
        $result['3: index'][2] = [];

        return $result;
    }
}

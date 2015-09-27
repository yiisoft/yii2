<?php

namespace yiiunit\framework\db\mssql;

use yii\db\Expression;
use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group mssql
 */
class MssqlSchemaTest extends SchemaTest
{
    public $driverName = 'sqlsrv';
    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        $columns['int_col']['dbType'] = 'int';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col']['autoIncrement'] = false;
        $columns['int_col2']['dbType'] = 'int';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['int_col3']['dbType'] = 'int';
        $columns['int_col3']['size'] = null;
        $columns['int_col3']['precision'] = null;
        $columns['smallint_col']['dbType'] = 'smallint';
        $columns['smallint_col']['size'] = null;
        $columns['smallint_col']['precision'] = null;
        $columns['char_col']['dbType'] = 'char';
        $columns['char_col']['size'] = null;
        $columns['char_col']['precision'] = null;
        $columns['char_col2']['dbType'] = 'varchar';
        $columns['char_col2']['size'] = null;
        $columns['char_col2']['precision'] = null;
        $columns['escape_col']['dbType'] = 'varchar';
        $columns['escape_col']['size'] = null;
        $columns['escape_col']['precision'] = null;
        $columns['escape_col']['defaultValue'] = "fo''o\\\\ba'r";
        $columns['func_default']['dbType'] = 'varchar';
        $columns['func_default']['size'] = null;
        $columns['func_default']['precision'] = null;
        $columns['func_default']['defaultValue'] = new Expression("replace('xxxbarxxx','x','')");
        $columns['float_col']['dbType'] = 'decimal';
        $columns['float_col']['type'] = 'decimal';
        $columns['float_col']['phpType'] = 'string';
        $columns['float_col']['size'] = null;
        $columns['float_col']['precision'] = null;
        $columns['float_col']['scale'] = null;
        $columns['float_col2']['dbType'] = 'float';
        $columns['float_col2']['type'] = 'float';
        $columns['float_col2']['phpType'] = 'double';
        $columns['float_col2']['size'] = null;
        $columns['float_col2']['precision'] = null;
        $columns['float_col2']['scale'] = null;
        $columns['float_col3']['dbType'] = 'float';
        $columns['float_col3']['type'] = 'float';
        $columns['float_col3']['phpType'] = 'double';
        $columns['float_col3']['size'] = null;
        $columns['float_col3']['precision'] = null;
        $columns['float_col3']['scale'] = null;
        $columns['blob_col']['dbType'] = 'varbinary';
        $columns['numeric_col']['dbType'] = 'decimal';
        $columns['numeric_col']['size'] = null;
        $columns['numeric_col']['precision'] = null;
        $columns['numeric_col']['scale'] = null;
        $columns['time']['dbType'] = 'datetime';
        $columns['time']['type'] = 'datetime';
        $columns['bool_col']['dbType'] = 'tinyint';
        $columns['bool_col']['type'] = 'smallint';
        $columns['bool_col']['size'] = null;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['dbType'] = 'tinyint';
        $columns['bool_col2']['type'] = 'smallint';
        $columns['bool_col2']['size'] = null;
        $columns['bool_col2']['precision'] = null;
        $columns['ts_default']['dbType'] = 'datetime';
        $columns['ts_default']['type'] = 'datetime';
        $columns['ts_default']['defaultValue'] = new Expression('getdate()');
        $columns['bit_col']['dbType'] = 'binary';
        $columns['bit_col']['type'] = 'binary';
        $columns['bit_col']['phpType'] = 'resource';
        $columns['bit_col']['size'] = null;
        $columns['bit_col']['precision'] = null;
        $columns['bit_col']['defaultValue'] = '0xF2';
        return $columns;
    }
}

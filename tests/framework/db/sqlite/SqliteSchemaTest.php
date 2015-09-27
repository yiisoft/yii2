<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteSchemaTest extends SchemaTest
{
    protected $driverName = 'sqlite';

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        unset($columns['bit_col']);
        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['autoIncrement'] = false;
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['int_col3']['dbType'] = 'integer';
        $columns['int_col3']['size'] = null;
        $columns['int_col3']['precision'] = null;
        $columns['escape_col']['dbType'] = 'varchar(100)';
        $columns['escape_col']['precision'] = 100;
        $columns['escape_col']['defaultValue'] = 'fo\'\'o\ba\'r';
        $columns['func_default']['type'] = 'text';
        $columns['func_default']['dbType'] = 'text';
        $columns['func_default']['defaultValue'] = new \yii\db\Expression("TRIM('xxxbarxxx', 'x')");
        $columns['func_default']['precision'] = null;
        $columns['func_default']['size'] = null;
        $columns['bool_col']['type'] = 'boolean';
        $columns['bool_col']['phpType'] = 'boolean';
        $columns['bool_col2']['type'] = 'boolean';
        $columns['bool_col2']['phpType'] = 'boolean';
        $columns['bool_col2']['defaultValue'] = true;
        return $columns;
    }

}

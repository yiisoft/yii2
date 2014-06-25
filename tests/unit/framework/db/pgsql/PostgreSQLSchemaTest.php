<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\pgsql\Schema;
use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group pgsql
 */
class PostgreSQLSchemaTest extends SchemaTest
{
    public $driverName = 'pgsql';

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
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
}

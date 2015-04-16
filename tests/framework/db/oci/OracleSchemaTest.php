<?php

namespace yiiunit\framework\db\oci;

use yii\db\Expression;
use yii\db\oci\Schema;
use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group oci
 */
class OracleSchemaTest extends SchemaTest
{
    public $driverName = 'oci';

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        $columns['int_col']['dbType'] = 'NUMBER';
        $columns['int_col']['size'] = 22;
        $columns['int_col']['precision'] = null;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'NUMBER';
        $columns['int_col2']['size'] = 22;
        $columns['int_col2']['precision'] = null;
        $columns['int_col2']['scale'] = 0;
        $columns['smallint_col']['dbType'] = 'NUMBER';
        $columns['smallint_col']['type'] = 'integer';
        $columns['smallint_col']['size'] = 22;
        $columns['smallint_col']['precision'] = null;
        $columns['smallint_col']['scale'] = 0;
        $columns['char_col']['dbType'] = 'CHAR';
        $columns['char_col']['precision'] = null;
        $columns['char_col']['size'] = 100;
        $columns['char_col2']['dbType'] = 'VARCHAR2';
        $columns['char_col2']['precision'] = null;
        $columns['char_col2']['size'] = 100;
        $columns['char_col3']['type'] = 'string';
        $columns['char_col3']['dbType'] = 'VARCHAR2';
        $columns['char_col3']['precision'] = null;
        $columns['char_col3']['size'] = 4000;
        $columns['float_col']['dbType'] = 'FLOAT';
        $columns['float_col']['precision'] = 126;
        $columns['float_col']['scale'] = null;
        $columns['float_col']['size'] = 22;
        $columns['float_col2']['dbType'] = 'FLOAT';
        $columns['float_col2']['precision'] = 126;
        $columns['float_col2']['scale'] = null;
        $columns['float_col2']['size'] = 22;
        $columns['blob_col']['dbType'] = 'BLOB';
        $columns['blob_col']['phpType'] = 'resource';
        $columns['blob_col']['type'] = 'binary';
        $columns['blob_col']['size'] = 4000;
        $columns['numeric_col']['dbType'] = 'NUMBER';
        $columns['numeric_col']['size'] = 22;
        $columns['time']['dbType'] = 'TIMESTAMP(6)';
        $columns['time']['size'] = 11;
        $columns['time']['scale'] = 6;
        $columns['time']['defaultValue'] = null;
        $columns['bool_col']['type'] = 'string';
        $columns['bool_col']['phpType'] = 'string';
        $columns['bool_col']['dbType'] = 'CHAR';
        $columns['bool_col']['size'] = 1;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['type'] = 'string';
        $columns['bool_col2']['phpType'] = 'string';
        $columns['bool_col2']['dbType'] = 'CHAR';
        $columns['bool_col2']['size'] = 1;
        $columns['bool_col2']['precision'] = null;
        $columns['bool_col2']['defaultValue'] = '1';
        $columns['ts_default']['type'] = 'timestamp';
        $columns['ts_default']['phpType'] = 'string';
        $columns['ts_default']['dbType'] = 'TIMESTAMP(6)';
        $columns['ts_default']['scale'] = 6;
        $columns['ts_default']['size'] = 11;
        $columns['ts_default']['defaultValue'] = null;
        $columns['bit_col']['type'] = 'string';
        $columns['bit_col']['phpType'] = 'string';
        $columns['bit_col']['dbType'] = 'CHAR';
        $columns['bit_col']['size'] = 3;
        $columns['bit_col']['precision'] = null;
        $columns['bit_col']['defaultValue'] = '130';
        return $columns;
    }

    /**
     * Autoincrement columns detection should be disabled for Oracle
     * because there is no way of associating a column with a sequence.
     */
    public function testAutoincrementDisabled()
    {
        $table = $this->getConnection(false)->schema->getTableSchema('order', true);
        $this->assertSame(false, $table->columns['id']->autoIncrement);
    }
}

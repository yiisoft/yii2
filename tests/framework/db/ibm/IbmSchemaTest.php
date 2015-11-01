<?php

namespace yiiunit\framework\db\ibm;

use yii\db\Expression;
use yii\db\ibm\Schema;
use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group ibm
 */
class IbmSchemaTest extends SchemaTest
{
    public $driverName = 'ibm';

    public function testGetPDOType()
    {
        $values = [
            [null, \PDO::PARAM_INT],
            ['', \PDO::PARAM_STR],
            ['hello', \PDO::PARAM_STR],
            [0, \PDO::PARAM_INT],
            [1, \PDO::PARAM_INT],
            [1337, \PDO::PARAM_INT],
            [true, \PDO::PARAM_INT],
            [false, \PDO::PARAM_INT],
            [$fp = fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
        ];

        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]), 'type for value ' . print_r($value[0], true) . ' does not match.');
        }
        fclose($fp);
    }

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        $columns['int_col']['dbType'] = 'INTEGER';
        $columns['int_col']['size'] = 4;
        $columns['int_col']['precision'] = 4;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'INTEGER';
        $columns['int_col2']['size'] = 4;
        $columns['int_col2']['precision'] = 4;
        $columns['int_col2']['scale'] = 0;
        $columns['int_col2']['defaultValue'] = '1';
        $columns['smallint_col']['dbType'] = 'SMALLINT';
        $columns['smallint_col']['size'] = 2;
        $columns['smallint_col']['precision'] = 2;
        $columns['smallint_col']['scale'] = 0;
        $columns['smallint_col']['defaultValue'] = '1';
        $columns['char_col']['dbType'] = 'CHARACTER(100)';
        $columns['char_col']['scale'] = 0;
        $columns['char_col2']['dbType'] = 'VARCHAR(100)';
        $columns['char_col2']['scale'] = 0;
        $columns['char_col3']['dbType'] = 'CLOB(1048576)';
        $columns['char_col3']['size'] = 1048576;
        $columns['char_col3']['precision'] = 1048576;
        $columns['char_col3']['scale'] = 0;
        $columns['float_col']['dbType'] = 'DOUBLE(8,0)';
        $columns['float_col']['size'] = 8;
        $columns['float_col']['precision'] = 8;
        $columns['float_col']['scale'] = 0;
        $columns['float_col2']['dbType'] = 'DOUBLE(8,0)';
        $columns['float_col2']['size'] = 8;
        $columns['float_col2']['precision'] = 8;
        $columns['float_col2']['scale'] = 0;
        $columns['float_col2']['defaultValue'] = '1.23';
        $columns['blob_col']['dbType'] = 'BLOB(1048576)';
        $columns['blob_col']['size'] = 1048576;
        $columns['blob_col']['precision'] = 1048576;
        $columns['blob_col']['scale'] = 0;
        $columns['numeric_col']['dbType'] = 'DECIMAL(5,2)';
        $columns['numeric_col']['size'] = 5;
        $columns['numeric_col']['precision'] = 5;
        $columns['numeric_col']['scale'] = 2;
        $columns['time']['dbType'] = 'TIMESTAMP';
        $columns['time']['size'] = 10;
        $columns['time']['precision'] = 10;
        $columns['time']['scale'] = 6;
        $columns['time']['defaultValue'] = '2002-01-01-00.00.00.000000';
        $columns['bool_col']['dbType'] = 'SMALLINT';
        $columns['bool_col']['size'] = 2;
        $columns['bool_col']['precision'] = 2;
        $columns['bool_col']['scale'] = 0;
        $columns['bool_col2']['dbType'] = 'SMALLINT';
        $columns['bool_col2']['size'] = 2;
        $columns['bool_col2']['precision'] = 2;
        $columns['bool_col2']['scale'] = 0;
        $columns['bool_col2']['defaultValue'] = '1';
        $columns['ts_default']['dbType'] = 'TIMESTAMP';
        $columns['ts_default']['size'] = 10;
        $columns['ts_default']['precision'] = 10;
        $columns['ts_default']['scale'] = 6;
        $columns['ts_default']['defaultValue'] = new Expression('CURRENT TIMESTAMP');
        $columns['bit_col']['type'] = 'smallint';
        $columns['bit_col']['dbType'] = 'SMALLINT';
        $columns['bit_col']['size'] = 2;
        $columns['bit_col']['precision'] = 2;
        $columns['bit_col']['scale'] = 0;
        $columns['bit_col']['defaultValue'] = '130';
        return $columns;
    }
}

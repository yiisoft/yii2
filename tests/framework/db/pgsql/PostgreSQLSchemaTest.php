<?php

namespace yiiunit\framework\db\pgsql;

use yii\db\Expression;
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
        $columns['int_col']['dbType'] = 'int4';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = 32;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'int4';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = 32;
        $columns['int_col2']['scale'] = 0;
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
        $columns['ts_default']['defaultValue'] = new Expression('now()');
        $columns['bit_col']['dbType'] = 'bit';
        $columns['bit_col']['size'] = 8;
        $columns['bit_col']['precision'] = null;
        return $columns;
    }

    public function testGetPDOType()
    {
        $values = [
            [null, \PDO::PARAM_NULL],
            ['', \PDO::PARAM_STR],
            ['hello', \PDO::PARAM_STR],
            [0, \PDO::PARAM_INT],
            [1, \PDO::PARAM_INT],
            [1337, \PDO::PARAM_INT],
            [true, \PDO::PARAM_BOOL],
            [false, \PDO::PARAM_BOOL],
            [$fp = fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
        ];

        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]));
        }
        fclose($fp);
    }

    public function testBooleanDefaultValues()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('bool_values');
        $this->assertSame(true, $table->getColumn('default_true')->defaultValue);
        $this->assertSame(false, $table->getColumn('default_false')->defaultValue);
    }

    public function testFindSchemaNames()
    {
        $schema = $this->getConnection()->schema;

        $this->assertEquals(3, count($schema->getSchemaNames()));
    }
}

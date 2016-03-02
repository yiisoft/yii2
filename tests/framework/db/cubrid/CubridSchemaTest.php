<?php
namespace yiiunit\framework\db\cubrid;

use yii\db\Expression;
use yiiunit\framework\db\SchemaTest;

/**
 * @group db
 * @group cubrid
 */
class CubridSchemaTest extends SchemaTest
{
    public $driverName = 'cubrid';

    public function testGetPDOType()
    {
        $values = [
            [null, \PDO::PARAM_NULL],
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
            $this->assertEquals($value[1], $schema->getPdoType($value[0]));
        }
        fclose($fp);
    }


    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['smallint_col']['dbType'] = 'short';
        $columns['smallint_col']['size'] = null;
        $columns['smallint_col']['precision'] = null;
        $columns['char_col3']['type'] = 'string';
        $columns['char_col3']['dbType'] = 'varchar(1073741823)';
        $columns['char_col3']['size'] = 1073741823;
        $columns['char_col3']['precision'] = 1073741823;
        $columns['enum_col']['dbType'] = "enum('a', 'B')";
        $columns['float_col']['dbType'] = 'double';
        $columns['float_col']['size'] = null;
        $columns['float_col']['precision'] = null;
        $columns['float_col']['scale'] = null;
        $columns['numeric_col']['dbType'] = 'numeric(5,2)';
        $columns['blob_col']['phpType'] = 'resource';
        $columns['blob_col']['type'] = 'binary';
        $columns['bool_col']['dbType'] = 'short';
        $columns['bool_col']['size'] = null;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['dbType'] = 'short';
        $columns['bool_col2']['size'] = null;
        $columns['bool_col2']['precision'] = null;
        $columns['time']['defaultValue'] = '12:00:00 AM 01/01/2002';
        $columns['ts_default']['defaultValue'] = new Expression('SYS_TIMESTAMP');
        return $columns;
    }
}

<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\cubrid;

use yii\db\Expression;
use yiiunit\framework\db\AnyCaseValue;

/**
 * @group db
 * @group cubrid
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'cubrid';

    public function testGetSchemaNames()
    {
        $this->markTestSkipped('Schemas are not supported in CUBRID.');
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
            [true, \PDO::PARAM_INT],
            [false, \PDO::PARAM_INT],
            [$fp = fopen(__FILE__, 'rb'), \PDO::PARAM_LOB],
        ];

        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]));
        }
        fclose($fp);
    }


    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['json_col']);
        $columns['int_col']['dbType'] = 'integer';
        $columns['int_col']['size'] = null;
        $columns['int_col']['precision'] = null;
        $columns['int_col2']['dbType'] = 'integer';
        $columns['int_col2']['size'] = null;
        $columns['int_col2']['precision'] = null;
        $columns['tinyint_col']['smallint'] = 'short';
        $columns['tinyint_col']['dbType'] = 'short';
        $columns['tinyint_col']['size'] = null;
        $columns['tinyint_col']['precision'] = null;
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

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        foreach ($result as $name => $constraints) {
            $result[$name][2] = $this->convertPropertiesToAnycase($constraints[2]);
        }
        $result['1: check'][2] = false;
        unset($result['1: index'][2][0]);

        $result['2: check'][2] = false;
        unset($result['2: index'][2][0]);

        $result['3: foreign key'][2][0]->onDelete = 'RESTRICT';
        $result['3: foreign key'][2][0]->onUpdate = 'RESTRICT';
        $result['3: index'][2] = [];
        $result['3: check'][2] = false;

        $result['4: check'][2] = false;
        return $result;
    }

    public function lowercaseConstraintsProvider()
    {
        $this->markTestSkipped('This test hangs on CUBRID.');
    }

    public function uppercaseConstraintsProvider()
    {
        $this->markTestSkipped('This test hangs on CUBRID.');
    }

    /**
     * @param array|object|string $object
     * @param bool $isProperty
     * @return array|object|string
     */
    private function convertPropertiesToAnycase($object, $isProperty = false)
    {
        if (!$isProperty && \is_array($object)) {
            $result = [];
            foreach ($object as $name => $value) {
                $result[] = $this->convertPropertiesToAnycase($value);
            }

            return $result;
        }

        if (\is_object($object)) {
            foreach (array_keys((array) $object) as $name) {
                $object->$name = $this->convertPropertiesToAnycase($object->$name, true);
            }
        } elseif (\is_array($object) || \is_string($object)) {
            $object = new AnyCaseValue($object);
        }

        return $object;
    }
}

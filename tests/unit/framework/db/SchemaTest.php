<?php

namespace yiiunit\framework\db;

use yii\caching\FileCache;
use yii\db\Expression;
use yii\db\Schema;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends DatabaseTestCase
{
    public function testGetTableNames()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $tables = $schema->getTableNames();
        $this->assertTrue(in_array('customer', $tables));
        $this->assertTrue(in_array('category', $tables));
        $this->assertTrue(in_array('item', $tables));
        $this->assertTrue(in_array('order', $tables));
        $this->assertTrue(in_array('order_item', $tables));
        $this->assertTrue(in_array('type', $tables));
    }

    public function testGetTableSchemas()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $tables = $schema->getTableSchemas();
        $this->assertEquals(count($schema->getTableNames()), count($tables));
        foreach ($tables as $table) {
            $this->assertInstanceOf('yii\db\TableSchema', $table);
        }
    }

    public function testGetNonExistingTableSchema()
    {
        $this->assertNull($this->getConnection()->schema->getTableSchema('nonexisting_table'));
    }

    public function testSchemaCache()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();
        $noCacheTable = $schema->getTableSchema('type', true);
        $cachedTable = $schema->getTableSchema('type', true);
        $this->assertEquals($noCacheTable, $cachedTable);
    }

    public function testCompositeFk()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('composite_fk');

        $this->assertCount(1, $table->foreignKeys);
        $this->assertTrue(isset($table->foreignKeys[0]));
        $this->assertEquals('order_item', $table->foreignKeys[0][0]);
        $this->assertEquals('order_id', $table->foreignKeys[0]['order_id']);
        $this->assertEquals('item_id', $table->foreignKeys[0]['item_id']);
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
            $this->assertEquals($value[1], $schema->getPdoType($value[0]), 'type for value ' . print_r($value[0], true) . ' does not match.');
        }
        fclose($fp);
    }

    public function getExpectedColumns()
    {
        return [
            'int_col' => [
                'type' => 'integer',
                'dbType' => 'int(11)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 11,
                'precision' => 11,
                'scale' => null,
                'defaultValue' => null,
            ],
            'int_col2' => [
                'type' => 'integer',
                'dbType' => 'int(11)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 11,
                'precision' => 11,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'char_col' => [
                'type' => 'string',
                'dbType' => 'char(100)',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 100,
                'precision' => 100,
                'scale' => null,
                'defaultValue' => null,
            ],
            'char_col2' => [
                'type' => 'string',
                'dbType' => 'varchar(100)',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 100,
                'precision' => 100,
                'scale' => null,
                'defaultValue' => 'something',
            ],
            'char_col3' => [
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
            ],
            'enum_col' => [
                'type' => 'string',
                'dbType' => "enum('a','B')",
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => ['a', 'B'],
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'float_col' => [
                'type' => 'float',
                'dbType' => 'double(4,3)',
                'phpType' => 'double',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 4,
                'precision' => 4,
                'scale' => 3,
                'defaultValue' => null,
            ],
            'float_col2' => [
                'type' => 'float',
                'dbType' => 'double',
                'phpType' => 'double',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => 1.23,
            ],
            'blob_col' => [
                'type' => 'string',
                'dbType' => 'blob',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'numeric_col' => [
                'type' => 'decimal',
                'dbType' => 'decimal(5,2)',
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 5,
                'precision' => 5,
                'scale' => 2,
                'defaultValue' => '33.22',
            ],
            'time' => [
                'type' => 'timestamp',
                'dbType' => 'timestamp',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => '2002-01-01 00:00:00',
            ],
            'bool_col' => [
                'type' => 'smallint',
                'dbType' => 'tinyint(1)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => null,
            ],
            'bool_col2' => [
                'type' => 'smallint',
                'dbType' => 'tinyint(1)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'ts_default' => [
                'type' => 'timestamp',
                'dbType' => 'timestamp',
                'phpType' => 'string',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => new Expression('CURRENT_TIMESTAMP'),
            ],
            'bit_col' => [
                'type' => 'integer',
                'dbType' => 'bit(8)',
                'phpType' => 'integer',
                'allowNull' => false,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 8,
                'precision' => 8,
                'scale' => null,
                'defaultValue' => 130, // b'10000010'
            ],
        ];
    }

    public function testColumnSchema()
    {
        $columns = $this->getExpectedColumns();

        $table = $this->getConnection(false)->schema->getTableSchema('type', true);

        $expectedColNames = array_keys($columns);
        sort($expectedColNames);
        $colNames = $table->columnNames;
        sort($colNames);
        $this->assertEquals($expectedColNames, $colNames);

        foreach($table->columns as $name => $column) {
            $expected = $columns[$name];
            $this->assertSame($expected['dbType'], $column->dbType, "dbType of colum $name does not match. type is $column->type, dbType is $column->dbType.");
            $this->assertSame($expected['phpType'], $column->phpType, "phpType of colum $name does not match. type is $column->type, dbType is $column->dbType.");
            $this->assertSame($expected['type'], $column->type, "type of colum $name does not match.");
            $this->assertSame($expected['allowNull'], $column->allowNull, "allowNull of colum $name does not match.");
            $this->assertSame($expected['autoIncrement'], $column->autoIncrement, "autoIncrement of colum $name does not match.");
            $this->assertSame($expected['enumValues'], $column->enumValues, "enumValues of colum $name does not match.");
            $this->assertSame($expected['size'], $column->size, "size of colum $name does not match.");
            $this->assertSame($expected['precision'], $column->precision, "precision of colum $name does not match.");
            $this->assertSame($expected['scale'], $column->scale, "scale of colum $name does not match.");
            if (is_object($expected['defaultValue'])) {
                $this->assertTrue(is_object($column->defaultValue), "defaultValue of colum $name is expected to be an object but it is not.");
                $this->assertEquals((string) $expected['defaultValue'], (string) $column->defaultValue, "defaultValue of colum $name does not match.");
            } else {
                $this->assertSame($expected['defaultValue'], $column->defaultValue, "defaultValue of colum $name does not match.");
            }
        }
    }
}

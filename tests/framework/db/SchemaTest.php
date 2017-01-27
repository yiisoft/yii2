<?php

namespace yiiunit\framework\db;

use PDO;
use yii\caching\FileCache;
use yii\db\ColumnSchema;
use yii\db\Expression;
use yii\db\Schema;

abstract class SchemaTest extends DatabaseTestCase
{
    public function pdoAttributesProvider()
    {
        return [
            [[PDO::ATTR_EMULATE_PREPARES => true]],
            [[PDO::ATTR_EMULATE_PREPARES => false]],
        ];
    }

    /**
     * @dataProvider pdoAttributesProvider
     */
    public function testGetTableNames($pdoAttributes)
    {
        $connection = $this->getConnection();
        foreach($pdoAttributes as $name => $value) {
            $connection->pdo->setAttribute($name, $value);
        }
        /* @var $schema Schema */
        $schema = $connection->schema;

        $tables = $schema->getTableNames();
        $this->assertTrue(in_array('customer', $tables));
        $this->assertTrue(in_array('category', $tables));
        $this->assertTrue(in_array('item', $tables));
        $this->assertTrue(in_array('order', $tables));
        $this->assertTrue(in_array('order_item', $tables));
        $this->assertTrue(in_array('type', $tables));
        $this->assertTrue(in_array('animal', $tables));
        $this->assertTrue(in_array('animal_view', $tables));
    }

    /**
     * @dataProvider pdoAttributesProvider
     */
    public function testGetTableSchemas($pdoAttributes)
    {
        $connection = $this->getConnection();
        foreach($pdoAttributes as $name => $value) {
            $connection->pdo->setAttribute($name, $value);
        }
        /* @var $schema Schema */
        $schema = $connection->schema;

        $tables = $schema->getTableSchemas();
        $this->assertEquals(count($schema->getTableNames()), count($tables));
        foreach ($tables as $table) {
            $this->assertInstanceOf('yii\db\TableSchema', $table);
        }
    }

    public function testGetTableSchemasWithAttrCase()
    {
        $db = $this->getConnection(false);
        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $this->assertEquals(count($db->schema->getTableNames()), count($db->schema->getTableSchemas()));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $this->assertEquals(count($db->schema->getTableNames()), count($db->schema->getTableSchemas()));
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
        $cachedTable = $schema->getTableSchema('type', false);
        $this->assertEquals($noCacheTable, $cachedTable);
    }

    /**
     * @depends testSchemaCache
     */
    public function testRefreshTableSchema()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();
        $noCacheTable = $schema->getTableSchema('type', true);

        $schema->refreshTableSchema('type');
        $refreshedTable = $schema->getTableSchema('type', false);
        $this->assertFalse($noCacheTable === $refreshedTable);
    }

    public function testCompositeFk()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('composite_fk');

        $this->assertCount(1, $table->foreignKeys);
        $this->assertTrue(isset($table->foreignKeys['FK_composite_fk_order_item']));
        $this->assertEquals('order_item', $table->foreignKeys['FK_composite_fk_order_item'][0]);
        $this->assertEquals('order_id', $table->foreignKeys['FK_composite_fk_order_item']['order_id']);
        $this->assertEquals('item_id', $table->foreignKeys['FK_composite_fk_order_item']['item_id']);
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
            'smallint_col' => [
                'type' => 'smallint',
                'dbType' => 'smallint(1)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 1,
                'precision' => 1,
                'scale' => null,
                'defaultValue' => 1,
            ],
            'char_col' => [
                'type' => 'char',
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
                'dbType' => "enum('a','B','c,D')",
                'phpType' => 'string',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => ['a', 'B','c,D'],
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
            'float_col' => [
                'type' => 'double',
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
                'type' => 'double',
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
                'type' => 'binary',
                'dbType' => 'blob',
                'phpType' => 'resource',
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

    public function testNegativeDefaultValues()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('negative_default_values');
        $this->assertEquals(-123, $table->getColumn('smallint_col')->defaultValue);
        $this->assertEquals(-123, $table->getColumn('int_col')->defaultValue);
        $this->assertEquals(-123, $table->getColumn('bigint_col')->defaultValue);
        $this->assertEquals(-12345.6789, $table->getColumn('float_col')->defaultValue);
        $this->assertEquals(-33.22, $table->getColumn('numeric_col')->defaultValue);
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
            $this->assertSame($expected['dbType'], $column->dbType, "dbType of column $name does not match. type is $column->type, dbType is $column->dbType.");
            $this->assertSame($expected['phpType'], $column->phpType, "phpType of column $name does not match. type is $column->type, dbType is $column->dbType.");
            $this->assertSame($expected['type'], $column->type, "type of column $name does not match.");
            $this->assertSame($expected['allowNull'], $column->allowNull, "allowNull of column $name does not match.");
            $this->assertSame($expected['autoIncrement'], $column->autoIncrement, "autoIncrement of column $name does not match.");
            $this->assertSame($expected['enumValues'], $column->enumValues, "enumValues of column $name does not match.");
            $this->assertSame($expected['size'], $column->size, "size of column $name does not match.");
            $this->assertSame($expected['precision'], $column->precision, "precision of column $name does not match.");
            $this->assertSame($expected['scale'], $column->scale, "scale of column $name does not match.");
            if (is_object($expected['defaultValue'])) {
                $this->assertTrue(is_object($column->defaultValue), "defaultValue of column $name is expected to be an object but it is not.");
                $this->assertEquals((string) $expected['defaultValue'], (string) $column->defaultValue, "defaultValue of column $name does not match.");
            } else {
                $this->assertSame($expected['defaultValue'], $column->defaultValue, "defaultValue of column $name does not match.");
            }
        }
    }

    public function testColumnSchemaDbTypecastWithEmptyCharType()
    {
        $columnSchema = new ColumnSchema(['type' => Schema::TYPE_CHAR]);
        $this->assertSame('', $columnSchema->dbTypecast(''));
    }

    public function testFindUniqueIndexes()
    {
        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch(\Exception $e) {
        }
        $db->createCommand()->createTable('uniqueIndex', [
            'somecol' => 'string',
            'someCol2' => 'string',
        ])->execute();

        /* @var $schema Schema */
        $schema = $db->schema;

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([], $uniqueIndexes);

        $db->createCommand()->createIndex('somecolUnique', 'uniqueIndex', 'somecol', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
        ], $uniqueIndexes);

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex('someCol2Unique', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
        ], $uniqueIndexes);
    }
}

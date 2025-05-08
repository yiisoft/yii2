<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use PDO;
use yii\caching\ArrayCache;
use yii\caching\FileCache;
use yii\db\CheckConstraint;
use yii\db\ColumnSchema;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\Schema;
use yii\db\TableSchema;

abstract class SchemaTest extends DatabaseTestCase
{
    /**
     * @var string[]
     */
    protected $expectedSchemas;

    public function pdoAttributesProvider()
    {
        return [
            [[PDO::ATTR_EMULATE_PREPARES => true]],
            [[PDO::ATTR_EMULATE_PREPARES => false]],
        ];
    }

    public function testGetSchemaNames()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $schemas = $schema->getSchemaNames();
        $this->assertNotEmpty($schemas);
        foreach ($this->expectedSchemas as $schema) {
            $this->assertContains($schema, $schemas);
        }
    }

    /**
     * @dataProvider pdoAttributesProvider
     * @param array $pdoAttributes
     */
    public function testGetTableNames($pdoAttributes)
    {
        $connection = $this->getConnection();
        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $connection->driverName === 'sqlsrv') {
                continue;
            }
            $connection->pdo->setAttribute($name, $value);
        }

        /* @var $schema Schema */
        $schema = $connection->schema;

        $tables = $schema->getTableNames();
        if ($this->driverName === 'sqlsrv') {
            $tables = array_map(static function ($item) {
                return trim($item, '[]');
            }, $tables);
        }
        $this->assertContains('customer', $tables);
        $this->assertContains('category', $tables);
        $this->assertContains('item', $tables);
        $this->assertContains('order', $tables);
        $this->assertContains('order_item', $tables);
        $this->assertContains('type', $tables);
        $this->assertContains('animal', $tables);
        $this->assertContains('animal_view', $tables);
    }

    /**
     * @dataProvider pdoAttributesProvider
     * @param array $pdoAttributes
     */
    public function testGetTableSchemas($pdoAttributes)
    {
        $connection = $this->getConnection();
        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $connection->driverName === 'sqlsrv') {
                continue;
            }
            $connection->pdo->setAttribute($name, $value);
        }
        /* @var $schema Schema */
        $schema = $connection->schema;

        $tables = $schema->getTableSchemas();
        $this->assertEquals(\count($schema->getTableNames()), \count($tables));
        foreach ($tables as $table) {
            $this->assertInstanceOf('yii\db\TableSchema', $table);
        }
    }

    public function testGetTableSchemasWithAttrCase()
    {
        $db = $this->getConnection(false);
        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $this->assertEquals(\count($db->schema->getTableNames()), \count($db->schema->getTableSchemas()));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $this->assertEquals(\count($db->schema->getTableNames()), \count($db->schema->getTableSchemas()));
    }

    public function testGetNonExistingTableSchema()
    {
        $this->assertNull($this->getConnection()->schema->getTableSchema('nonexisting_table'));
    }

    public function testSchemaCache()
    {
        /* @var $db Connection */
        $db = $this->getConnection();

        /* @var $schema Schema */
        $schema = $db->schema;

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();
        $noCacheTable = $schema->getTableSchema('type', true);
        $cachedTable = $schema->getTableSchema('type', false);
        $this->assertEquals($noCacheTable, $cachedTable);

        $db->createCommand()->renameTable('type', 'type_test');
        $noCacheTable = $schema->getTableSchema('type', true);
        $this->assertNotSame($noCacheTable, $cachedTable);

        $db->createCommand()->renameTable('type_test', 'type');
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
        $this->assertNotSame($noCacheTable, $refreshedTable);
    }

    public function tableSchemaCachePrefixesProvider()
    {
        $configs = [
            [
                'prefix' => '',
                'name' => 'type',
            ],
            [
                'prefix' => '',
                'name' => '{{%type}}',
            ],
            [
                'prefix' => 'ty',
                'name' => '{{%pe}}',
            ],
        ];
        $data = [];
        foreach ($configs as $config) {
            foreach ($configs as $testConfig) {
                if ($config === $testConfig) {
                    continue;
                }

                $description = sprintf(
                    "%s (with '%s' prefix) against %s (with '%s' prefix)",
                    $config['name'],
                    $config['prefix'],
                    $testConfig['name'],
                    $testConfig['prefix']
                );
                $data[$description] = [
                    $config['prefix'],
                    $config['name'],
                    $testConfig['prefix'],
                    $testConfig['name'],
                ];
            }
        }
        return $data;
    }

    /**
     * @dataProvider tableSchemaCachePrefixesProvider
     * @depends      testSchemaCache
     */
    public function testTableSchemaCacheWithTablePrefixes($tablePrefix, $tableName, $testTablePrefix, $testTableName)
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;
        $schema->db->enableSchemaCache = true;

        $schema->db->tablePrefix = $tablePrefix;
        $schema->db->schemaCache = new ArrayCache();
        $noCacheTable = $schema->getTableSchema($tableName, true);
        $this->assertInstanceOf(TableSchema::className(), $noCacheTable);

        // Compare
        $schema->db->tablePrefix = $testTablePrefix;
        $testNoCacheTable = $schema->getTableSchema($testTableName);
        $this->assertSame($noCacheTable, $testNoCacheTable);

        $schema->db->tablePrefix = $tablePrefix;
        $schema->refreshTableSchema($tableName);
        $refreshedTable = $schema->getTableSchema($tableName, false);
        $this->assertInstanceOf(TableSchema::className(), $refreshedTable);
        $this->assertNotSame($noCacheTable, $refreshedTable);

        // Compare
        $schema->db->tablePrefix = $testTablePrefix;
        $schema->refreshTableSchema($testTablePrefix);
        $testRefreshedTable = $schema->getTableSchema($testTableName, false);
        $this->assertInstanceOf(TableSchema::className(), $testRefreshedTable);
        $this->assertEquals($refreshedTable, $testRefreshedTable);
        $this->assertNotSame($testNoCacheTable, $testRefreshedTable);
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
            'tinyint_col' => [
                'type' => 'tinyint',
                'dbType' => 'tinyint(3)',
                'phpType' => 'integer',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => 3,
                'precision' => 3,
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
                'enumValues' => ['a', 'B', 'c,D'],
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
                'type' => 'tinyint',
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
                'type' => 'tinyint',
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
            'json_col' => [
                'type' => 'json',
                'dbType' => 'json',
                'phpType' => 'array',
                'allowNull' => true,
                'autoIncrement' => false,
                'enumValues' => null,
                'size' => null,
                'precision' => null,
                'scale' => null,
                'defaultValue' => null,
            ],
        ];
    }

    public function testNegativeDefaultValues()
    {
        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('negative_default_values');
        $this->assertEquals(-123, $table->getColumn('tinyint_col')->defaultValue);
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

        foreach ($table->columns as $name => $column) {
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
            if (\is_object($expected['defaultValue'])) {
                $this->assertIsObject($column->defaultValue, "defaultValue of column $name is expected to be an object but it is not.");
                $this->assertEquals((string)$expected['defaultValue'], (string)$column->defaultValue, "defaultValue of column $name does not match.");
            } else {
                $this->assertEquals($expected['defaultValue'], $column->defaultValue, "defaultValue of column $name does not match.");
            }
            if (isset($expected['dimension'])) { // PgSQL only
                $this->assertSame($expected['dimension'], $column->dimension, "dimension of column $name does not match");
            }
        }
    }

    public function testColumnSchemaDbTypecastWithEmptyCharType()
    {
        $columnSchema = new ColumnSchema(['type' => Schema::TYPE_CHAR]);
        $this->assertSame('', $columnSchema->dbTypecast(''));
    }

    /**
     * @dataProvider columnSchemaDbTypecastBooleanPhpTypeProvider
     * @param mixed $value
     * @param bool $expected
     */
    public function testColumnSchemaDbTypecastBooleanPhpType($value, $expected)
    {
        $columnSchema = new ColumnSchema(['phpType' => Schema::TYPE_BOOLEAN]);
        $this->assertSame($expected, $columnSchema->dbTypecast($value));
    }

    public function columnSchemaDbTypecastBooleanPhpTypeProvider()
    {
        return [
            [1, true],
            [0, false],
            ['1', true],
            ['0', false],

            // https://github.com/yiisoft/yii2/issues/9006
            ["\1", true],
            ["\0", false],

            // https://github.com/yiisoft/yii2/pull/20122
            ['TRUE', true],
            ['FALSE', false],
            ['true', true],
            ['false', false],
            ['True', true],
            ['False', false],
        ];
    }

    public function testFindUniqueIndexes()
    {
        if ($this->driverName === 'sqlsrv') {
            $this->markTestSkipped('`\yii\db\mssql\Schema::findUniqueIndexes()` returns only unique constraints not unique indexes.');
        }

        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch (\Exception $e) {
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

        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
            'another unique index' => ['someCol2'],
        ], $uniqueIndexes);
    }

    public function testContraintTablesExistance()
    {
        $tableNames = [
            'T_constraints_1',
            'T_constraints_2',
            'T_constraints_3',
            'T_constraints_4',
        ];
        $schema = $this->getConnection()->getSchema();
        foreach ($tableNames as $tableName) {
            $tableSchema = $schema->getTableSchema($tableName);
            $this->assertInstanceOf('yii\db\TableSchema', $tableSchema, $tableName);
        }
    }

    public function constraintsProvider()
    {
        return [
            '1: primary key' => ['T_constraints_1', 'primaryKey', new Constraint([
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id'],
            ])],
            '1: check' => ['T_constraints_1', 'checks', [
                new CheckConstraint([
                    'name' => AnyValue::getInstance(),
                    'columnNames' => ['C_check'],
                    'expression' => "C_check <> ''",
                ]),
            ]],
            '1: unique' => ['T_constraints_1', 'uniques', [
                new Constraint([
                    'name' => 'CN_unique',
                    'columnNames' => ['C_unique'],
                ]),
            ]],
            '1: index' => ['T_constraints_1', 'indexes', [
                new IndexConstraint([
                    'name' => AnyValue::getInstance(),
                    'columnNames' => ['C_id'],
                    'isUnique' => true,
                    'isPrimary' => true,
                ]),
                new IndexConstraint([
                    'name' => 'CN_unique',
                    'columnNames' => ['C_unique'],
                    'isPrimary' => false,
                    'isUnique' => true,
                ]),
            ]],
            '1: default' => ['T_constraints_1', 'defaultValues', false],

            '2: primary key' => ['T_constraints_2', 'primaryKey', new Constraint([
                'name' => 'CN_pk',
                'columnNames' => ['C_id_1', 'C_id_2'],
            ])],
            '2: unique' => ['T_constraints_2', 'uniques', [
                new Constraint([
                    'name' => 'CN_constraints_2_multi',
                    'columnNames' => ['C_index_2_1', 'C_index_2_2'],
                ]),
            ]],
            '2: index' => ['T_constraints_2', 'indexes', [
                new IndexConstraint([
                    'name' => AnyValue::getInstance(),
                    'columnNames' => ['C_id_1', 'C_id_2'],
                    'isUnique' => true,
                    'isPrimary' => true,
                ]),
                new IndexConstraint([
                    'name' => 'CN_constraints_2_single',
                    'columnNames' => ['C_index_1'],
                    'isPrimary' => false,
                    'isUnique' => false,
                ]),
                new IndexConstraint([
                    'name' => 'CN_constraints_2_multi',
                    'columnNames' => ['C_index_2_1', 'C_index_2_2'],
                    'isPrimary' => false,
                    'isUnique' => true,
                ]),
            ]],
            '2: check' => ['T_constraints_2', 'checks', []],
            '2: default' => ['T_constraints_2', 'defaultValues', false],

            '3: primary key' => ['T_constraints_3', 'primaryKey', null],
            '3: foreign key' => ['T_constraints_3', 'foreignKeys', [
                new ForeignKeyConstraint([
                    'name' => 'CN_constraints_3',
                    'columnNames' => ['C_fk_id_1', 'C_fk_id_2'],
                    'foreignTableName' => 'T_constraints_2',
                    'foreignColumnNames' => ['C_id_1', 'C_id_2'],
                    'onDelete' => 'CASCADE',
                    'onUpdate' => 'CASCADE',
                ]),
            ]],
            '3: unique' => ['T_constraints_3', 'uniques', []],
            '3: index' => ['T_constraints_3', 'indexes', [
                new IndexConstraint([
                    'name' => 'CN_constraints_3',
                    'columnNames' => ['C_fk_id_1', 'C_fk_id_2'],
                    'isUnique' => false,
                    'isPrimary' => false,
                ]),
            ]],
            '3: check' => ['T_constraints_3', 'checks', []],
            '3: default' => ['T_constraints_3', 'defaultValues', false],

            '4: primary key' => ['T_constraints_4', 'primaryKey', new Constraint([
                'name' => AnyValue::getInstance(),
                'columnNames' => ['C_id'],
            ])],
            '4: unique' => ['T_constraints_4', 'uniques', [
                new Constraint([
                    'name' => 'CN_constraints_4',
                    'columnNames' => ['C_col_1', 'C_col_2'],
                ]),
            ]],
            '4: check' => ['T_constraints_4', 'checks', []],
            '4: default' => ['T_constraints_4', 'defaultValues', false],
        ];
    }

    public function lowercaseConstraintsProvider()
    {
        return $this->constraintsProvider();
    }

    public function uppercaseConstraintsProvider()
    {
        return $this->constraintsProvider();
    }

    /**
     * @dataProvider constraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraints($tableName, $type, $expected)
    {
        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        $constraints = $this->getConnection(false)->getSchema()->{'getTable' . ucfirst($type)}($tableName);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider uppercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected)
    {
        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider lowercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected)
    {
        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    protected function assertMetadataEquals($expected, $actual)
    {
        switch (\strtolower(\gettype($expected))) {
            case 'object':
                $this->assertIsObject($actual);
                break;
            case 'array':
                $this->assertIsArray($actual);
                break;
            case 'null':
                $this->assertNull($actual);
                break;
        }

        if (\is_array($expected)) {
            $this->normalizeArrayKeys($expected, false);
            $this->normalizeArrayKeys($actual, false);
        }

        $this->normalizeConstraints($expected, $actual);

        if (\is_array($expected)) {
            $this->normalizeArrayKeys($expected, true);
            $this->normalizeArrayKeys($actual, true);
        }
        $this->assertEquals($expected, $actual);
    }

    protected function normalizeArrayKeys(array &$array, $caseSensitive)
    {
        $newArray = [];
        foreach ($array as $value) {
            if ($value instanceof Constraint) {
                $key = (array)$value;
                unset($key['name'], $key['foreignSchemaName']);
                foreach ($key as $keyName => $keyValue) {
                    if ($keyValue instanceof AnyCaseValue) {
                        $key[$keyName] = $keyValue->value;
                    } elseif ($keyValue instanceof AnyValue) {
                        $key[$keyName] = '[AnyValue]';
                    }
                }
                ksort($key, SORT_STRING);
                $newArray[$caseSensitive ? json_encode($key) : strtolower(json_encode($key))] = $value;
            } else {
                $newArray[] = $value;
            }
        }
        ksort($newArray, SORT_STRING);
        $array = $newArray;
    }

    protected function normalizeConstraints(&$expected, &$actual)
    {
        if (\is_array($expected)) {
            foreach ($expected as $key => $value) {
                if (!$value instanceof Constraint || !isset($actual[$key]) || !$actual[$key] instanceof Constraint) {
                    continue;
                }

                $this->normalizeConstraintPair($value, $actual[$key]);
            }
        } elseif ($expected instanceof Constraint && $actual instanceof Constraint) {
            $this->normalizeConstraintPair($expected, $actual);
        }
    }

    protected function normalizeConstraintPair(Constraint $expectedConstraint, Constraint $actualConstraint)
    {
        if ($expectedConstraint::className() !== $actualConstraint::className()) {
            return;
        }

        foreach (array_keys((array)$expectedConstraint) as $name) {
            if ($expectedConstraint->$name instanceof AnyValue) {
                $actualConstraint->$name = $expectedConstraint->$name;
            } elseif ($expectedConstraint->$name instanceof AnyCaseValue) {
                $actualConstraint->$name = new AnyCaseValue($actualConstraint->$name);
            }
        }
    }
}

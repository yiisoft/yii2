<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use yii\base\NotSupportedException;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\base\db\providers\SchemaProvider;
use yiiunit\support\Assert;
use Exception;
use PDO;
use yii\caching\ArrayCache;
use yii\caching\FileCache;
use yii\db\ColumnSchema;
use yii\db\Constraint;
use yii\db\Schema;
use yii\db\TableSchema;

use function array_keys;
use function array_map;
use function count;
use function fopen;
use function is_object;
use function print_r;
use function sort;
use function trim;

abstract class BaseSchema extends DatabaseTestCase
{
    /**
     * @var list<string> List of expected schemas in the database.
     */
    protected array $expectedSchemas = [];

    public function testGetSchemaNames(): void
    {
        $schema = $this->getConnection()->schema;

        $schemas = $schema->getSchemaNames();

        self::assertNotEmpty(
            $schemas,
            'The list of schema names is empty.',
        );

        foreach ($this->expectedSchemas as $schema) {
            self::assertContains(
                $schema,
                $schemas,
                "Schema '{$schema}' is missing in the list of schemas.",
            );
        }
    }

    /**
     * @param array<int, bool> $pdoAttributes PDO attributes to be applied to the connection.
     */
    #[DataProviderExternal(SchemaProvider::class, 'pdoAttributes')]
    public function testGetTableNames(array $pdoAttributes): void
    {
        $db = $this->getConnection();

        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $db->driverName === 'sqlsrv') {
                continue;
            }

            $db->pdo->setAttribute($name, $value);
        }

        $schema = $db->getSchema();
        $tables = $schema->getTableNames();

        if ($this->driverName === 'sqlsrv') {
            $tables = array_map(static fn($item): string => trim((string) $item, '[]'), $tables);
        }

        self::assertContains(
            'customer',
            $tables,
            "'customer' table is missing in the list of tables.",
        );
        self::assertContains(
            'category',
            $tables,
            "'category' table is missing in the list of tables.",
        );
        self::assertContains(
            'item',
            $tables,
            "'item' table is missing in the list of tables.",
        );
        self::assertContains(
            'order',
            $tables,
            "'order' table is missing in the list of tables.",
        );
        self::assertContains(
            'order_item',
            $tables,
            "'order_item' table is missing in the list of tables.",
        );
        self::assertContains(
            'type',
            $tables,
            "'type' table is missing in the list of tables.",
        );
        self::assertContains(
            'animal',
            $tables,
            "'animal' table is missing in the list of tables.",
        );
        self::assertContains(
            'animal_view',
            $tables,
            "'animal_view' table is missing in the list of tables.",
        );
    }

    /**
     * @param array<int, bool> $pdoAttributes PDO attributes to be applied to the connection.
     */
    #[DataProviderExternal(SchemaProvider::class, 'pdoAttributes')]
    public function testGetTableSchemas(array $pdoAttributes): void
    {
        $db = $this->getConnection();

        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $db->driverName === 'sqlsrv') {
                continue;
            }

            $db->pdo->setAttribute($name, $value);
        }

        $schema = $db->getSchema();
        $tables = $schema->getTableSchemas();

        self::assertSame(
            count($schema->getTableNames()),
            count($tables),
            'Number of table schemas does not match the number of table names.',
        );

        foreach ($tables as $table) {
            self::assertInstanceOf(
                TableSchema::class,
                $table,
                'Table schema is not an instance of ' . TableSchema::class . '.',
            );
        }
    }

    public function testGetTableSchemasWithAttrCase(): void
    {
        $db = $this->getConnection(false);

        $db->slavePdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        self::assertSame(
            count($db->schema->getTableNames()),
            count($db->schema->getTableSchemas()),
            "Number of table does not match the number of table names with 'PDO::ATTR_CASE' set to 'PDO::CASE_LOWER'.",
        );

        $db->slavePdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

        self::assertSame(
            count($db->schema->getTableNames()),
            count($db->schema->getTableSchemas()),
            "Number of table does not match the number of table names with 'PDO::ATTR_CASE' set to 'PDO::CASE_UPPER'.",
        );
    }

    public function testGetNonExistingTableSchema(): void
    {
        self::assertNull(
            $this->getConnection()->schema->getTableSchema('nonexisting_table'),
            "Getting schema for non-existing table should return 'null'.",
        );
    }

    public function testSchemaCache(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();

        $noCacheTable = $schema->getTableSchema('type', true);
        $cachedTable = $schema->getTableSchema('type', false);

        self::assertSame(
            $noCacheTable,
            $cachedTable,
            'Getting table with cache should return the same instance.',
        );

        $db->createCommand()->renameTable('type', 'type_test');
        $noCacheTable = $schema->getTableSchema('type', true);

        self::assertNotSame(
            $noCacheTable,
            $cachedTable,
            'Getting table with cache should return a different instance.',
        );

        $db->createCommand()->renameTable('type_test', 'type');
    }

    /**
     * @depends testSchemaCache
     */
    public function testRefreshTableSchema(): void
    {
        $schema = $this->getConnection()->schema;

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();

        $noCacheTable = $schema->getTableSchema('type', true);

        $schema->refreshTableSchema('type');

        $refreshedTable = $schema->getTableSchema('type', false);

        self::assertNotSame(
            $noCacheTable,
            $refreshedTable,
            'Refreshing table should return a different instance.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'tableSchemaCachePrefixes')]
    #[Depends('testSchemaCache')]
    public function testTableSchemaCacheWithTablePrefixes(
        string $tablePrefix,
        string $tableName,
        string $testTablePrefix,
        string $testTableName,
    ): void {
        $schema = $this->getConnection()->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->tablePrefix = $tablePrefix;
        $schema->db->schemaCache = new ArrayCache();

        $noCacheTable = $schema->getTableSchema($tableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $noCacheTable,
            'Getting table with cache should return an instance of ' . TableSchema::class . '.',
        );

        // Compare
        $schema->db->tablePrefix = $testTablePrefix;

        $testNoCacheTable = $schema->getTableSchema($testTableName);

        self::assertSame(
            $noCacheTable,
            $testNoCacheTable,
            'Getting table with cache should return the same instance.',
        );

        $schema->db->tablePrefix = $tablePrefix;

        $schema->refreshTableSchema($tableName);
        $refreshedTable = $schema->getTableSchema($tableName, false);

        self::assertInstanceOf(
            TableSchema::class,
            $refreshedTable,
            'Refreshing table should return an instance of ' . TableSchema::class . '.',
        );
        self::assertNotSame(
            $noCacheTable,
            $refreshedTable,
            'Refreshing table should return a different instance.',
        );

        // Compare
        $schema->db->tablePrefix = $testTablePrefix;

        $schema->refreshTableSchema($testTablePrefix);

        $testRefreshedTable = $schema->getTableSchema($testTableName, false);

        self::assertInstanceOf(
            TableSchema::class,
            $testRefreshedTable,
            'Refreshing table should return an instance of ' . TableSchema::class . '.',
        );
        self::assertSame(
            $refreshedTable,
            $testRefreshedTable,
            'Refreshing table should return the same instance.',
        );
        self::assertNotSame(
            $testNoCacheTable,
            $testRefreshedTable,
            'Refreshing table should return a different instance.',
        );
    }

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('composite_fk');

        self::assertCount(
            1,
            $table->foreignKeys,
            'Number of foreign keys does not match the expected count.',
        );
        self::assertTrue(
            isset($table->foreignKeys['FK_composite_fk_order_item']),
            "Foreign key 'FK_composite_fk_order_item' is missing in the table schema.",
        );
        self::assertSame(
            'order_item',
            $table->foreignKeys['FK_composite_fk_order_item'][0],
            "Referenced table name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'order_id',
            $table->foreignKeys['FK_composite_fk_order_item']['order_id'],
            "Referenced column name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'item_id',
            $table->foreignKeys['FK_composite_fk_order_item']['item_id'],
            "Referenced column name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
    }

    public function testGetPDOType(): void
    {
        $values = [
            [null, PDO::PARAM_NULL],
            ['', PDO::PARAM_STR],
            ['hello', PDO::PARAM_STR],
            [0, PDO::PARAM_INT],
            [1, PDO::PARAM_INT],
            [1337, PDO::PARAM_INT],
            [true, PDO::PARAM_BOOL],
            [false, PDO::PARAM_BOOL],
            [$fp = fopen(__FILE__, 'rb'), PDO::PARAM_LOB],
        ];

        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            self::assertSame(
                $value[1],
                $schema->getPdoType($value[0]),
                'type for value ' . print_r($value[0], true) . ' does not match.',
            );
        }

        fclose($fp);
    }


    public function testNegativeDefaultValues(): void
    {
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('negative_default_values');

        self::assertSame(
            -123,
            $table->getColumn('tinyint_col')->defaultValue,
            "'defaultValue' of column 'tinyint_col' does not match.",
        );
        self::assertSame(
            -123,
            $table->getColumn('smallint_col')->defaultValue,
            "'defaultValue' of column 'smallint_col' does not match.",
        );
        self::assertSame(
            -123,
            $table->getColumn('int_col')->defaultValue,
            "'defaultValue' of column 'int_col' does not match.",
        );
        self::assertSame(
            -123,
            $table->getColumn('bigint_col')->defaultValue,
            "'defaultValue' of column 'bigint_col' does not match.",
        );
        self::assertSame(
            -12345.6789,
            $table->getColumn('float_col')->defaultValue,
            "'defaultValue' of column 'float_col' does not match.",
        );
        self::assertEquals(
            -33.22,
            $table->getColumn('numeric_col')->defaultValue,
            "'defaultValue' of column 'numeric_col' does not match.",
        );
    }

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        $table = $this->getConnection(false)->schema->getTableSchema('type', true);

        $expectedColNames = array_keys($columns);

        sort($expectedColNames);

        $colNames = $table->columnNames;

        sort($colNames);

        self::assertSame($expectedColNames, $colNames);

        foreach ($table->columns as $name => $column) {
            $expected = $columns[$name];

            self::assertSame(
                $expected['dbType'],
                $column->dbType,
                "'dbType' of column {$name} does not match. type is {$column->type}, dbType is {$column->dbType}.",
            );
            self::assertSame(
                $expected['phpType'],
                $column->phpType,
                "'phpType' of column {$name} does not match. type is {$column->type}, dbType is {$column->dbType}.",
            );
            self::assertSame(
                $expected['type'],
                $column->type,
                "'type' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['allowNull'],
                $column->allowNull,
                "'allowNull' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['autoIncrement'],
                $column->autoIncrement,
                "'autoIncrement' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['enumValues'],
                $column->enumValues,
                "'enumValues' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['size'],
                $column->size,
                "'size' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['precision'],
                $column->precision,
                "'precision' of column {$name} does not match.",
            );
            self::assertSame(
                $expected['scale'],
                $column->scale,
                "'scale' of column {$name} does not match.",
            );

            if (is_object($expected['defaultValue'])) {
                self::assertIsObject(
                    $column->defaultValue,
                    "'defaultValue' of column {$name} is expected to be an object but it is not.",
                );
                self::assertSame(
                    (string) $expected['defaultValue'],
                    (string) $column->defaultValue,
                    "'defaultValue' of column {$name} does not match.",
                );
            } else {
                self::assertSame(
                    $expected['defaultValue'],
                    $column->defaultValue,
                    "'defaultValue' of column {$name} does not match.",
                );
            }

            if (isset($expected['dimension'])) {
                self::assertInstanceOf(
                    \yii\db\pgsql\ColumnSchema::class,
                    $column,
                    "Column {$name} is expected to be an instance of " . \yii\db\pgsql\ColumnSchema::class . '.',
                );
                self::assertSame(
                    $expected['dimension'],
                    $column->dimension,
                    "'dimension' of column {$name} does not match.",
                );
            }
        }
    }

    public function testColumnSchemaDbTypecastWithEmptyCharType(): void
    {
        $columnSchema = new ColumnSchema(['type' => Schema::TYPE_CHAR]);

        self::assertSame(
            '',
            $columnSchema->dbTypecast(''),
            "'dbTypecast' did not return the expected empty string for char type.",
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'columnSchemaDbTypecastBooleanPhpType')]
    public function testColumnSchemaDbTypecastBooleanPhpType(mixed $value, bool $expected): void
    {
        $columnSchema = new ColumnSchema(['phpType' => Schema::TYPE_BOOLEAN]);

        self::assertSame(
            $expected,
            $columnSchema->dbTypecast($value),
            "'dbTypecast' did not return the expected boolean.",
        );
    }

    public function testFindUniqueIndexes(): void
    {
        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch (Exception) {
        }
        $db->createCommand()->createTable(
            'uniqueIndex',
            [
                'somecol' => 'string',
                'someCol2' => 'string',
            ],
        )->execute();

        $schema = $db->getSchema();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertSame(
            [],
            $uniqueIndexes,
            'There should be no unique indexes in the table.',
        );

        $db->createCommand()->createIndex('somecolUnique', 'uniqueIndex', 'somecol', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            ['somecolUnique' => ['somecol']],
            $uniqueIndexes,
            'Unique indexes do not match after creating unique index on "somecol".',
        );

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex('someCol2Unique', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol2'.",
        );

        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
                'another unique index' => ['someCol2'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol2'.",
        );
    }

    public function testContraintTablesExistance(): void
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

            $this->assertInstanceOf(
                TableSchema::class,
                $tableSchema,
                "Table schema for '{$tableName}' should be an instance of " . TableSchema::class . '.',
            );
        }
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'constraints')]
    public function testTableSchemaConstraints(
        string $tableName,
        string $type,
        Constraint|bool|array|null $expected,
    ): void {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
        }

        $constraints = $this->getConnection(false)->getSchema()->{'getTable' . ucfirst($type)}($tableName);

        Assert::metadataEquals(
            $expected,
            $constraints,
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
        }

        $db = $this->getConnection(false);

        $db->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
        $constraints = $db->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);

        Assert::metadataEquals(
            $expected,
            $constraints,
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
        }

        $db = $this->getConnection(false);

        $db->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $constraints = $db->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);

        Assert::metadataEquals(
            $expected,
            $constraints,
        );
    }
}

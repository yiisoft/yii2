<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use ReflectionMethod;
use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\Connection;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\Exception;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\TableSchema;
use yiiunit\framework\db\AnyValue;

/**
 * @group db
 * @group sqlite
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    protected $driverName = 'sqlite';

    /**
     * The name the additional in-memory database is attached under by [[attachSecondSchema()]].
     */
    private const ATTACHED_SCHEMA = 'second_schema';

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        unset($columns['bit_col']);
        unset($columns['json_col']);
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

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('composite_fk');

        $this->assertCount(1, $table->foreignKeys);
        $this->assertTrue(isset($table->foreignKeys[0]));
        $this->assertEquals('order_item', $table->foreignKeys[0][0]);
        $this->assertEquals('order_id', $table->foreignKeys[0]['order_id']);
        $this->assertEquals('item_id', $table->foreignKeys[0]['item_id']);
    }

    public static function constraintsProvider(): array
    {
        $result = parent::constraintsProvider();
        $result['1: primary key'][2]->name = null;
        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        $result['1: unique'][2][0]->name = AnyValue::getInstance();
        $result['1: index'][2][1]->name = AnyValue::getInstance();

        $result['2: primary key'][2]->name = null;
        $result['2: unique'][2][0]->name = AnyValue::getInstance();
        $result['2: index'][2][2]->name = AnyValue::getInstance();

        $result['3: foreign key'][2][0]->name = null;
        $result['3: foreign key'][2][0]->foreignSchemaName = 'main';
        $result['3: index'][2] = [];

        $result['4: primary key'][2]->name = null;
        $result['4: unique'][2][0]->name = AnyValue::getInstance();

        $result['5: primary key'] = ['T_upsert', 'primaryKey', new Constraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['id'],
        ])];

        return $result;
    }

    /**
     * @dataProvider quoteTableNameDataProvider
     * @param $name
     * @param $expectedName
     * @throws NotSupportedException
     */
    public function testQuoteTableName($name, $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();
        $quotedName = $schema->quoteTableName($name);
        $this->assertEquals($expectedName, $quotedName);
    }

    public function quoteTableNameDataProvider()
    {
        return [
            ['test', '`test`'],
            ['test.test', '`test`.`test`'],
            ['test.test.test', '`test`.`test`.`test`'],
            ['`test`', '`test`'],
            ['`test`.`test`', '`test`.`test`'],
            ['test.`test`.test', '`test`.`test`.`test`'],
        ];
    }

    public function testGetSchemaNames(): void
    {
        $db = $this->getConnection();

        $this->assertSame(['main'], $db->getSchema()->getSchemaNames());

        $this->attachSecondSchema($db);

        $this->assertSame(['main', self::ATTACHED_SCHEMA], $db->getSchema()->getSchemaNames(true));
    }

    /**
     * The `temp` database is a system schema, so it must not show up among the schema names even once it exists.
     */
    public function testGetSchemaNamesSkipsTempSchema(): void
    {
        $db = $this->getConnection();
        $db->createCommand('CREATE TEMPORARY TABLE temp_customer (id INTEGER NOT NULL PRIMARY KEY)')->execute();

        $this->assertSame(['main'], $db->getSchema()->getSchemaNames(true));
    }

    /**
     * Table names used to be read from the `main` schema no matter which schema was asked for.
     *
     * @see https://github.com/yiisoft/yii2/issues/20262
     */
    public function testGetTableNamesFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);
        $schema = $db->getSchema();

        $this->assertSame(['attached_item', 'customer'], $schema->getTableNames(self::ATTACHED_SCHEMA));

        $defaultSchemaTableNames = $schema->getTableNames();
        $this->assertContains('customer', $defaultSchemaTableNames);
        $this->assertContains('profile', $defaultSchemaTableNames);
        $this->assertNotContains('attached_item', $defaultSchemaTableNames);
    }

    /**
     * Asking for the default schema explicitly must give the same result as not naming a schema at all.
     */
    public function testGetTableNamesFromDefaultSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);
        $schema = $db->getSchema();

        $this->assertSame($schema->getTableNames(), $schema->getTableNames('main'));
    }

    public function testGetTableSchemaFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $table = $db->getSchema()->getTableSchema(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertNotNull($table);
        $this->assertSame(self::ATTACHED_SCHEMA, $table->schemaName);
        $this->assertSame('attached_item', $table->name);
        $this->assertSame(self::ATTACHED_SCHEMA . '.attached_item', $table->fullName);
        $this->assertSame(['id', 'customer_id', 'slug'], $table->getColumnNames());
        $this->assertSame(['id'], $table->primaryKey);
        $this->assertSame(
            [[self::ATTACHED_SCHEMA . '.customer', 'customer_id' => 'id']],
            $table->foreignKeys
        );
    }

    /**
     * Both schemas hold a `customer` table with different columns, so reading the wrong one is easy to spot.
     */
    public function testGetTableSchemaTellsApartSameNamedTables(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);
        $schema = $db->getSchema();

        $attachedCustomer = $schema->getTableSchema(self::ATTACHED_SCHEMA . '.customer');
        $this->assertSame(['id', 'code'], $attachedCustomer->getColumnNames());

        $mainCustomer = $schema->getTableSchema('customer');
        $this->assertSame(
            ['id', 'email', 'name', 'address', 'status', 'profile_id'],
            $mainCustomer->getColumnNames()
        );
    }

    /**
     * The default schema is never repeated in [[TableSchema::$fullName]], the same way the other drivers behave.
     */
    public function testGetTableSchemaWithDefaultSchemaPrefix(): void
    {
        $schema = $this->getConnection()->getSchema();

        $table = $schema->getTableSchema('main.customer');

        $this->assertNotNull($table);
        $this->assertSame('main', $table->schemaName);
        $this->assertSame('customer', $table->name);
        $this->assertSame('customer', $table->fullName);
        $this->assertSame($schema->getTableSchema('customer')->getColumnNames(), $table->getColumnNames());
    }

    public function testGetTableSchemasFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $tableSchemas = $db->getSchema()->getTableSchemas(self::ATTACHED_SCHEMA);

        $this->assertCount(2, $tableSchemas);
        foreach ($tableSchemas as $tableSchema) {
            $this->assertSame(self::ATTACHED_SCHEMA, $tableSchema->schemaName);
        }
        $this->assertSame(
            [self::ATTACHED_SCHEMA . '.attached_item', self::ATTACHED_SCHEMA . '.customer'],
            array_map(static function (TableSchema $tableSchema) {
                return $tableSchema->fullName;
            }, $tableSchemas)
        );

        // a full name is a name the schema can be asked for again
        foreach ($tableSchemas as $tableSchema) {
            $roundTripped = $db->getSchema()->getTableSchema($tableSchema->fullName);
            $this->assertNotNull($roundTripped);
            $this->assertSame($tableSchema->schemaName, $roundTripped->schemaName);
            $this->assertSame($tableSchema->name, $roundTripped->name);
        }
    }

    /**
     * A quote character inside a schema name has to be doubled, or it breaks out of the quoting and the
     * schema-qualified metadata queries turn into invalid SQL.
     */
    public function testSchemaNameHoldingAQuoteCharacter(): void
    {
        $db = $this->getConnection();
        $db->createCommand("ATTACH DATABASE ':memory:' AS `my``schema`")->execute();
        $db->createCommand('CREATE TABLE `my``schema`.customer (id INTEGER NOT NULL PRIMARY KEY, code varchar(32))')
            ->execute();
        $schema = $db->getSchema();

        $this->assertSame(['main', 'my`schema'], $schema->getSchemaNames(true));
        $this->assertSame(['customer'], $schema->getTableNames('my`schema'));

        $table = $schema->getTableSchema('`my``schema`.customer');

        $this->assertNotNull($table);
        $this->assertSame('my`schema', $table->schemaName);
        $this->assertSame('customer', $table->name);
        $this->assertSame('`my``schema`.customer', $table->fullName);
        $this->assertSame(['id', 'code'], $table->getColumnNames());
    }

    /**
     * A dot inside a schema or a table name would make the full name ambiguous, so those names are quoted back
     * and keep resolving to the table they were read from.
     */
    public function testFullNameOfNamesHoldingADotResolvesBack(): void
    {
        $db = $this->getConnection();
        $db->createCommand("ATTACH DATABASE ':memory:' AS `my.schema`")->execute();
        $db->createCommand(
            'CREATE TABLE `my.schema`.`my.customer` (id INTEGER NOT NULL PRIMARY KEY, note varchar(32))'
        )->execute();
        $schema = $db->getSchema();

        $table = $schema->getTableSchema('`my.schema`.`my.customer`');

        $this->assertNotNull($table);
        $this->assertSame('my.schema', $table->schemaName);
        $this->assertSame('my.customer', $table->name);
        $this->assertSame('`my.schema`.`my.customer`', $table->fullName);

        $roundTripped = $schema->getTableSchema($table->fullName);
        $this->assertNotNull($roundTripped);
        $this->assertSame('my.schema', $roundTripped->schemaName);
        $this->assertSame('my.customer', $roundTripped->name);
        $this->assertSame(['id', 'note'], $roundTripped->getColumnNames());
    }

    public function testGetTablePrimaryKeyFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $primaryKey = $schema->getTablePrimaryKey(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertInstanceOf(Constraint::class, $primaryKey);
        $this->assertSame(['id'], $primaryKey->columnNames);
    }

    public function testGetTableForeignKeysFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $foreignKeys = $schema->getTableForeignKeys(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertCount(1, $foreignKeys);
        $this->assertInstanceOf(ForeignKeyConstraint::class, $foreignKeys[0]);
        $this->assertSame(['customer_id'], $foreignKeys[0]->columnNames);
        // foreign keys never cross databases in SQLite, so the referenced table is in the same schema
        $this->assertSame(self::ATTACHED_SCHEMA, $foreignKeys[0]->foreignSchemaName);
        $this->assertSame('customer', $foreignKeys[0]->foreignTableName);
        $this->assertSame(['id'], $foreignKeys[0]->foreignColumnNames);
    }

    public function testGetTableIndexesFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $indexes = $schema->getTableIndexes(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertCount(2, $indexes);
        foreach ($indexes as $index) {
            $this->assertInstanceOf(IndexConstraint::class, $index);
        }
        $indexedColumnNames = [];
        foreach ($indexes as $index) {
            $indexedColumnNames[$index->name] = $index->columnNames;
        }
        $this->assertSame(['customer_id'], $indexedColumnNames['idx_attached_item_customer_id']);
    }

    public function testGetTableUniquesFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $uniques = $schema->getTableUniques(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertCount(1, $uniques);
        $this->assertSame(['slug'], $uniques[0]->columnNames);
    }

    public function testGetTableChecksFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $checks = $schema->getTableChecks(self::ATTACHED_SCHEMA . '.attached_item');

        $this->assertCount(1, $checks);
        $this->assertInstanceOf(CheckConstraint::class, $checks[0]);
        $this->assertSame("slug <> ''", $checks[0]->expression);
    }

    public function testFindUniqueIndexesFromAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);
        $schema = $db->getSchema();

        $table = $schema->getTableSchema(self::ATTACHED_SCHEMA . '.attached_item');
        $uniqueIndexes = $schema->findUniqueIndexes($table);

        $this->assertCount(1, $uniqueIndexes);
        $this->assertSame([['slug']], array_values($uniqueIndexes));
    }

    /**
     * The `temp` schema is hidden from [[Schema::getSchemaNames()]] but still reachable by name.
     */
    public function testGetTableSchemaFromTempSchema(): void
    {
        $db = $this->getConnection();
        $db->createCommand(
            'CREATE TEMPORARY TABLE temp_customer (id INTEGER NOT NULL PRIMARY KEY, note varchar(32))'
        )->execute();

        $table = $db->getSchema()->getTableSchema('temp.temp_customer');

        $this->assertNotNull($table);
        $this->assertSame('temp', $table->schemaName);
        $this->assertSame('temp_customer', $table->name);
        $this->assertSame(['id', 'note'], $table->getColumnNames());
    }

    public function testGetTableSchemaReturnsNullForMissingTableInAttachedSchema(): void
    {
        $db = $this->getConnection();
        $this->attachSecondSchema($db);

        $this->assertNull($db->getSchema()->getTableSchema(self::ATTACHED_SCHEMA . '.no_such_table'));
    }

    /**
     * An unknown schema is a different thing from a missing table: SQLite reports it and we let that through.
     */
    public function testGetTableSchemaFailsForUnknownSchema(): void
    {
        $schema = $this->getConnection()->getSchema();

        $this->expectException(Exception::class);

        $schema->getTableSchema('no_such_schema.customer');
    }

    /**
     * @dataProvider resolveTableNameDataProvider
     */
    public function testResolveTableName(
        string $name,
        string $expectedSchemaName,
        string $expectedName,
        string $expectedFullName
    ): void {
        $schema = $this->getConnection()->getSchema();
        $method = new ReflectionMethod($schema, 'resolveTableName');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $resolvedName = $method->invoke($schema, $name);

        $this->assertSame($expectedSchemaName, $resolvedName->schemaName);
        $this->assertSame($expectedName, $resolvedName->name);
        $this->assertSame($expectedFullName, $resolvedName->fullName);
    }

    public static function resolveTableNameDataProvider(): array
    {
        return [
            'no schema' => ['customer', 'main', 'customer', 'customer'],
            'default schema' => ['main.customer', 'main', 'customer', 'customer'],
            'attached schema' => ['second_schema.customer', 'second_schema', 'customer', 'second_schema.customer'],
            'quoted with backticks' => [
                '`second_schema`.`customer`',
                'second_schema',
                'customer',
                'second_schema.customer',
            ],
            'quoted with double quotes' => [
                '"second_schema"."customer"',
                'second_schema',
                'customer',
                'second_schema.customer',
            ],
            // a part holding a dot or a quote character is quoted back, so that the full name stays unambiguous
            'quoted table name holding a dot' => ['`my.customer`', 'main', 'my.customer', '`my.customer`'],
            'quoted schema name holding a dot' => [
                '`my.schema`.`customer`',
                'my.schema',
                'customer',
                '`my.schema`.customer',
            ],
            'quote character escaped by doubling' => ['`my``customer`', 'main', 'my`customer', '`my``customer`'],
        ];
    }

    /**
     * A table whose name holds a dot is reachable as long as the name is quoted.
     */
    public function testGetTableSchemaOfTableNameHoldingADot(): void
    {
        $db = $this->getConnection();
        $db->createCommand('CREATE TABLE `my.customer` (id INTEGER NOT NULL PRIMARY KEY, note varchar(32))')
            ->execute();

        $table = $db->getSchema()->getTableSchema('`my.customer`');

        $this->assertNotNull($table);
        $this->assertSame('main', $table->schemaName);
        $this->assertSame('my.customer', $table->name);
        $this->assertSame('`my.customer`', $table->fullName);
        $this->assertSame(['id', 'note'], $table->getColumnNames());
    }

    /**
     * Attaches an additional in-memory database as the `second_schema` schema and fills it with tables.
     *
     * Its `customer` table shares its name with the one in the `main` schema but holds different columns, so that
     * tests can tell which of the two was actually read.
     *
     * @param Connection $db the connection to attach the database to.
     */
    private function attachSecondSchema($db)
    {
        $db->createCommand("ATTACH DATABASE ':memory:' AS `" . self::ATTACHED_SCHEMA . '`')->execute();
        $db->createCommand(
            'CREATE TABLE ' . self::ATTACHED_SCHEMA . '.customer (
                id INTEGER NOT NULL,
                code varchar(32) NOT NULL,
                PRIMARY KEY (id)
            )'
        )->execute();
        $db->createCommand(
            'CREATE TABLE ' . self::ATTACHED_SCHEMA . ".attached_item (
                id INTEGER NOT NULL PRIMARY KEY,
                customer_id INTEGER NOT NULL,
                slug varchar(64) NOT NULL UNIQUE,
                CHECK (slug <> ''),
                CONSTRAINT fk_attached_item_customer FOREIGN KEY (customer_id) REFERENCES customer (id)
            )"
        )->execute();
        $db->createCommand(
            'CREATE INDEX ' . self::ATTACHED_SCHEMA . '.idx_attached_item_customer_id ON attached_item (customer_id)'
        )->execute();
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PDO;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use yii\base\NotSupportedException;
use yii\db\CheckConstraint;
use yii\db\Connection;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\DefaultValueConstraint;
use yii\db\Exception;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\TableSchema;
use yiiunit\base\db\providers\ConstraintsProvider;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\Assert;

use function ucfirst;

/**
 * Base unit tests for {@see \yii\db\Schema} constraint and index metadata retrieval across all database drivers.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
abstract class BaseSchemaConstraints extends DatabaseTestCase
{
    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->getSchema();

        $tableSchema = $schema->getTableSchema('composite_fk');

        self::assertCount(
            1,
            $tableSchema->foreignKeys,
            'Number of foreign keys does not match the expected count.',
        );
        self::assertTrue(
            isset($tableSchema->foreignKeys['FK_composite_fk_order_item']),
            "Foreign key 'FK_composite_fk_order_item' is missing in the table schema.",
        );
        self::assertSame(
            'order_item',
            $tableSchema->foreignKeys['FK_composite_fk_order_item'][0],
            "Referenced table name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'order_id',
            $tableSchema->foreignKeys['FK_composite_fk_order_item']['order_id'],
            "Referenced column name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'item_id',
            $tableSchema->foreignKeys['FK_composite_fk_order_item']['item_id'],
            "Referenced column name for foreign key 'FK_composite_fk_order_item' does not match the expected value.",
        );
    }

    public function testConstraintTablesExistence(): void
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
        $uniqueIndexes = $schema->findUniqueIndexes(
            $schema->getTableSchema('uniqueIndex', true),
        );

        self::assertSame(
            [],
            $uniqueIndexes,
            'There should be no unique indexes in the table.',
        );

        $db->createCommand()->createIndex(
            'somecolUnique',
            'uniqueIndex',
            'somecol',
            true,
        )->execute();
        $uniqueIndexes = $schema->findUniqueIndexes(
            $schema->getTableSchema('uniqueIndex', true),
        );

        self::assertEquals(
            ['somecolUnique' => ['somecol']],
            $uniqueIndexes,
            'Unique indexes do not match after creating unique index on "somecol".',
        );

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex(
            'someCol2Unique',
            'uniqueIndex',
            'someCol2',
            true,
        )->execute();
        $uniqueIndexes = $schema->findUniqueIndexes(
            $schema->getTableSchema('uniqueIndex', true),
        );

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol2'.",
        );

        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex(
            'another unique index',
            'uniqueIndex',
            'someCol2',
            true,
        )->execute();
        $uniqueIndexes = $schema->findUniqueIndexes(
            $schema->getTableSchema('uniqueIndex', true),
        );

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

    public function testGetSchemaChecks(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $checks = $schema->getSchemaChecks();

        self::assertIsArray(
            $checks,
            'Schema check constraints should be returned as an array.',
        );

        foreach ($checks as $tableChecks) {
            self::assertIsArray(
                $tableChecks,
                'Table check constraints should be returned as an array.',
            );
            self::assertContainsOnlyInstancesOf(
                CheckConstraint::class,
                $tableChecks,
                'Table check constraints should contain only check constraint instances.',
            );
        }
    }

    public function testGetSchemaDefaultValues(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        if ($this->driverName !== 'sqlsrv') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessageMatches(
                '/does not support default value constraints\.$/',
            );
        }

        $defaultValues = $schema->getSchemaDefaultValues();

        self::assertIsArray(
            $defaultValues,
            'Schema default values should be returned as an array.',
        );

        foreach ($defaultValues as $tableDefaultValues) {
            self::assertIsArray(
                $tableDefaultValues,
                'Table default values should be returned as an array.',
            );
            self::assertContainsOnlyInstancesOf(
                DefaultValueConstraint::class,
                $tableDefaultValues,
                'Table default values should contain only default value constraint instances.',
            );
        }
    }

    public function testGetSchemaForeignKeys(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $foreignKeys = $schema->getSchemaForeignKeys();

        self::assertIsArray(
            $foreignKeys,
            'Schema foreign keys should be returned as an array.',
        );

        foreach ($foreignKeys as $tableForeignKeys) {
            self::assertIsArray(
                $tableForeignKeys,
                'Table foreign keys should be returned as an array.',
            );
            self::assertContainsOnlyInstancesOf(
                ForeignKeyConstraint::class,
                $tableForeignKeys,
                'Table foreign keys should contain only foreign key constraint instances.',
            );
        }
    }

    public function testGetSchemaIndexes(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $indexes = $schema->getSchemaIndexes();

        self::assertIsArray(
            $indexes,
            'Schema indexes should be returned as an array.',
        );

        foreach ($indexes as $tableIndexes) {
            self::assertIsArray(
                $tableIndexes,
                'Table indexes should be returned as an array.',
            );
            self::assertContainsOnlyInstancesOf(
                IndexConstraint::class,
                $tableIndexes,
                'Table indexes should contain only index constraint instances.',
            );
        }
    }

    public function testGetSchemaPrimaryKeys(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $primaryKeys = $schema->getSchemaPrimaryKeys();

        self::assertIsArray(
            $primaryKeys,
            'Schema primary keys should be returned as an array.',
        );
        self::assertContainsOnlyInstancesOf(
            Constraint::class,
            $primaryKeys,
            'Schema primary keys should contain only constraint instances.',
        );
    }

    public function testGetSchemaUniques(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $uniques = $schema->getSchemaUniques();

        self::assertIsArray(
            $uniques,
            'Schema unique constraints should be returned as an array.',
        );

        foreach ($uniques as $tableUniques) {
            self::assertIsArray(
                $tableUniques,
                'Table unique constraints should be returned as an array.',
            );
            self::assertContainsOnlyInstancesOf(
                Constraint::class,
                $tableUniques,
                'Table unique constraints should contain only constraint instances.',
            );
        }
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraints(
        string $tableName,
        string $type,
        Constraint|bool|array|null $expected,
    ): void {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessageMatches(
                '/does not support default value constraints\.$/',
            );
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
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessageMatches(
                '/does not support default value constraints\.$/',
            );
        }

        $db = $this->getConnection(false);

        $db->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        $constraints = $db->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);

        Assert::metadataEquals(
            $expected,
            $constraints,
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        if ($expected === false) {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessageMatches(
                '/does not support default value constraints\.$/',
            );
        }

        $db = $this->getConnection(false);

        $db->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

        $constraints = $db->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);

        Assert::metadataEquals(
            $expected,
            $constraints,
        );
    }

    public function testWorkWithCheckConstraint(): void
    {
        $tableName = 'test_table_with';
        $constraintName = 't_constraint';
        $columnName = 't_field';

        $db = $this->getConnection();
        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        if ($this->driverName === 'sqlite') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessage(
                'yii\db\sqlite\QueryBuilder::addCheck is not supported by SQLite.',
            );

            $db->createCommand()->addCheck(
                $constraintName,
                $tableName,
                "{$columnName} > 0",
            );
        }

        // a nullable column: Oracle materializes `NOT NULL` as an extra implicit check constraint.
        $this->createTableForConstraintTests(
            $db,
            $tableName,
            $columnName,
            'int',
        );

        $db->createCommand()->addCheck(
            $constraintName,
            $tableName,
            $schema->quoteColumnName($columnName) . ' > 0',
        )->execute();

        /** @var CheckConstraint[] $constraints */
        $constraints = $schema->getTableChecks($tableName, true);

        self::assertCount(
            1,
            $constraints,
            'Exactly one check constraint must be reflected after creation.',
        );
        self::assertInstanceOf(
            CheckConstraint::class,
            $constraints[0],
            'Reflected constraint must be a check constraint instance.',
        );
        self::assertSame(
            $constraintName,
            $constraints[0]->name,
            'Constraint name must match the created one.',
        );
        self::assertStringContainsString(
            $columnName,
            $constraints[0]->expression,
            'Expression must reference the constrained column.',
        );

        $db->createCommand()->dropCheck(
            $constraintName,
            $tableName,
        )->execute();

        self::assertCount(
            0,
            $schema->getTableChecks($tableName, true),
            'Check constraint must be gone after drop.',
        );

        $this->dropTableForConstraintTests(
            $db,
            $tableName,
        );
    }

    public function testWorkWithDefaultValueConstraint(): void
    {
        $tableName = 'test_table_with';
        $constraintName = 't_constraint';
        $columnName = 't_field';

        $db = $this->getConnection();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        if ($this->driverName !== 'sqlsrv') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessageMatches(
                '/does not support adding default value constraints\.$|::addDefaultValue is not supported by SQLite\.$/',
            );

            $db->createCommand()->addDefaultValue(
                $constraintName,
                $tableName,
                $columnName,
                919,
            );
        }

        $this->createTableForConstraintTests(
            $db,
            $tableName,
            $columnName,
        );

        $db->createCommand()->addDefaultValue(
            $constraintName,
            $tableName,
            $columnName,
            919,
        )->execute();

        /** @var DefaultValueConstraint[] $constraints */
        $constraints = $schema->getTableDefaultValues($tableName, true);

        self::assertCount(
            1,
            $constraints,
            'Exactly one default value constraint must be reflected after creation.',
        );
        self::assertInstanceOf(
            DefaultValueConstraint::class,
            $constraints[0],
            'Reflected constraint must be a default value constraint instance.',
        );
        self::assertSame(
            $constraintName,
            $constraints[0]->name,
            'Constraint name must match the created one.',
        );
        self::assertSame(
            [$columnName],
            $constraints[0]->columnNames,
            'Constraint columns must match the created one.',
        );
        self::assertStringContainsString(
            '919',
            $constraints[0]->value,
            'Constraint value must contain the configured default.',
        );

        $db->createCommand()->dropDefaultValue(
            $constraintName,
            $tableName,
        )->execute();

        self::assertCount(
            0,
            $schema->getTableDefaultValues($tableName, true),
            'Default value constraint must be gone after drop.',
        );

        $this->dropTableForConstraintTests(
            $db,
            $tableName,
        );
    }

    public function testWorkWithIndex(): void
    {
        $tableName = 'test_table_with';
        $indexName = 't_index';
        $columnName = 't_field';

        $db = $this->getConnection();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $this->createTableForConstraintTests(
            $db,
            $tableName,
            $columnName,
        );

        $db->createCommand()->createIndex(
            $indexName,
            $tableName,
            $columnName,
        )->execute();

        /** @var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($tableName, true);

        self::assertCount(
            1,
            $indexes,
            'Exactly one index must be reflected after creation.',
        );
        self::assertInstanceOf(
            IndexConstraint::class,
            $indexes[0],
            'Reflected index must be an index constraint instance.',
        );
        self::assertSame(
            $indexName,
            $indexes[0]->name,
            'Index name must match the created one.',
        );
        self::assertSame(
            [$columnName],
            $indexes[0]->columnNames,
            'Index columns must match the created one.',
        );
        self::assertFalse(
            $indexes[0]->isUnique,
            'Plain index must not be unique.',
        );
        self::assertFalse(
            $indexes[0]->isPrimary,
            'Plain index must not be primary.',
        );

        $this->dropTableForConstraintTests(
            $db,
            $tableName,
        );
    }

    public function testWorkWithPrimaryKeyConstraint(): void
    {
        $tableName = 'test_table_with';
        $constraintName = 't_constraint';
        $columnName = 't_field';

        $db = $this->getConnection();
        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        if ($this->driverName === 'sqlite') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessage(
                'yii\db\sqlite\QueryBuilder::addPrimaryKey is not supported by SQLite.',
            );

            $db->createCommand()->addPrimaryKey($constraintName, $tableName, $columnName);
        }

        $this->createTableForConstraintTests(
            $db,
            $tableName,
            $columnName,
        );

        $db->createCommand()->addPrimaryKey(
            $constraintName,
            $tableName,
            $columnName,
        )->execute();

        $constraint = $schema->getTablePrimaryKey($tableName, true);

        self::assertInstanceOf(
            Constraint::class,
            $constraint,
            'Primary key must be reflected after creation.',
        );
        self::assertSame(
            [$columnName],
            $constraint->columnNames,
            'Primary key columns must match the created one.',
        );

        if ($this->driverName !== 'mysql') {
            self::assertSame(
                $constraintName,
                $constraint->name,
                'Constraint name must match the created one.',
            );
        }

        $db->createCommand()->dropPrimaryKey(
            $constraintName,
            $tableName,
        )->execute();

        self::assertNull(
            $schema->getTablePrimaryKey($tableName, true),
            'Primary key must be gone after drop.',
        );

        $this->dropTableForConstraintTests(
            $db,
            $tableName,
        );
    }

    public function testWorkWithUniqueConstraint(): void
    {
        $tableName = 'test_table_with';
        $constraintName = 't_constraint';
        $columnName = 't_field';

        $db = $this->getConnection();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        if ($this->driverName === 'sqlite') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessage(
                'yii\db\sqlite\QueryBuilder::addUnique is not supported by SQLite.',
            );

            $db->createCommand()->addUnique(
                $constraintName,
                $tableName,
                $columnName,
            );
        }

        $this->createTableForConstraintTests(
            $db,
            $tableName,
            $columnName,
        );

        $db->createCommand()->addUnique(
            $constraintName,
            $tableName,
            $columnName,
        )->execute();

        /** @var Constraint[] $constraints */
        $constraints = $schema->getTableUniques($tableName, true);

        self::assertCount(
            1,
            $constraints,
            'Exactly one unique constraint must be reflected after creation.',
        );
        self::assertInstanceOf(
            Constraint::class,
            $constraints[0],
            'Reflected constraint must be a constraint instance.',
        );
        self::assertSame(
            $constraintName,
            $constraints[0]->name,
            'Constraint name must match the created one.',
        );
        self::assertSame(
            [$columnName],
            $constraints[0]->columnNames,
            'Constraint columns must match the created one.',
        );

        $db->createCommand()->dropUnique(
            $constraintName,
            $tableName,
        )->execute();

        self::assertCount(
            0,
            $schema->getTableUniques($tableName, true),
            'Unique constraint must be gone after drop.',
        );

        $this->dropTableForConstraintTests(
            $db,
            $tableName,
        );
    }

    /**
     * Creates the working table used by the constraint and index DDL cycle tests.
     *
     * @param Connection $db Database connection to operate on.
     * @param string $tableName Working table name.
     * @param string $columnName Working column name.
     * @param string $columnType Working column type definition.
     */
    protected function createTableForConstraintTests(
        Connection $db,
        string $tableName,
        string $columnName,
        string $columnType = 'int NOT NULL',
    ): void {
        if ($db->getSchema()->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [$columnName => $columnType],
        )->execute();

        self::assertNotNull(
            $db->getSchema()->getTableSchema($tableName, true),
            'Working table must exist before the DDL cycle.',
        );
    }

    /**
     * Drops the working table used by the constraint and index DDL cycle tests.
     *
     * @param Connection $db Database connection to operate on.
     * @param string $tableName Working table name.
     */
    protected function dropTableForConstraintTests(Connection $db, string $tableName): void
    {
        $db->createCommand()->dropTable($tableName)->execute();

        self::assertNull(
            $db->getSchema()->getTableSchema($tableName, true),
            'Working table must be gone after the DDL cycle.',
        );
    }
}

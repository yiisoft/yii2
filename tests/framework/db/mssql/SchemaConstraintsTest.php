<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\CheckConstraint;
use yii\db\Connection;
use yii\db\Constraint;
use yii\db\DefaultValueConstraint;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\mssql\providers\ConstraintsProvider;

use function array_values;

/**
 * Unit tests for {@see \yii\db\mssql\Schema} constraint and index metadata retrieval for the MSSQL driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    public $driverName = 'sqlsrv';

    public function testFindUniqueIndexes(): void
    {
        $db = $this->getConnection();

        $table = $db->getSchema()->getTableSchema('T_upsert');
        $indexes = $db->getSchema()->findUniqueIndexes($table);

        self::assertCount(
            2,
            $indexes,
            "Should return the unique constraints defined on 'T_upsert'.",
        );
        self::assertContains(
            ['email'],
            array_values($indexes),
            "Single-column unique constraint on 'email' is missing.",
        );
        self::assertContains(
            ['email', 'recovery_email'],
            array_values($indexes),
            "Composite unique constraint on 'email, recovery_email' is missing.",
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'compositePrimaryKeyColumnOrder')]
    public function testCompositePrimaryKeyColumnOrder(
        string $tableName,
        string $constraintName,
        string|null $expectedCatalog,
    ): void {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'col_b' => Schema::TYPE_INTEGER . ' NOT NULL',
                'col_a' => Schema::TYPE_INTEGER . ' NOT NULL',
                'col_c' => Schema::TYPE_INTEGER . ' NOT NULL',
                'data' => Schema::TYPE_STRING,
            ],
        )->execute();
        $db->createCommand()->addPrimaryKey(
            $constraintName,
            $tableName,
            ['col_b', 'col_a', 'col_c'],
        )->execute();

        $schema->refreshTableSchema($tableName);

        $tableSchema = $schema->getTableSchema($tableName);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table schema should be returned for existing table.',
        );
        self::assertSame(
            $expectedCatalog,
            $tableSchema->catalogName,
            'Table schema catalog name should match the requested table name.',
        );
        self::assertSame(
            ['col_b', 'col_a', 'col_c'],
            $tableSchema->primaryKey,
            "Composite PK columns should follow 'key_ordinal' order, not alphabetical.",
        );

        $db->createCommand()->dropTable($tableName)->execute();
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'compositeUniqueConstraintColumnOrder')]
    public function testCompositeUniqueConstraintColumnOrder(
        string $tableName,
        string $constraintName,
        string|null $expectedCatalog,
    ): void {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => Schema::TYPE_PK,
                'col_z' => Schema::TYPE_INTEGER . ' NOT NULL',
                'col_y' => Schema::TYPE_INTEGER . ' NOT NULL',
                'col_x' => Schema::TYPE_INTEGER . ' NOT NULL',
            ],
        )->execute();
        $db->createCommand()->addUnique(
            $constraintName,
            $tableName,
            ['col_z', 'col_y', 'col_x'],
        )->execute();

        $schema->refreshTableSchema($tableName);

        $tableSchema = $schema->getTableSchema($tableName);
        $uniqueIndexes = $schema->findUniqueIndexes($tableSchema);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table schema should be returned for existing table.',
        );
        self::assertSame(
            $expectedCatalog,
            $tableSchema->catalogName,
            'Table schema catalog name should match the requested table name.',
        );
        self::assertArrayHasKey(
            $constraintName,
            $uniqueIndexes,
            'Unique constraint should be found by name.',
        );
        self::assertSame(
            ['col_z', 'col_y', 'col_x'],
            $uniqueIndexes[$constraintName],
            "Composite UQ columns should follow 'key_ordinal' order, not alphabetical.",
        );

        $db->createCommand()->dropTable($tableName)->execute();
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableMetadata')]
    public function testCatalogQualifiedTableIndexes(
        string $parentTableName,
        string $childTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $indexes = $schema->getTableIndexes($childTableName, true);

        $index = $this->findConstraintByName($indexes, $constraints['index']);

        self::assertInstanceOf(
            IndexConstraint::class,
            $index,
            'Catalog-qualified index metadata should be reflected from the requested catalog.',
        );
        self::assertSame(
            ['parent_id'],
            $index->columnNames,
            'Index columns should match the created index.',
        );
        self::assertFalse(
            $index->isPrimary,
            'Created plain index should not be primary.',
        );
        self::assertFalse(
            $index->isUnique,
            'Created plain index should not be unique.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableSchemaForeignKeys')]
    public function testCatalogQualifiedTableSchemaForeignKeys(
        string $parentTableName,
        string $childTableName,
        string $expectedForeignTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $tableSchema = $schema->getTableSchema($childTableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table schema should be returned for the catalog-qualified child table.',
        );
        self::assertArrayHasKey(
            $constraints['foreignKey'],
            $tableSchema->foreignKeys,
            'Foreign key should be reflected in table schema metadata.',
        );
        self::assertSame(
            $expectedForeignTableName,
            $tableSchema->foreignKeys[$constraints['foreignKey']][0],
            'Referenced table name should be resolved in the requested catalog context.',
        );
        self::assertSame(
            'id',
            $tableSchema->foreignKeys[$constraints['foreignKey']]['parent_id'],
            'Referenced column should match the parent table primary key.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableForeignKeys')]
    public function testCatalogQualifiedTableForeignKeys(
        string $parentTableName,
        string $childTableName,
        string $expectedForeignSchemaName,
        string $expectedForeignTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $foreignKeys = $schema->getTableForeignKeys($childTableName, true);

        $foreignKey = $this->findConstraintByName($foreignKeys, $constraints['foreignKey']);

        self::assertInstanceOf(
            ForeignKeyConstraint::class,
            $foreignKey,
            'Catalog-qualified foreign key metadata should be reflected.',
        );
        self::assertSame(
            ['parent_id'],
            $foreignKey->columnNames,
            'Foreign key columns should match the child table column.',
        );
        self::assertSame(
            $expectedForeignSchemaName,
            $foreignKey->foreignSchemaName,
            'Foreign schema should match the referenced table schema.',
        );
        self::assertSame(
            $expectedForeignTableName,
            $foreignKey->foreignTableName,
            'Foreign table should match the referenced table name.',
        );
        self::assertSame(
            ['id'],
            $foreignKey->foreignColumnNames,
            'Foreign columns should match the referenced table primary key.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableMetadata')]
    public function testCatalogQualifiedTableUniques(
        string $parentTableName,
        string $childTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $unique = $this->findConstraintByName(
            $schema->getTableUniques($childTableName, true),
            $constraints['unique'],
        );

        self::assertInstanceOf(
            Constraint::class,
            $unique,
            'Catalog-qualified unique constraint metadata should be reflected.',
        );
        self::assertSame(
            ['code'],
            $unique->columnNames,
            'Unique constraint columns should match the created unique constraint.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableMetadata')]
    public function testCatalogQualifiedTableChecks(
        string $parentTableName,
        string $childTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $check = $this->findConstraintByName(
            $schema->getTableChecks($childTableName, true),
            $constraints['check'],
        );

        self::assertInstanceOf(
            CheckConstraint::class,
            $check,
            'Catalog-qualified check constraint metadata should be reflected.',
        );
        self::assertSame(
            ['checked_value'],
            $check->columnNames,
            'Check constraint column should match the created check constraint.',
        );
        self::assertStringContainsString(
            'checked_value',
            $check->expression,
            'Check constraint expression should reference the checked column.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
    }

    #[DataProviderExternal(ConstraintsProvider::class, 'catalogQualifiedTableMetadata')]
    public function testCatalogQualifiedTableDefaultValues(
        string $parentTableName,
        string $childTableName,
        string $constraintSuffix,
    ): void {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $constraints = $this->createCatalogQualifiedConstraintMetadataFixture(
            $db,
            $schema,
            $parentTableName,
            $childTableName,
            $constraintSuffix,
        );

        $default = $this->findConstraintByName(
            $schema->getTableDefaultValues($childTableName, true),
            $constraints['default'],
        );

        self::assertInstanceOf(
            DefaultValueConstraint::class,
            $default,
            'Catalog-qualified default value constraint metadata should be reflected.',
        );
        self::assertSame(
            ['default_value'],
            $default->columnNames,
            'Default value constraint column should match the created default constraint.',
        );
        self::assertStringContainsString(
            '7',
            $default->value,
            'Default value constraint expression should contain the configured value.',
        );

        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );
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
        parent::testTableSchemaConstraints($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected);
    }

    /**
     * Creates parent and child tables with representative constraints for catalog-qualified metadata tests.
     *
     * @return array{index: string, foreignKey: string, unique: string, check: string, default: string}
     */
    private function createCatalogQualifiedConstraintMetadataFixture(
        Connection $db,
        Schema $schema,
        string $parentTableName,
        string $childTableName,
        string $constraintSuffix,
    ): array {
        $this->dropCatalogQualifiedConstraintMetadataFixture(
            $db,
            $parentTableName,
            $childTableName,
        );

        $constraints = [
            'index' => "IX_metadata_{$constraintSuffix}",
            'foreignKey' => "FK_metadata_{$constraintSuffix}",
            'unique' => "UQ_metadata_{$constraintSuffix}",
            'check' => "CK_metadata_{$constraintSuffix}",
            'default' => "DF_metadata_{$constraintSuffix}",
        ];

        $db->createCommand()->createTable(
            $parentTableName,
            ['id' => 'int NOT NULL PRIMARY KEY'],
        )->execute();
        $db->createCommand()->createTable(
            $childTableName,
            [
                'id' => 'int NOT NULL PRIMARY KEY',
                'parent_id' => 'int NOT NULL',
                'code' => 'int NOT NULL',
                'checked_value' => 'int NOT NULL',
                'default_value' => 'int NULL',
            ],
        )->execute();
        $db->createCommand()->createIndex(
            $constraints['index'],
            $childTableName,
            'parent_id',
        )->execute();
        $db->createCommand()->addUnique(
            $constraints['unique'],
            $childTableName,
            'code',
        )->execute();
        $db->createCommand()->addCheck(
            $constraints['check'],
            $childTableName,
            $schema->quoteColumnName('checked_value') . ' > 0',
        )->execute();
        $db->createCommand()->addDefaultValue(
            $constraints['default'],
            $childTableName,
            'default_value',
            7,
        )->execute();
        $db->createCommand()->addForeignKey(
            $constraints['foreignKey'],
            $childTableName,
            'parent_id',
            $parentTableName,
            'id',
            'CASCADE',
            'CASCADE',
        )->execute();

        return $constraints;
    }

    private function dropCatalogQualifiedConstraintMetadataFixture(
        Connection $db,
        string $parentTableName,
        string $childTableName,
    ): void {
        $schema = $db->getSchema();

        if ($schema->getTableSchema($childTableName, true) !== null) {
            $db->createCommand()->dropTable($childTableName)->execute();
        }

        if ($schema->getTableSchema($parentTableName, true) !== null) {
            $db->createCommand()->dropTable($parentTableName)->execute();
        }
    }

    /**
     * @param Constraint[] $constraints Constraint metadata.
     */
    private function findConstraintByName(array $constraints, string $name): Constraint|null
    {
        foreach ($constraints as $constraint) {
            if ($constraint->name === $name) {
                return $constraint;
            }
        }

        return null;
    }
}

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
use yii\db\Constraint;
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
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use Exception;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yiiunit\base\db\BaseSchema;
use yiiunit\framework\db\oci\providers\SchemaProvider;

use function array_filter;
use function array_values;

/**
 * Unit test for {@see yii\db\oci\Schema} schema reflection and metadata retrieval for the Oracle driver.
 */
#[Group('db')]
#[Group('oci')]
#[Group('schema')]
final class SchemaTest extends BaseSchema
{
    public $driverName = 'oci';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    public function testGetTableSequenceNameResolvesIdentityColumnForModernTable(): void
    {
        $schema = $this->getConnection(false)->schema;

        $table = $schema->getTableSchema('profile', true);

        self::assertNotNull(
            $table,
            'IDENTITY-backed fixture table must be loadable.',
        );
        self::assertStringStartsWith(
            'ISEQ$$_',
            (string) $table->sequenceName,
            'IDENTITY-backed sequence name must use the Oracle `ISEQ$$_` prefix.',
        );
        self::assertTrue(
            $table->columns['id']->autoIncrement,
            "IDENTITY column must be flagged as 'autoIncrement'.",
        );
    }

    public function testGetTableSequenceNameResolvesTriggerBackedSequenceForLegacyTable(): void
    {
        $schema = $this->getConnection(false)->schema;

        $table = $schema->getTableSchema('legacy_identity_via_trigger', true);

        self::assertNotNull(
            $table,
            'Legacy fixture table must be loadable.',
        );
        self::assertSame(
            'legacy_identity_via_trigger_SEQ',
            $table->sequenceName,
            'Legacy fallback must surface the trigger-referenced sequence name.',
        );

        self::assertFalse(
            $table->columns['id']->autoIncrement,
            "Trigger-backed PK is not an IDENTITY column 'autoIncrement' must be 'false'.",
        );
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
        parent::testTableSchemaConstraints($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected);
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
                'someCol3' => 'string',
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
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol3', true)->execute();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
                'another unique index' => ['someCol3'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol3'.",
        );
    }

    /**
     * Verifies that LOB indexes (internal Oracle indexes for CLOB/BLOB columns) are excluded from
     * {@see \yii\db\oci\Schema::loadTableIndexes()} results, preventing `null` column names and PHP deprecation
     * warnings in {@see \yii\db\oci\Schema::quoteColumnName()}.
     *
     * @see https://github.com/yiisoft/yii2/pull/20697
     */
    public function testLobIndexesExcluded(): void
    {
        $db = $this->getConnection();

        $dbSchema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $dbSchema,
            'Schema must implement ' . ConstraintFinderInterface::class . ' for LOB index filtering.',
        );

        if ($dbSchema->getTableSchema('lob_test') !== null) {
            $db->createCommand()->dropTable('lob_test')->execute();
        }

        $db->createCommand()->setSql(
            <<<SQL
            CREATE TABLE "lob_test" (
                "id" NUMBER(10) NOT NULL,
                "content" CLOB,
                "data" BLOB, PRIMARY KEY ("id")
            )
            SQL
        )->execute();

        $indexes = $dbSchema->getTableIndexes('lob_test', true);

        self::assertCount(
            1,
            $indexes,
            'Only the primary key index should be present; LOB indexes must be excluded.',
        );

        $primaryIndexes = array_values(
            array_filter($indexes, static fn ($index) => $index->isPrimary),
        );

        self::assertCount(
            1,
            $primaryIndexes,
            'Should be exactly one primary key index.',
        );
        self::assertSame(
            ['id'],
            $primaryIndexes[0]->columnNames,
            'Primary key index columns do not match.',
        );

        foreach ($indexes as $index) {
            foreach ($index->columnNames as $columnName) {
                self::assertNotNull(
                    $columnName,
                    'LOB index with "NULL" column name should be excluded',
                );
                self::assertIsString(
                    $columnName,
                    'Index column name must be a string',
                );
            }
        }

        $db->createCommand()->dropTable('lob_test')->execute();
    }

    public function testPrimaryKeyDefaultPreservedWhileIdentityDefaultIsNullified(): void
    {
        $db = $this->getConnection();

        $dbSchema = $db->getSchema();

        if ($dbSchema->getTableSchema('cr_pk_default') !== null) {
            $db->createCommand()->dropTable('cr_pk_default')->execute();
        }

        $sql = <<<SQL
        CREATE TABLE "cr_pk_default" (
            "pk_int_default" integer DEFAULT 42 NOT NULL,
            "id_identity" NUMBER GENERATED BY DEFAULT ON NULL AS IDENTITY NOT NULL,
            "plain_default" integer DEFAULT 7,
            CONSTRAINT "cr_pk_default_pk" PRIMARY KEY ("pk_int_default")
        )
        SQL;

        $db->createCommand()->setSql($sql)->execute();

        $table = $dbSchema->getTableSchema('cr_pk_default', true);

        self::assertNotNull(
            $table,
            'Fixture table must be loadable.',
        );
        self::assertTrue(
            $table->columns['pk_int_default']->isPrimaryKey,
            'Column must be the primary key.',
        );
        self::assertSame(
            42,
            $table->columns['pk_int_default']->defaultValue,
            'Explicit default on a non-identity PK must be preserved.',
        );
        self::assertTrue(
            $table->columns['id_identity']->autoIncrement,
            'Identity column must be flagged `autoIncrement`.',
        );
        self::assertNull(
            $table->columns['id_identity']->defaultValue,
            'Identity sequence default must be nullified.',
        );
        self::assertSame(
            7,
            $table->columns['plain_default']->defaultValue,
            'Regular column default must be preserved.',
        );

        $db->createCommand()->dropTable('cr_pk_default')->execute();
    }

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->getSchema();

        $table = $schema->getTableSchema('composite_fk');

        $foreignKey = 'tableName';

        self::assertCount(
            1,
            $table->foreignKeys,
            'Number of foreign keys does not match the expected count.',
        );
        self::assertTrue(
            isset($table->foreignKeys[$foreignKey]),
            "Foreign key '{$foreignKey}' is missing in the table schema.",
        );
        self::assertSame(
            'order_item',
            $table->foreignKeys[$foreignKey][0],
            "Referenced table name for foreign key '{$foreignKey}' does not match the expected value.",
        );
        self::assertSame(
            'order_id',
            $table->foreignKeys[$foreignKey]['order_id'],
            "Referenced column name for foreign key '{$foreignKey}' does not match the expected value.",
        );
        self::assertSame(
            'item_id',
            $table->foreignKeys[$foreignKey]['item_id'],
            "Referenced column name for foreign key '{$foreignKey}' does not match the expected value.",
        );
    }
}

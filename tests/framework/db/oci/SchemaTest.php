<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PHPUnit\Framework\Attributes\Group;
use yii\db\ConstraintFinderInterface;
use yii\db\TableSchema;
use yiiunit\base\db\BaseSchema;
use yiiunit\support\DbHelper;

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

    public function testExpressionAndLiteralColumnDefaultValues(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'column_default';

        DbHelper::dropTablesIfExist($db, [$tableName]);

        $db->createCommand()->createTable(
            $tableName,
            [
                'date_expression' => "DATE DEFAULT (DATE '2011-11-11' + 2)",
                'text_expression' => "VARCHAR2(16) DEFAULT UPPER('abc')",
                'number_expression' => 'NUMBER(10, 0) DEFAULT (1 + 2)',
                'timestamp_expression' => "TIMESTAMP DEFAULT TIMESTAMP '2011-11-11 12:34:56'",
                'number_literal' => 'NUMBER(10, 0) DEFAULT 42',
                'text_literal' => "VARCHAR2(32) DEFAULT 'O''Reilly'",
            ],
        )->execute();

        $expressionDefaults = [
            'date_expression' => "(DATE '2011-11-11' + 2)",
            'text_expression' => "UPPER('abc')",
            'number_expression' => '(1 + 2)',
            'timestamp_expression' => "TIMESTAMP '2011-11-11 12:34:56'",
        ];

        foreach ($expressionDefaults as $column => $expression) {
            DbHelper::assertColumnDefaultExpression(
                $db,
                $tableName,
                $column,
                $expression,
            );
        }

        DbHelper::assertColumnDefaultValue(
            $db,
            $tableName,
            'number_literal',
            42,
        );
        DbHelper::assertColumnDefaultValue(
            $db,
            $tableName,
            'text_literal',
            "O'Reilly",
        );

        DbHelper::dropTablesIfExist($db, [$tableName]);
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

    public function testResolveAndFindTableNamesWithExplicitSchema(): void
    {
        $schema = $this->getConnection()->getSchema();
        $schemaName = $schema->defaultSchema;

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        self::assertContains(
            'profile',
            $schema->getTableNames($schemaName, true),
            "Table 'profile' should be present when listing tables with an explicit schema name.",
        );
        self::assertSame(
            ['id'],
            $schema->getTablePrimaryKey("{$schemaName}.profile", true)->columnNames,
            'Primary key metadata should be reflected with an explicit schema name.',
        );

        $table = $schema->getTableSchema("{$schemaName}.profile", true);

        self::assertInstanceOf(
            TableSchema::class,
            $table,
            'Table schema should be loadable with an explicit schema name.',
        );
        self::assertSame(
            $schemaName,
            $table->schemaName,
            'Loaded table schema should keep the explicit schema name.',
        );
        self::assertSame(
            'profile',
            $table->name,
            'Loaded table name should match expected value.',
        );
    }

    public function testGetTableSchemaWithQuotedTableName(): void
    {
        $schema = $this->getConnection()->getSchema();

        $tableSchema = $schema->getTableSchema('"profile"', true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table schema should be loadable with a quoted table name.',
        );
        self::assertSame(
            'profile',
            $tableSchema->name,
            'Loaded table name should not keep quote characters.',
        );
    }

    public function testIntegerDataTypeColumn(): void
    {
        $table = $this->getConnection()->getSchema()->getTableSchema('employee');

        self::assertInstanceOf(
            TableSchema::class,
            $table,
            'Employee fixture table should be loadable.',
        );
        self::assertSame(
            'integer',
            $table->columns['id']->type,
            "An 'INTEGER' fixture column should be reflected as an integer.",
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
}

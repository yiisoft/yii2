<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\Constraint;
use yii\db\Expression;
use yii\db\sqlite\Schema;
use yiiunit\base\db\BaseSchema;
use yiiunit\framework\db\sqlite\providers\SchemaProvider;

/**
 * Unit tests for {@see \yii\db\sqlite\Schema} schema reflection and metadata retrieval for the SQLite driver.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('schema')]
final class SchemaTest extends BaseSchema
{
    protected $driverName = 'sqlite';

    public function testGetSchemaNames(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            Schema::class . ' does not support fetching all schema names.',
        );

        $this->getConnection()->getSchema()->getSchemaNames();
    }

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(SchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->getSchema();

        $table = $schema->getTableSchema('composite_fk');

        self::assertCount(
            1,
            $table->foreignKeys,
            'There must be exactly one foreign key defined for the table.',
        );
        self::assertTrue(
            isset($table->foreignKeys[0]),
            'Foreign key must be defined.',
        );
        self::assertEquals(
            'order_item',
            $table->foreignKeys[0][0],
            'Referenced table name does not match.',
        );
        self::assertEquals(
            'order_id',
            $table->foreignKeys[0]['order_id'],
            'Referenced column name does not match.',
        );
        self::assertEquals(
            'item_id',
            $table->foreignKeys[0]['item_id'],
            'Referenced column name does not match.',
        );
    }

    public function testCurrentTimestampLowercaseDefaultValue(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_current_ts') !== null) {
            $db->createCommand()->dropTable('test_default_current_ts')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_current_ts',
            [
                'id' => 'pk',
                'ts_col' => 'timestamp NOT NULL DEFAULT current_timestamp',
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_current_ts');

        $tableSchema = $db->schema->getTableSchema('test_default_current_ts');

        self::assertEquals(
            new Expression('CURRENT_TIMESTAMP'),
            $tableSchema->getColumn('ts_col')->defaultValue,
            "Default must be a 'CURRENT_TIMESTAMP' expression.",
        );
    }

    public function testUppercaseNullDefaultValue(): void
    {
        $db = $this->getConnection();

        $table = $db->schema->getTableSchema('null_values');

        self::assertNull(
            $table->getColumn('var3')->defaultValue,
            "Integer default must be 'null', not '0'.",
        );
        self::assertNull(
            $table->getColumn('stringcol')->defaultValue,
            "String default must be 'null', not the literal 'NULL'.",
        );
    }

    public function testEscapedSingleQuoteDefaultValue(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_escaped_quote') !== null) {
            $db->createCommand()->dropTable('test_default_escaped_quote')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_escaped_quote',
            [
                'id' => 'pk',
                'str_col' => "varchar(32) NOT NULL DEFAULT 'it''s'",
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_escaped_quote');

        $tableSchema = $db->schema->getTableSchema('test_default_escaped_quote');

        self::assertSame(
            "it's",
            $tableSchema->getColumn('str_col')->defaultValue,
            'Doubled single quote must resolve to one quote.',
        );
    }

    public function testDoubleQuotedDefaultValue(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_double_quoted') !== null) {
            $db->createCommand()->dropTable('test_default_double_quoted')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_double_quoted',
            [
                'id' => 'pk',
                'str_col' => 'varchar(32) NOT NULL DEFAULT "do""uble"',
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_double_quoted');

        $tableSchema = $db->schema->getTableSchema('test_default_double_quoted');

        self::assertSame(
            'do"uble',
            $tableSchema->getColumn('str_col')->defaultValue,
            'Doubled double quote must resolve to one quote.',
        );
    }

    public function testEmptyStringDefaultValue(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_empty_string') !== null) {
            $db->createCommand()->dropTable('test_default_empty_string')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_empty_string',
            [
                'id' => 'pk',
                'str_col' => "varchar(32) NOT NULL DEFAULT ''",
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_empty_string');

        $tableSchema = $db->schema->getTableSchema('test_default_empty_string');

        self::assertSame(
            '',
            $tableSchema->getColumn('str_col')->defaultValue,
            "Empty string literal must stay an empty 'string', not 'null'.",
        );
    }

    public function testBooleanKeywordDefaultValues(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_boolean_keyword') !== null) {
            $db->createCommand()->dropTable('test_default_boolean_keyword')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_boolean_keyword',
            [
                'id' => 'pk',
                'bool_true' => 'boolean NOT NULL DEFAULT true',
                'bool_false' => 'boolean NOT NULL DEFAULT FALSE',
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_boolean_keyword');

        $tableSchema = $db->schema->getTableSchema('test_default_boolean_keyword');

        self::assertTrue(
            $tableSchema->getColumn('bool_true')->defaultValue,
            "Keyword `true` must cast to 'true'.",
        );
        self::assertFalse(
            $tableSchema->getColumn('bool_false')->defaultValue,
            "Keyword 'FALSE' must cast to 'false'.",
        );
    }

    public function testExpressionDefaultValueIsPreservedAsString(): void
    {
        $db = $this->getConnection(false);

        if ($db->schema->getTableSchema('test_default_expression') !== null) {
            $db->createCommand()->dropTable('test_default_expression')->execute();
        }

        $db->createCommand()->createTable(
            'test_default_expression',
            [
                'id' => 'pk',
                'expr_col' => "text DEFAULT (datetime('now'))",
            ],
        )->execute();

        $db->schema->refreshTableSchema('test_default_expression');

        $tableSchema = $db->schema->getTableSchema('test_default_expression');

        self::assertSame(
            "datetime('now')",
            $tableSchema->getColumn('expr_col')->defaultValue,
            'Expression default must be preserved verbatim.',
        );
    }

    public function testPrimaryKeyDefaultValueIsNull(): void
    {
        $db = $this->getConnection();

        $table = $db->schema->getTableSchema('default_pk');
        $column = $table->getColumn('id');

        self::assertTrue(
            $column->isPrimaryKey,
            'Column must be flagged as primary key.',
        );
        self::assertNull(
            $column->defaultValue,
            "Primary key default must stay 'null'.",
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

    /**
     * @throws NotSupportedException
     */
    #[DataProviderExternal(SchemaProvider::class, 'quoteTableName')]
    public function testQuoteTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();
        $quotedName = $schema->quoteTableName($name);
        $this->assertEquals($expectedName, $quotedName);
    }
}

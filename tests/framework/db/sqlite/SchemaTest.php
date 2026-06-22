<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\ConstraintFinderInterface;
use yii\db\Expression;
use yii\db\Transaction;
use yii\db\sqlite\Schema;
use yiiunit\base\db\BaseSchema;

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

    public function testThrowNotSupportedExceptionWhenResolveTableNameIsNotSupported(): void
    {
        $schema = $this->getConnection()->getSchema();

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            Schema::class . ' does not support resolving table names.',
        );

        $schema->resolveRawTableName('profile');
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

    public function testLoadTableChecksReturnsEmptyForViews(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        self::assertSame(
            [],
            $schema->getTableChecks('animal_view', true),
            'Views should not expose table check constraints.',
        );
    }

    public function testNamedCheckConstraint(): void
    {
        $schema = $this->getConnection()->schema;

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        $checks = $schema->getTableChecks('T_check_constraint', true);

        self::assertCount(
            1,
            $checks,
            'Exactly one check constraint should be reflected.',
        );
        self::assertSame(
            'ck_named_value',
            $checks[0]->name,
            'Named check constraint should keep its name.',
        );
    }

    public function testThrowNotSupportedExceptionWhenSettingUnsupportedTransactionIsolationLevel(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            Schema::class . ' only supports transaction isolation levels READ UNCOMMITTED and SERIALIZABLE.',
        );

        $this->getConnection()->getSchema()->setTransactionIsolationLevel(Transaction::READ_COMMITTED);
    }
}

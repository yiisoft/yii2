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
use yii\db\Constraint;
use yii\db\Expression;
use yiiunit\framework\db\AnyValue;
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
        $this->markTestSkipped('Schemas are not supported in SQLite.');
    }

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
     *
     * @param string $name Table name.
     * @param string $expectedName Expected quoted table name.
     *
     * @throws NotSupportedException
     */
    public function testQuoteTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();
        $quotedName = $schema->quoteTableName($name);
        $this->assertEquals($expectedName, $quotedName);
    }

    public static function quoteTableNameDataProvider(): array
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
}

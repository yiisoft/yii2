<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\db\TableSchema;
use yiiunit\base\db\providers\ColumnSchemaProvider;
use yiiunit\framework\db\DatabaseTestCase;

use function array_keys;
use function is_object;
use function sort;

/**
 * Base unit tests for {@see \yii\db\ColumnSchema} column reflection and type-casting across all database drivers.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
abstract class BaseColumnSchema extends DatabaseTestCase
{
    public function testColumnComment(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();
        $schema = $db->getSchema();

        if ($this->driverName === 'sqlite') {
            $this->expectException(NotSupportedException::class);
            $this->expectExceptionMessage(
                'yii\db\sqlite\QueryBuilder::addCommentOnColumn is not supported by SQLite.',
            );

            $command->addCommentOnColumn(
                'testCommentTable',
                'bar',
                'Test comment for column.',
            );
        }

        if ($schema->getTableSchema('testCommentTable', true) !== null) {
            $command->dropTable('testCommentTable')->execute();
        }

        $command->createTable(
            'testCommentTable',
            ['bar' => Schema::TYPE_INTEGER],
        )->execute();
        $command->addCommentOnColumn(
            'testCommentTable',
            'bar',
            'Test comment for column.',
        )->execute();

        self::assertSame(
            'Test comment for column.',
            $schema->getTableSchema('testCommentTable', true)->getColumn('bar')->comment,
            'Column comment must be reflected after being added.',
        );

        $command->dropTable('testCommentTable')->execute();
    }

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        $schema = $this->getConnection(false)->getSchema();

        $tableSchema = $schema->getTableSchema('type', true);

        $expectedColNames = array_keys($columns);

        sort($expectedColNames);

        $colNames = $tableSchema->columnNames;

        sort($colNames);

        self::assertSame(
            $expectedColNames,
            $colNames,
            'Column names do not match expected column names.',
        );

        foreach ($tableSchema->columns as $name => $column) {
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

    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchemaDbTypecastBooleanPhpType')]
    public function testColumnSchemaDbTypecastBooleanPhpType(mixed $value, bool $expected): void
    {
        $columnSchema = new ColumnSchema(['phpType' => Schema::TYPE_BOOLEAN]);

        self::assertSame(
            $expected,
            $columnSchema->dbTypecast($value),
            "'dbTypecast' did not return the expected boolean.",
        );
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

    public function testGetColumnNoExist(): void
    {
        $schema = $this->getConnection()->getSchema();

        $tableSchema = $schema->getTableSchema('negative_default_values');

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table schema should be available.',
        );
        self::assertNull(
            $tableSchema->getColumn('no_exist'),
            "Getting a non-existing column should return 'null'.",
        );
    }

    public function testNegativeDefaultValues(): void
    {
        $schema = $this->getConnection()->getSchema();

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
}

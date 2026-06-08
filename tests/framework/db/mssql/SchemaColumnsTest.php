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
use yii\db\Exception;
use yii\db\mssql\ColumnSchema;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yiiunit\data\db\Connection;
use yiiunit\framework\db\mssql\providers\SchemaColumnsProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\mssql\Schema} column discovery and column metadata loading.
 *
 * {@see Connection} for the connection stub.
 * {@see SchemaColumnsProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaColumnsTest extends TestCase
{
    public function testFindColumnsPropagatesColumnQueryFailure(): void
    {
        $schema = new Schema();

        $schema->db = new Connection(
            queryAllException: new Exception('Invalid object name.'),
        );

        $table = new TableSchema();

        $table->name = 'profile';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Invalid object name.',
        );

        $this->invokeMethod(
            $schema,
            'findColumns',
            [$table],
        );
    }

    public function testFindColumnsReturnsFalseWhenColumnQueryYieldsNoRows(): void
    {
        $schema = new Schema();

        $schema->db = new Connection();

        $table = new TableSchema();

        $table->name = 'profile';

        self::assertFalse(
            $this->invokeMethod(
                $schema,
                'findColumns',
                [$table],
            ),
            'An empty column set must report the table as missing.',
        );
    }

    #[DataProviderExternal(SchemaColumnsProvider::class, 'findColumnsQuery')]
    public function testFindColumnsBuildsExpectedQuery(
        array $tableParts,
        string $expectedSql,
        array $expectedParams,
    ): void {
        $schema = new Schema();

        $schema->db = new Connection();

        $table = new TableSchema();

        foreach ($tableParts as $property => $value) {
            $table->{$property} = $value;
        }

        $this->invokeMethod(
            $schema,
            'findColumns',
            [$table],
        );

        self::assertSame(
            $expectedSql,
            $schema->db->sql,
            'Catalog query must match the sys.columns metadata statement.',
        );
        self::assertSame(
            $expectedParams,
            $schema->db->params,
            'Table name parts must be quoted independently and bound as a parameter.',
        );
    }

    public function testFindColumnsLoadsCatalogViewMetadata(): void
    {
        $schema = new Schema();

        $schema->db = new Connection(
            queryAllRows: [
                [
                    'column_name' => 'id',
                    'is_nullable' => 'NO',
                    'data_type' => 'int',
                    'column_default' => '((1))',
                    'is_identity' => 1,
                    'is_computed' => 0,
                    'comment' => null,
                ],
                [
                    'column_name' => 'amount',
                    'is_nullable' => 'YES',
                    'data_type' => 'decimal(10,2)',
                    'column_default' => '((3.14))',
                    'is_identity' => 0,
                    'is_computed' => 0,
                    'comment' => 'Amount comment.',
                ],
                [
                    'column_name' => 'payload',
                    'is_nullable' => 'YES',
                    'data_type' => 'varbinary(max)',
                    'column_default' => null,
                    'is_identity' => 0,
                    'is_computed' => 0,
                    'comment' => null,
                ],
                [
                    'column_name' => 'computed_amount',
                    'is_nullable' => 'YES',
                    'data_type' => 'int',
                    'column_default' => null,
                    'is_identity' => 0,
                    'is_computed' => 1,
                    'comment' => null,
                ],
            ],
        );

        $table = new TableSchema();

        $table->catalogName = 'yiitest';
        $table->schemaName = 'dbo';
        $table->name = 'table.with.special.characters';
        $table->primaryKey = ['id'];

        self::assertTrue(
            $this->invokeMethod(
                $schema,
                'findColumns',
                [$table],
            ),
            'A non-empty sys catalog result must report the table as existing.',
        );
        self::assertArrayHasKey(
            'id',
            $table->columns,
            'Primary key column must be loaded.',
        );
        self::assertTrue(
            $table->columns['id']->isPrimaryKey,
            'Primary key membership must be applied after loading columns.',
        );
        self::assertTrue(
            $table->columns['id']->autoIncrement,
            'Identity flag must be reflected from sys.columns.',
        );
        self::assertSame(
            '',
            $table->sequenceName,
            'Identity primary key must set the sequence name marker.',
        );
        self::assertNull(
            $table->columns['id']->defaultValue,
            'Primary key defaults must be ignored.',
        );
        self::assertSame(
            'decimal(10,2)',
            $table->columns['amount']->dbType,
            'Decimal precision and scale must be preserved.',
        );
        self::assertSame(
            10,
            $table->columns['amount']->size,
            'Decimal size must be parsed from the catalog metadata.',
        );
        self::assertSame(
            10,
            $table->columns['amount']->precision,
            'Decimal precision must be parsed from the catalog metadata.',
        );
        self::assertSame(
            2,
            $table->columns['amount']->scale,
            'Decimal scale must be parsed from the catalog metadata.',
        );
        self::assertSame(
            '3.14',
            $table->columns['amount']->defaultValue,
            'Decimal defaults must be resolved after the primary key is known.',
        );
        self::assertSame(
            'Amount comment.',
            $table->columns['amount']->comment,
            'Extended property comments must be assigned.',
        );
        self::assertSame(
            'varbinary(max)',
            $table->columns['payload']->dbType,
            'max variable-length declarations must be preserved.',
        );
        self::assertNull(
            $table->columns['payload']->size,
            'max variable-length declarations must not be parsed as size 0.',
        );

        $computedColumn = $table->columns['computed_amount'];

        self::assertInstanceOf(
            ColumnSchema::class,
            $computedColumn,
            'Computed column must be an MSSQL column schema instance.',
        );
        self::assertTrue(
            $computedColumn->isComputed,
            'Computed flag must be reflected from sys.columns.',
        );
    }

    public function testLoadColumnSchemaLeavesScaleNullForSizeOnlyDbType(): void
    {
        $column = $this->invokeMethod(
            new Schema(),
            'loadColumnSchema',
            [$this->createColumnInfo('varchar(255)')],
        );

        self::assertSame(
            255,
            $column->size,
            'Size must be parsed from the sized db type.',
        );
        self::assertSame(
            255,
            $column->precision,
            'Precision must mirror the parsed size.',
        );
        self::assertNull(
            $column->scale,
            "Scale must stay 'null' without a scale component.",
        );
    }

    public function testLoadColumnSchemaLeavesSizeNullForMaxDbType(): void
    {
        $column = $this->invokeMethod(
            new Schema(),
            'loadColumnSchema',
            [$this->createColumnInfo('varbinary(max)')],
        );

        self::assertSame(
            'varbinary(max)',
            $column->dbType,
            'The max db type declaration must be preserved.',
        );
        self::assertNull(
            $column->size,
            'max must not be cast to size 0.',
        );
        self::assertNull(
            $column->precision,
            'max must not be cast to precision 0.',
        );
        self::assertNull(
            $column->scale,
            'max must not set a scale.',
        );
    }

    public function testLoadColumnSchemaParsesScaleFromSizedDbType(): void
    {
        $column = $this->invokeMethod(
            new Schema(),
            'loadColumnSchema',
            [$this->createColumnInfo('decimal(10,2)')],
        );

        self::assertSame(
            'decimal',
            $column->type,
            'Abstract type must be resolved from the db type name.',
        );
        self::assertSame(
            10,
            $column->size,
            'Size must be parsed from the first sized component.',
        );
        self::assertSame(
            10,
            $column->precision,
            'Precision must mirror the parsed size.',
        );
        self::assertSame(
            2,
            $column->scale,
            'Scale must be parsed from the second sized component.',
        );
    }

    /**
     * Creates raw column information as produced by the MSSQL column query.
     *
     * @param string $dbType Column db type definition.
     *
     * @return array<string, mixed>
     */
    private function createColumnInfo(string $dbType): array
    {
        return [
            'column_name' => 'amount',
            'is_nullable' => 'NO',
            'data_type' => $dbType,
            'is_identity' => 0,
            'is_computed' => 0,
            'comment' => null,
            'column_default' => null,
        ];
    }
}

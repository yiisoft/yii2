<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PDO;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Exception;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yiiunit\data\db\Connection;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\mssql\Schema} column discovery and column metadata loading.
 *
 * {@see Connection} for the connection stub.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaColumnsTest extends TestCase
{
    public function testFindColumnsReturnsFalseWhenColumnQueryFails(): void
    {
        $schema = new Schema();

        $schema->db = new Connection(
            1,
            new PDO('sqlite::memory:'),
            false,
            new Exception('Invalid object name.'),
        );

        $table = new TableSchema();

        $table->name = 'profile';

        self::assertFalse(
            $this->invokeMethod(
                $schema,
                'findColumns',
                [$table],
            ),
            'A failing column query must report the table as missing.',
        );
    }

    public function testFindColumnsReturnsFalseWhenColumnQueryYieldsNoRows(): void
    {
        $schema = new Schema();

        $schema->db = new Connection(
            1,
            new PDO('sqlite::memory:'),
        );

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
            'Scale must stay `null` without a scale component.',
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

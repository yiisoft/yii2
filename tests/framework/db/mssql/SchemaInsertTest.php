<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yii\db\ColumnSchema;
use yii\db\TableSchema;
use yiiunit\data\db\Connection;
use yiiunit\data\db\mssql\Schema;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\db\mssql\Schema} insert primary key composition fallbacks.
 *
 * {@see Schema} and {@see Connection} for the stubs.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaInsertTest extends TestCase
{
    public function testInsertReturnsFalseWhenCommandExecutionFails(): void
    {
        $schema = new Schema($this->createTableSchema());

        $schema->db = new Connection(false);

        self::assertFalse(
            $schema->insert(
                'profile',
                ['description' => 'test'],
            ),
            'Schema insert should return false when command execution fails.',
        );
    }

    public function testInsertReturnsPrimaryKeyDefaultValueWhenColumnNotProvided(): void
    {
        $schema = new Schema($this->createTableSchema());

        $schema->db = new Connection(1);

        self::assertSame(
            ['id' => 42],
            $schema->insert(
                'profile',
                ['description' => 'test'],
            ),
            'Primary key default value must be returned when no row is fetched and the column is not provided.',
        );
    }

    public function testInsertReturnsPrimaryKeyFromOutputInsertedRow(): void
    {
        $schema = new Schema($this->createTableSchema());

        $schema->db = new Connection(1, null, ['id' => 5, 'description' => 'test']);

        self::assertSame(
            ['id' => 5],
            $schema->insert(
                'profile',
                ['description' => 'test'],
            ),
            'Primary key must be taken from the fetched `OUTPUT INSERTED` row.',
        );
    }

    public function testInsertReturnsProvidedPrimaryKeyValueWhenOutputRowLacksIt(): void
    {
        $schema = new Schema($this->createTableSchema());

        $schema->db = new Connection(1, null, ['description' => 'test']);

        self::assertSame(
            ['id' => 7],
            $schema->insert(
                'profile',
                [
                    'id' => 7,
                    'description' => 'test',
                ],
            ),
            'Provided primary key value must be returned when the fetched row lacks the column.',
        );
    }

    private function createTableSchema(): TableSchema
    {
        $column = new ColumnSchema();

        $column->name = 'id';
        $column->defaultValue = 42;

        $tableSchema = new TableSchema();

        $tableSchema->primaryKey = ['id'];
        $tableSchema->columns = ['id' => $column];

        return $tableSchema;
    }
}

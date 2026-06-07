<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use PDO;
use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\QueryBuilder;
use yii\db\TableSchema;
use yiiunit\data\db\Connection;
use yiiunit\data\db\Schema;
use yiiunit\TestCase;

use function get_class;

/**
 * Unit tests for {@see \yii\db\Schema} base schema behavior.
 *
 * {@see Schema} and {@see Connection} for the stubs.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('schema')]
final class SchemaTest extends TestCase
{
    public function testCreateQueryBuilder(): void
    {
        $db = new Connection();

        $schema = new Schema();

        $schema->db = $db;

        $queryBuilder = $schema->createQueryBuilder();

        self::assertInstanceOf(
            QueryBuilder::class,
            $queryBuilder,
            'Base schema should create a base query builder instance.',
        );
        self::assertSame(
            $db,
            $queryBuilder->db,
            'Query builder should receive the schema database connection.',
        );
    }

    public function testInsertReturnsFalseWhenCommandExecutionFails(): void
    {
        $schema = new Schema();

        $schema->db = new Connection(false);

        self::assertFalse(
            $schema->insert('profile', ['description' => 'test']),
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
            'Primary key default value must be returned when the column is not provided.',
        );
    }

    public function testNormalizePdoRowKeyCase(): void
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

        $schema = new Schema();
        $schema->db = new Connection(1, $pdo);

        self::assertSame(
            ['id' => 1, 'name' => 'test'],
            $this->invokeMethod(
                $schema,
                'normalizePdoRowKeyCase',
                [
                    [
                        'ID' => 1,
                        'NAME' => 'test',
                    ],
                    false,
                ],
            ),
            'Single row keys must be lowercased when PDO returns uppercase keys.',
        );
        self::assertSame(
            [['id' => 1], ['name' => 'test']],
            $this->invokeMethod(
                $schema,
                'normalizePdoRowKeyCase',
                [
                    [
                        ['ID' => 1],
                        ['NAME' => 'test'],
                    ],
                    true,
                ],
            ),
            'Row set keys must be lowercased when PDO returns uppercase keys.',
        );
    }

    public function testThrowNotSupportedExceptionWhenGetTableNamesIsNotSupported(): void
    {
        $schema = new Schema();

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            get_class($schema) . ' does not support fetching all table names.',
        );

        $schema->getTableNames();
    }

    public function testThrowNotSupportedExceptionWhenFindUniqueIndexesIsNotSupported(): void
    {
        $schema = new Schema();

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            get_class($schema) . ' does not support getting unique indexes information.',
        );

        $schema->findUniqueIndexes(new TableSchema());
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

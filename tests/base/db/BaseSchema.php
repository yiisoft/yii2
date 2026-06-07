<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use yii\base\InvalidCallException;
use yii\db\Exception;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\base\db\providers\SchemaProvider;
use PDO;
use yii\db\ColumnSchemaBuilder;
use yii\db\QueryBuilder;
use yii\db\TableSchema;

use function array_key_first;
use function array_map;
use function fclose;
use function count;
use function fopen;
use function print_r;
use function trim;

/**
 * Base unit tests for {@see \yii\db\Schema} schema reflection and table metadata retrieval across all database drivers.
 *
 * {@see SchemaProvider} for test case data providers.
 */
abstract class BaseSchema extends DatabaseTestCase
{
    /**
     * @var list<string> List of expected schemas in the database.
     */
    protected array $expectedSchemas = [];

    public function testCreateColumnSchemaBuilder(): void
    {
        $schema = $this->getConnection()->getSchema();

        $columnSchemaBuilder = $schema->createColumnSchemaBuilder('string');

        self::assertInstanceOf(
            ColumnSchemaBuilder::class,
            $columnSchemaBuilder,
            'Column schema builder should be created.',
        );
        self::assertSame(
            'string',
            (string) $columnSchemaBuilder,
            'Column schema builder type does not match.',
        );
    }

    public function testCreateQueryBuilder(): void
    {
        $db = $this->getConnection();

        $queryBuilder = $db->getSchema()->createQueryBuilder();

        self::assertInstanceOf(
            QueryBuilder::class,
            $queryBuilder,
            'Query builder should be created.',
        );
        self::assertSame(
            $db,
            $queryBuilder->db,
            'Query builder should receive the schema database connection.',
        );
    }

    public function testGetSchemaNames(): void
    {
        $schema = $this->getConnection()->getSchema();

        $schemas = $schema->getSchemaNames();

        self::assertNotEmpty(
            $schemas,
            'The list of schema names is empty.',
        );

        foreach ($this->expectedSchemas as $schema) {
            self::assertContains(
                $schema,
                $schemas,
                "Schema '{$schema}' is missing in the list of schemas.",
            );
        }
    }

    /**
     * @param array<int, bool> $pdoAttributes PDO attributes to be applied to the connection.
     */
    #[DataProviderExternal(SchemaProvider::class, 'pdoAttributes')]
    public function testGetTableNames(array $pdoAttributes): void
    {
        $db = $this->getConnection();

        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $db->driverName === 'sqlsrv') {
                continue;
            }

            $db->pdo->setAttribute($name, $value);
        }

        $schema = $db->getSchema();
        $tables = $schema->getTableNames();

        if ($this->driverName === 'sqlsrv') {
            $tables = array_map(static fn($item): string => trim((string) $item, '[]'), $tables);
        }

        $expectedTables = [
            'customer',
            'category',
            'item',
            'order',
            'order_item',
            'type',
            'animal',
            'animal_view',
        ];

        foreach ($expectedTables as $expectedTable) {
            self::assertContains(
                $expectedTable,
                $tables,
                "'{$expectedTable}' table is missing in the list of tables.",
            );
        }
    }

    /**
     * @param string $name Table name to resolve.
     * @param string $expectedName Expected resolved table name.
     */
    #[DataProviderExternal(SchemaProvider::class, 'getTableSchema')]
    public function testGetTableSchema(string $name, string $expectedName): void
    {
        $tableSchema = $this->getConnection()->getSchema()->getTableSchema($name);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Resolved table schema must be loadable.',
        );
        self::assertSame(
            $expectedName,
            $tableSchema->name,
            'Resolved table name does not match.',
        );
    }

    /**
     * @param array<int, bool> $pdoAttributes PDO attributes to be applied to the connection.
     */
    #[DataProviderExternal(SchemaProvider::class, 'pdoAttributes')]
    public function testGetTableSchemas(array $pdoAttributes): void
    {
        $db = $this->getConnection();

        foreach ($pdoAttributes as $name => $value) {
            if ($name === PDO::ATTR_EMULATE_PREPARES && $db->driverName === 'sqlsrv') {
                continue;
            }

            $db->pdo->setAttribute($name, $value);
        }

        $schema = $db->getSchema();
        $tables = $schema->getTableSchemas();

        self::assertSame(
            count($schema->getTableNames()),
            count($tables),
            'Number of table schemas does not match the number of table names.',
        );

        foreach ($tables as $table) {
            self::assertInstanceOf(
                TableSchema::class,
                $table,
                'Table schema is not an instance of ' . TableSchema::class . '.',
            );
        }
    }

    public function testGetTableSchemasWithAttrCase(): void
    {
        $db = $this->getConnection(false);

        $db->slavePdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $schema = $db->getSchema();

        self::assertSame(
            count($schema->getTableNames()),
            count($schema->getTableSchemas()),
            "Number of table does not match the number of table names with 'PDO::ATTR_CASE' set to 'PDO::CASE_LOWER'.",
        );

        $db->slavePdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
        $schema = $db->getSchema();

        self::assertSame(
            count($schema->getTableNames()),
            count($schema->getTableSchemas()),
            "Number of table does not match the number of table names with 'PDO::ATTR_CASE' set to 'PDO::CASE_UPPER'.",
        );
    }

    public function testGetNonExistingTableSchema(): void
    {
        self::assertNull(
            $this->getConnection()->getSchema()->getTableSchema('nonexisting_table'),
            "Getting schema for non-existing table should return 'null'.",
        );
    }

    public function testGetPDOType(): void
    {
        $values = [
            [null, PDO::PARAM_NULL],
            ['', PDO::PARAM_STR],
            ['hello', PDO::PARAM_STR],
            [0, PDO::PARAM_INT],
            [1, PDO::PARAM_INT],
            [1337, PDO::PARAM_INT],
            [true, PDO::PARAM_BOOL],
            [false, PDO::PARAM_BOOL],
            [$fp = fopen(__FILE__, 'rb'), PDO::PARAM_LOB],
        ];

        $schema = $this->getConnection()->getSchema();

        foreach ($values as $value) {
            self::assertSame(
                $value[1],
                $schema->getPdoType($value[0]),
                'type for value ' . print_r($value[0], true) . ' does not match.',
            );
        }

        fclose($fp);
    }

    public function testSupportsSavepoint(): void
    {
        self::assertSame(
            $this->getConnection()->enableSavepoint,
            $this->getConnection()->getSchema()->supportsSavepoint(),
            'Savepoint support should match the connection configuration.',
        );
    }

    public function testInsertReturnsPrimaryKey(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();
        $insertResult = $schema->insert(
            'animal',
            ['type' => 'cat'],
        );

        self::assertIsArray(
            $insertResult,
            'Insert should return primary key values.',
        );
        self::assertArrayHasKey(
            'id',
            $insertResult,
            "Insert should return the 'id' primary key.",
        );
        self::assertEquals(
            $db->createCommand(
                <<<SQL
                SELECT [[id]] FROM {{animal}} WHERE [[type]] = 'cat'
                SQL,
            )->queryScalar(),
            $insertResult['id'],
            'Returned primary key must match the stored row.',
        );

        $db->createCommand()->delete('animal', ['id' => $insertResult['id']])->execute();
    }

    public function testInsertReturnsProvidedPrimaryKeyValues(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        if ($schema->getTableSchema('test_pk_insert', true) !== null) {
            $db->createCommand()->dropTable('test_pk_insert')->execute();
        }

        $db->createCommand()->createTable(
            'test_pk_insert',
            [
                'id1' => 'int NOT NULL',
                'id2' => 'int NOT NULL',
                'description' => 'string',
                'PRIMARY KEY ([[id1]], [[id2]])',
            ],
        )->execute();

        self::assertEquals(
            ['id1' => 7, 'id2' => 8],
            $schema->insert(
                'test_pk_insert',
                [
                    'id1' => 7,
                    'id2' => 8,
                    'description' => 'provided pk',
                ],
            ),
            'Provided primary key values must be returned.',
        );

        $db->createCommand()->dropTable('test_pk_insert')->execute();
    }

    public function testThrowInvalidCallExceptionWhenGetLastInsertIdOnInactiveConnection(): void
    {
        $db = $this->getConnection(false, false);

        $this->expectException(InvalidCallException::class);
        $this->expectExceptionMessage(
            'DB Connection is not active.',
        );

        $db->getSchema()->getLastInsertID();
    }

    public function testQuoteSpecialNames(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertSame(
            '{{%profile}}',
            $schema->quoteTableName('{{%profile}}'),
            'Table name with Yii prefix placeholder should not be quoted.',
        );
        self::assertSame(
            '',
            $schema->quoteColumnName(null),
            'Null column name should be quoted as an empty string.',
        );
    }

    public function testQuoteValueFallback(): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();

        $dsn = $db->dsn;

        try {
            $db->dsn = 'odbc:test';

            self::assertSame(
                "'it''s'",
                $schema->quoteValue("it's"),
                'Fallback quote value should escape single quotes.',
            );
        } finally {
            $db->dsn = $dsn;
        }
    }

    public function testUnquoteSimpleNames(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertSame(
            'profile',
            $schema->unquoteSimpleTableName($schema->quoteSimpleTableName('profile')),
            'Quoted table name should be unquoted.',
        );
        self::assertSame(
            'profile',
            $schema->unquoteSimpleTableName('profile'),
            'Unquoted table name should stay unchanged.',
        );
        self::assertSame(
            'id',
            $schema->unquoteSimpleColumnName($schema->quoteSimpleColumnName('id')),
            'Quoted column name should be unquoted.',
        );
        self::assertSame(
            'id',
            $schema->unquoteSimpleColumnName('id'),
            'Unquoted column name should stay unchanged.',
        );
    }

    public function testConvertException(): void
    {
        $schema = $this->getConnection()->getSchema();

        $error = array_key_first($schema->exceptionMap);

        /** @var class-string<Exception> $exceptionClass */
        $exceptionClass = $schema->exceptionMap[$error];

        self::assertInstanceOf(
            $exceptionClass,
            $schema->convertException(new \Exception($error), 'SELECT 1'),
            'Mapped DB exception should be converted to its configured exception class.',
        );
        self::assertInstanceOf(
            Exception::class,
            $schema->convertException(new \Exception('Generic database error'), 'SELECT 1'),
            'Generic exception should be converted to DB exception.',
        );

        $exception = new Exception('Existing DB exception');

        self::assertSame(
            $exception,
            $schema->convertException($exception, 'SELECT 1'),
            'Existing DB exception should not be converted again.',
        );
    }

    public function testIsReadQuery(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertTrue(
            $schema->isReadQuery(
                <<<SQL
                SELECT * FROM profile
                SQL,
            ),
            'Read statement must yield `true`.',
        );
        self::assertTrue(
            $schema->isReadQuery(
                <<<SQL
                SHOW TABLES
                SQL,
            ),
            'Metadata listing statement must yield `true`.',
        );
        self::assertTrue(
            $schema->isReadQuery(
                <<<SQL
                DESCRIBE profile
                SQL,
            ),
            'Table inspection statement must yield `true`.',
        );
        self::assertFalse(
            $schema->isReadQuery(
                <<<SQL
                UPDATE profile SET description = description
                SQL,
            ),
            'Write statement must yield `false`.',
        );
    }
}

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
use yii\base\NotSupportedException;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\mssql\ColumnSchemaBuilder;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yiiunit\base\db\BaseSchema;
use yiiunit\framework\db\mssql\providers\SchemaProvider;

use function str_starts_with;

/**
 * Unit test for {@see yii\db\mssql\Schema} schema reflection and metadata retrieval for the MSSQL driver.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaTest extends BaseSchema
{
    public $driverName = 'sqlsrv';

    /**
     * @var list<string> List of expected schemas in the database.
     */
    protected array $expectedSchemas = [
        'dbo',
    ];


    public function testGetStringFieldsSize(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();
        $columns = $schema->getTableSchema('type', false)->columns;

        foreach ($columns as $name => $column) {
            $type = $column->type;
            $size = $column->size;
            $dbType = $column->dbType;

            if (str_starts_with($name, 'char_')) {
                switch ($name) {
                    case 'char_col':
                        $expectedType = 'char';
                        $expectedSize = 100;
                        $expectedDbType = 'char(100)';

                        break;
                    case 'char_col2':
                        $expectedType = 'string';
                        $expectedSize = 100;
                        $expectedDbType = 'varchar(100)';

                        break;
                    case 'char_col3':
                        $expectedType = 'text';
                        $expectedSize = null;
                        $expectedDbType = 'text';

                        break;
                    default:
                        self::fail(
                            "Unexpected column name: {$name}.",
                        );
                }

                self::assertSame(
                    $expectedType,
                    $type,
                    "Column type for '{$name}' should be '{$expectedType}'.",
                );
                self::assertSame(
                    $expectedSize,
                    $size,
                    "Column size for '{$name}' should be '{$expectedSize}'.",
                );
                self::assertSame(
                    $expectedDbType,
                    $dbType,
                    "Column DB type for '{$name}' should be '{$expectedDbType}'.",
                );
            }
        }
    }

    /**
     * @throws NotSupportedException
     */
    #[DataProviderExternal(SchemaProvider::class, 'quoteTableName')]
    public function testQuoteTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertSame(
            $expectedName,
            $schema->quoteTableName($name),
            "Quoted table name for '{$name}' should be '{$expectedName}'.",
        );
    }

    /**
     * @throws NotSupportedException if the table does not exist or schema retrieval is not supported.
     */
    #[DataProviderExternal(SchemaProvider::class, 'getTableSchema')]
    public function testGetTableSchema(string $name, string $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();

        $tableSchema = $schema->getTableSchema($name);

        self::assertSame(
            $expectedName,
            $tableSchema->name,
            "Table schema name for '{$name}' should be '{$expectedName}'.",
        );
    }

    public function testGetPrimaryKey(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testPKTable') !== null) {
            $db->createCommand()->dropTable('testPKTable')->execute();
        }

        $db->createCommand()->createTable(
            'testPKTable',
            [
                'id' => Schema::TYPE_PK,
                'bar' => Schema::TYPE_INTEGER,
            ]
        )->execute();

        $insertResult = $db->getSchema()->insert('testPKTable', ['bar' => 1]);
        $selectResult = $db->createCommand(
            <<<SQL
            SELECT [id] FROM [testPKTable] WHERE [bar]=1
            SQL
        )->queryOne();

        self::assertSame(
            $selectResult['id'],
            $insertResult['id'],
            'Inserted ID should match selected ID.',
        );
    }

    public function testGetTableSchemaWithCatalogName(): void
    {
        $db = $this->getConnection();
        $catalogName = (string) $db->createCommand('SELECT DB_NAME()')->queryScalar();
        $schema = $db->getSchema();

        $table = $schema->getTableSchema("{$catalogName}.dbo.profile", true);

        self::assertInstanceOf(
            TableSchema::class,
            $table,
            'Table schema should be loadable with an explicit catalog name.',
        );
        self::assertSame(
            $catalogName,
            $table->catalogName,
            'Loaded table schema should keep the explicit catalog name.',
        );
        self::assertSame(
            'dbo',
            $table->schemaName,
            'Loaded table schema should keep the explicit schema name.',
        );
        self::assertSame(
            'profile',
            $table->name,
            'Loaded table name should match expected value.',
        );

        $table = $schema->getTableSchema("{$catalogName}.dbo.customer", true);

        self::assertInstanceOf(
            TableSchema::class,
            $table,
            'Table with a foreign key should be loadable with an explicit catalog name.',
        );
        self::assertSame(
            $catalogName,
            $table->catalogName,
            'Loaded table schema should keep the explicit catalog name.',
        );
        self::assertSame(
            'customer',
            $table->name,
            'Loaded table name should match expected value.',
        );
    }

    public function testGetTableSchemaReturnsNullForNonExistentTable(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertNull(
            $schema->getTableSchema('non_existent_table_xyz', true),
            'Non-existent table schema should resolve to null.',
        );
    }

    public function testDefaultSchema(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema should be an instance of ' . Schema::class . '.',
        );
        self::assertSame(
            'dbo',
            $schema->defaultSchema,
            "Default schema should be 'dbo'.",
        );
    }

    public function testQuoteColumnNameWithBrackets(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertSame(
            '[already_quoted]',
            $schema->quoteColumnName('[already_quoted]'),
            "Column name '[already_quoted]' should not be double-quoted.",
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'resolveTableName')]
    public function testResolveTableName(
        string $name,
        string|null $expectedCatalog,
        string $expectedSchema,
        string $expectedTable,
        string $expectedFullName
    ): void {
        $schema = $this->getConnection()->getSchema();

        $result = $this->invokeMethod(
            $schema,
            'resolveTableName',
            [$name],
        );

        self::assertSame(
            $expectedCatalog,
            $result->catalogName,
            'Resolved catalog name should match expected value.',
        );
        self::assertSame(
            $expectedSchema,
            $result->schemaName,
            'Resolved schema name should match expected value.',
        );
        self::assertSame(
            $expectedTable,
            $result->name,
            'Resolved table name should match expected value.',
        );
        self::assertSame(
            $expectedFullName,
            $result->fullName,
            'Resolved full name should match expected value.',
        );
    }

    public function testSavepointOperations(): void
    {
        $db = $this->getConnection();

        $db->beginTransaction();

        $db->createCommand(
            <<<SQL
            INSERT INTO [profile] ([description]) VALUES ('sp_test')
            SQL,
        )->execute();

        $db->getSchema()->createSavepoint('sp1');

        $db->createCommand(
            <<<SQL
            INSERT INTO [profile] ([description]) VALUES ('sp_test_after')
            SQL,
        )->execute();

        $db->getSchema()->rollBackSavepoint('sp1');

        $db->transaction->commit();

        $afterCount = (int) $db->createCommand(
            <<<SQL
            SELECT COUNT(*) FROM [profile] WHERE [description] = 'sp_test_after'
            SQL,
        )->queryScalar();

        self::assertSame(
            0,
            $afterCount,
            'After rolling back to savepoint, the second insert should not be present in the database.',
        );

        $beforeCount = (int) $db->createCommand(
            <<<SQL
            SELECT COUNT(*) FROM [profile] WHERE [description] = 'sp_test'
            SQL,
        )->queryScalar();

        self::assertSame(
            1,
            $beforeCount,
            'Before rolling back to savepoint, the first insert should be present in the database.',
        );

        $db->createCommand(
            <<<SQL
            DELETE FROM [profile] WHERE [description] = 'sp_test'
            SQL,
        )->execute();
    }

    public function testReleaseSavepointIsNoOp(): void
    {
        $db = $this->getConnection();

        $db->beginTransaction();

        $db->getSchema()->createSavepoint('sp1');
        $db->getSchema()->releaseSavepoint('sp1');

        self::assertTrue(
            $db->transaction->getIsActive(),
            'Transaction should still be active after releasing savepoint.',
        );

        $db->transaction->rollBack();
    }

    #[DataProviderExternal(SchemaProvider::class, 'resolveTableName')]
    public function testResolveTableNames(
        string $name,
        string|null $expectedCatalog,
        string $expectedSchema,
        string $expectedTable,
        string $expectedFullName
    ): void {
        $schema = $this->getConnection()->getSchema();
        $table = new TableSchema();

        $this->invokeMethod(
            $schema,
            'resolveTableNames',
            [
                $table,
                $name,
            ],
        );

        self::assertSame(
            $expectedCatalog,
            $table->catalogName,
            'Resolved catalog name should match expected value.',
        );
        self::assertSame(
            $expectedSchema,
            $table->schemaName,
            'Resolved schema name should match expected value.',
        );
        self::assertSame(
            $expectedTable,
            $table->name,
            'Resolved table name should match expected value.',
        );
        self::assertSame(
            $expectedFullName,
            $table->fullName,
            'Resolved full name should match expected value.',
        );
    }

    public function testGetSchemaPrimaryKeysWithExplicitSchema(): void
    {
        $schema = $this->getConnection(true, true)->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should implement ' . ConstraintFinderInterface::class . ' for primary key retrieval.',
        );

        $primaryKeys = $schema->getSchemaPrimaryKeys('dbo');

        self::assertNotEmpty(
            $primaryKeys,
            "Primary keys should be retrieved for schema 'dbo'.",
        );
        self::assertContainsOnlyInstancesOf(
            Constraint::class,
            $primaryKeys,
            'Primary keys should be instances of ' . Constraint::class . '.',
        );
    }

    public function testNullDefaultValueColumn(): void
    {
        $schema = $this->getConnection(true, true)->getSchema();

        $table = $schema->getTableSchema('null_values');

        self::assertNull(
            $table->getColumn('var3')->defaultValue,
            "Default value for 'var3' should be 'null'.",
        );
        self::assertNull(
            $table->getColumn('stringcol')->defaultValue,
            "Default value for 'stringcol' should be 'null'.",
        );
    }

    public function testExpressionAndLiteralColumnDefaultValues(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('column_default') !== null) {
            $db->createCommand()->dropTable('column_default')->execute();
        }

        $db->createCommand()->createTable(
            'column_default',
            [
                'id' => 'int',
                'created_getdate' => 'datetime DEFAULT GETDATE()',
                'created_cts' => 'datetime DEFAULT CURRENT_TIMESTAMP',
                'uuid' => 'uniqueidentifier DEFAULT NEWID()',
                'status' => 'int DEFAULT 5',
                'label' => "varchar(32) DEFAULT 'pending'",
                'note' => "nvarchar(32) DEFAULT N'it''s'",
            ],
        )->execute();

        $table = $db->getSchema()->getTableSchema('column_default', true);

        self::assertNull(
            $table->getColumn('created_getdate')->defaultValue,
            "Default must resolve to 'null'.",
        );
        self::assertNull(
            $table->getColumn('created_cts')->defaultValue,
            "Default must resolve to 'null'.",
        );
        self::assertNull(
            $table->getColumn('uuid')->defaultValue,
            "Default must resolve to 'null'.",
        );
        self::assertSame(
            5,
            $table->getColumn('status')->defaultValue,
            "Numeric default must unwrap to 'int'.",
        );
        self::assertSame(
            'pending',
            $table->getColumn('label')->defaultValue,
            'String default must unwrap.',
        );
        self::assertSame(
            "it's",
            $table->getColumn('note')->defaultValue,
            'Escaped unicode default must unwrap.',
        );

        $db->createCommand()->dropTable('column_default')->execute();
    }

    public function testInsertWithCompositePrimaryKey(): void
    {
        $db = $this->getConnection();

        $result = $db->getSchema()->insert(
            'employee',
            [
                'id' => 100,
                'department_id' => 1,
                'first_name' => 'Test',
                'last_name' => 'User',
            ],
        );

        self::assertSame(
            '100',
            $result['id'],
            'Inserted ID should match expected value.',
        );
        self::assertSame(
            '1',
            $result['department_id'],
            'Inserted department ID should match expected value.',
        );

        $db->createCommand(
            <<<SQL
            DELETE FROM [employee] WHERE [id] = 100 AND [department_id] = 1
            SQL
        )->execute();
    }

    public function testGetViewNamesWithDefaultSchema(): void
    {
        $schema = $this->getConnection()->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema should be an instance of ' . Schema::class . '.',
        );

        $viewNames = $schema->getViewNames();

        self::assertContains(
            'animal_view',
            $viewNames,
            "View 'animal_view' should be present in the list of view names.",
        );
        self::assertContains(
            'animal_view',
            $schema->getViewNames('dbo'),
            "View 'animal_view' should be present when listing views with an explicit schema.",
        );
        self::assertContains(
            'animal_view',
            $schema->getViewNames('dbo', true),
            "View 'animal_view' should be present when refreshing views with an explicit schema.",
        );
    }


    public function testCreateColumnSchemaBuilder(): void
    {
        $schema = $this->getConnection()->getSchema();

        $builder = $schema->createColumnSchemaBuilder('string', 255);

        self::assertInstanceOf(
            ColumnSchemaBuilder::class,
            $builder,
            'Column schema builder should be an instance of ' . ColumnSchemaBuilder::class . '.',
        );
    }
}

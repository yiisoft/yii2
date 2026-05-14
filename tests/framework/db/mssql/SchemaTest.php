<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\base\NotSupportedException;
use yii\db\Constraint;
use yii\db\ConstraintFinderInterface;
use yii\db\DefaultValueConstraint;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yiiunit\framework\db\AnyValue;
use yiiunit\base\db\BaseSchema;

/**
 * @group db
 * @group mssql
 */
class SchemaTest extends BaseSchema
{
    public $driverName = 'sqlsrv';

    protected $expectedSchemas = [
        'dbo',
    ];

    public static function constraintsProvider(): array
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2][0]->expression = '([C_check]<>\'\')';
        $result['1: default'][2] = [];
        $result['1: default'][2][] = new DefaultValueConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_default'],
            'value' => '((0))',
        ]);

        $result['2: default'][2] = [];

        $result['3: foreign key'][2][0]->foreignSchemaName = 'dbo';
        $result['3: index'][2] = [];
        $result['3: default'][2] = [];

        $result['4: default'][2] = [];
        return $result;
    }

    public function testGetStringFieldsSize(): void
    {
        $db = $this->getConnection();
        $schema = $db->schema;

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
                        $this->fail("Unexpected column name: {$name}");
                }

                $this->assertEquals($expectedType, $type);
                $this->assertEquals($expectedSize, $size);
                $this->assertEquals($expectedDbType, $dbType);
            }
        }
    }

    /**
     * @dataProvider quoteTableNameDataProvider
     *
     * @param string $name Table name.
     * @param string $expectedName Expected result.
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
            ['test', '[test]'],
            ['test.test', '[test].[test]'],
            ['test.test.test', '[test].[test].[test]'],
            ['[test]', '[test]'],
            ['[test].[test]', '[test].[test]'],
            ['test.[test.test]', '[test].[test.test]'],
            ['test.test.[test.test]', '[test].[test].[test.test]'],
            ['[test].[test.test]', '[test].[test.test]'],
        ];
    }

    /**
     * @dataProvider getTableSchemaDataProvider
     *
     * @param string $name Table name.
     * @param string $expectedName Expected result.
     *
     * @throws NotSupportedException
     */
    public function testGetTableSchema(string $name, string $expectedName): void
    {
        $schema = $this->getConnection()->getSchema();
        $tableSchema = $schema->getTableSchema($name);
        $this->assertEquals($expectedName, $tableSchema->name);
    }

    public static function getTableSchemaDataProvider(): array
    {
        return [
            ['[dbo].[profile]', 'profile'],
            ['dbo.profile', 'profile'],
            ['profile', 'profile'],
            ['dbo.[table.with.special.characters]', 'table.with.special.characters'],
        ];
    }

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        unset($columns['ts_default']);
        unset($columns['bit_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'int';
        $columns['int_col2']['dbType'] = 'int';
        $columns['tinyint_col']['dbType'] = 'tinyint';
        $columns['smallint_col']['dbType'] = 'smallint';
        $columns['float_col']['dbType'] = 'decimal';
        $columns['float_col']['phpType'] = 'string';
        $columns['float_col']['type'] = 'decimal';
        $columns['float_col']['scale'] = null;
        $columns['float_col2']['dbType'] = 'float';
        $columns['float_col2']['phpType'] = 'double';
        $columns['float_col2']['type'] = 'float';
        $columns['float_col2']['scale'] = null;
        $columns['blob_col']['dbType'] = 'varbinary';
        $columns['numeric_col']['dbType'] = 'decimal';
        $columns['numeric_col']['scale'] = null;
        $columns['time']['dbType'] = 'datetime';
        $columns['time']['type'] = 'datetime';
        $columns['bool_col']['dbType'] = 'tinyint';
        $columns['bool_col2']['dbType'] = 'tinyint';

        array_walk($columns, static function (&$item): void {
            $item['enumValues'] = [];
        });

        array_walk($columns, static function (&$item, $name): void {
            if (!in_array($name, ['char_col', 'char_col2', 'char_col3'])) {
                $item['size'] = null;
            }
        });

        array_walk($columns, static function (&$item, $name): void {
            if (!in_array($name, ['char_col', 'char_col2', 'char_col3'])) {
                $item['precision'] = null;
            }
        });

        return $columns;
    }

    public function testGetPrimaryKey(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testPKTable') !== null) {
            $db->createCommand()->dropTable('testPKTable')->execute();
        }

        $db->createCommand()->createTable(
            'testPKTable',
            ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER]
        )->execute();

        $insertResult = $db->getSchema()->insert('testPKTable', ['bar' => 1]);
        $selectResult = $db->createCommand('select [id] from [testPKTable] where [bar]=1')->queryOne();

        $this->assertEquals($selectResult['id'], $insertResult['id']);
    }

    public function testQuoteColumnNameWithBrackets(): void
    {
        $schema = $this->getConnection()->getSchema();
        $this->assertSame('[already_quoted]', $schema->quoteColumnName('[already_quoted]'));
    }

    /**
     * @dataProvider resolveTableNameProvider
     */
    public function testResolveTableName(
        string $name,
        ?string $expectedCatalog,
        string $expectedSchema,
        string $expectedTable,
        string $expectedFullName
    ): void {
        $schema = $this->getConnection()->getSchema();
        $method = new \ReflectionMethod($schema, 'resolveTableName');
        $result = $method->invoke($schema, $name);
        $this->assertSame($expectedCatalog, $result->catalogName);
        $this->assertSame($expectedSchema, $result->schemaName);
        $this->assertSame($expectedTable, $result->name);
        $this->assertSame($expectedFullName, $result->fullName);
    }

    public static function resolveTableNameProvider(): array
    {
        return [
            'single part' => [
                'customer',
                null,
                'dbo',
                'customer',
                'customer',
            ],
            'two parts' => [
                'sales.customer',
                null,
                'sales',
                'customer',
                'sales.customer',
            ],
            'two parts default schema' => [
                'dbo.customer',
                null,
                'dbo',
                'customer',
                'customer',
            ],
            'three parts' => [
                'catalog1.sales.customer',
                'catalog1',
                'sales',
                'customer',
                'catalog1.sales.customer',
            ],
            'four parts' => [
                '[server1].catalog1.sales.customer',
                'catalog1',
                'sales',
                'customer',
                'catalog1.sales.customer',
            ],
        ];
    }

    public function testSavepointOperations(): void
    {
        $db = $this->getConnection(true, true);
        $db->beginTransaction();
        $db->createCommand("INSERT INTO [profile] ([description]) VALUES ('sp_test')")->execute();
        $db->getSchema()->createSavepoint('sp1');
        $db->createCommand("INSERT INTO [profile] ([description]) VALUES ('sp_test_after')")->execute();
        $db->getSchema()->rollBackSavepoint('sp1');
        $db->transaction->commit();

        $afterCount = (int)$db->createCommand("SELECT COUNT(*) FROM [profile] WHERE [description] = 'sp_test_after'")->queryScalar();
        $this->assertSame(0, $afterCount);
        $beforeCount = (int)$db->createCommand("SELECT COUNT(*) FROM [profile] WHERE [description] = 'sp_test'")->queryScalar();
        $this->assertSame(1, $beforeCount);

        $db->createCommand("DELETE FROM [profile] WHERE [description] = 'sp_test'")->execute();
    }

    public function testReleaseSavepointIsNoOp(): void
    {
        $db = $this->getConnection(true, true);
        $db->beginTransaction();
        $db->getSchema()->createSavepoint('sp1');
        $db->getSchema()->releaseSavepoint('sp1');
        $this->assertTrue($db->transaction->getIsActive());
        $db->transaction->rollBack();
    }

    /**
     * @dataProvider resolveTableNameProvider
     */
    public function testResolveTableNames(
        string $name,
        ?string $expectedCatalog,
        string $expectedSchema,
        string $expectedTable,
        string $expectedFullName
    ): void {
        $schema = $this->getConnection()->getSchema();
        $table = new TableSchema();
        $method = new \ReflectionMethod($schema, 'resolveTableNames');
        $method->invoke($schema, $table, $name);
        $this->assertSame($expectedCatalog, $table->catalogName);
        $this->assertSame($expectedSchema, $table->schemaName);
        $this->assertSame($expectedTable, $table->name);
        $this->assertSame($expectedFullName, $table->fullName);
    }

    public function testGetSchemaPrimaryKeysWithExplicitSchema(): void
    {
        $schema = $this->getConnection(true, true)->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        $primaryKeys = $schema->getSchemaPrimaryKeys('dbo');
        $this->assertNotEmpty($primaryKeys);
        $this->assertContainsOnlyInstancesOf(Constraint::class, $primaryKeys);
    }

    public function testNullDefaultValueColumn(): void
    {
        $schema = $this->getConnection(true, true)->getSchema();
        $table = $schema->getTableSchema('null_values');
        $this->assertNull($table->getColumn('var3')->defaultValue);
        $this->assertNull($table->getColumn('stringcol')->defaultValue);
    }

    public function testInsertWithCompositePrimaryKey(): void
    {
        $db = $this->getConnection(true, true);
        $result = $db->getSchema()->insert('employee', [
            'id' => 100,
            'department_id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);
        $this->assertSame('100', $result['id']);
        $this->assertSame('1', $result['department_id']);

        $db->createCommand('DELETE FROM [employee] WHERE [id] = 100 AND [department_id] = 1')->execute();
    }

    public function testGetViewNamesWithDefaultSchema(): void
    {
        $schema = $this->getConnection(true, true)->getSchema();
        $this->assertInstanceOf(Schema::class, $schema);

        $viewNames = $schema->getViewNames();
        $this->assertContains('animal_view', $viewNames);
    }

    public function testFindUniqueIndexes(): void
    {
        $db = $this->getConnection(true, true);
        $table = $db->getSchema()->getTableSchema('T_upsert');
        $indexes = $db->getSchema()->findUniqueIndexes($table);
        $this->assertNotEmpty($indexes);
    }

    public function testCreateColumnSchemaBuilder(): void
    {
        $schema = $this->getConnection()->getSchema();
        $builder = $schema->createColumnSchemaBuilder('string', 255);
        $this->assertInstanceOf('yii\db\mssql\ColumnSchemaBuilder', $builder);
    }
}

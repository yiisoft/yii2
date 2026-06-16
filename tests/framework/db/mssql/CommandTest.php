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
use yii\db\IntegrityException;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yii\db\Query;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\mssql\providers\CommandProvider;

/**
 * Unit tests for {@see \yii\db\mssql\Command} functionality for the MSSQL driver.
 *
 * {@see CommandProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('command')]
final class CommandTest extends BaseCommand
{
    protected $driverName = 'sqlsrv';

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT [id], [t].[name] FROM [customer] t', $command->sql);
    }

    #[DataProviderExternal(CommandProvider::class, 'renameTable')]
    public function testRenameTableWithQuotedNames(
        string $fromTableName,
        string $toTableName,
        string $fromRawTableName,
        string $toRawTableName,
    ): void {
        $db = $this->getConnection();

        foreach ([$toRawTableName, $fromRawTableName] as $tableName) {
            if ($db->getSchema()->getTableSchema($tableName, true) !== null) {
                $db->createCommand()->dropTable($tableName)->execute();
            }
        }

        $db->createCommand()->createTable(
            $fromTableName,
            ['id' => 'integer'],
        )->execute();

        self::assertNotNull(
            $db->getSchema()->getTableSchema($fromRawTableName, true),
            'Table must be created with the expected raw name.',
        );
        self::assertNull(
            $db->getSchema()->getTableSchema($toRawTableName, true),
            'Table must not exist with the expected raw name.',
        );

        $db->createCommand()->renameTable($fromTableName, $toTableName)->execute();

        self::assertNull(
            $db->getSchema()->getTableSchema($fromRawTableName, true),
            'Table must not exist with the expected raw name after renaming.',
        );
        self::assertNotNull(
            $db->getSchema()->getTableSchema($toRawTableName, true),
            'Table must exist with the expected raw name after renaming.',
        );

        foreach ([$toRawTableName, $fromRawTableName] as $tableName) {
            if ($db->getSchema()->getTableSchema($tableName, true) !== null) {
                $db->createCommand()->dropTable($tableName)->execute();
            }
        }
    }

    #[DataProviderExternal(CommandProvider::class, 'renameColumn')]
    public function testRenameColumnWithQuotedNames(
        string $tableName,
        string $oldColumnName,
        string $newColumnName,
        string $rawTableName,
        string $oldRawColumnName,
        string $newRawColumnName,
    ): void {
        $db = $this->getConnection();
        $schema = $db->getSchema();

        if ($schema->getTableSchema($rawTableName, true) !== null) {
            $db->createCommand()->dropTable($rawTableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [$oldColumnName => 'integer'],
        )->execute();

        $tableSchema = $schema->getTableSchema($rawTableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table must be created with the expected raw name.',
        );
        self::assertNotNull(
            $tableSchema->getColumn($oldRawColumnName),
            'Old column must exist before renaming.',
        );
        self::assertNull(
            $tableSchema->getColumn($newRawColumnName),
            'New column must not exist before renaming.',
        );

        $db->createCommand()->renameColumn($tableName, $oldColumnName, $newColumnName)->execute();

        $tableSchema = $schema->getTableSchema($rawTableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table must exist with the expected raw name after renaming.',
        );
        self::assertNull(
            $tableSchema->getColumn($oldRawColumnName),
            'Old column must not exist after renaming.',
        );
        self::assertNotNull(
            $tableSchema->getColumn($newRawColumnName),
            'New column must exist after renaming.',
        );

        if ($schema->getTableSchema($rawTableName, true) !== null) {
            $db->createCommand()->dropTable($rawTableName)->execute();
        }
    }

    public function testBindParamValue(): void
    {
        $db = $this->getConnection();

        // bindParam
        $sql = 'INSERT INTO customer(email, name, address) VALUES (:email, :name, :address)';
        $command = $db->createCommand($sql);
        $email = 'user4@example.com';
        $name = 'user4';
        $address = 'address4';
        $command->bindParam(':email', $email);
        $command->bindParam(':name', $name);
        $command->bindParam(':address', $address);
        $command->execute();

        $sql = 'SELECT name FROM customer WHERE email=:email';
        $command = $db->createCommand($sql);
        $command->bindParam(':email', $email);
        $this->assertEquals($name, $command->queryScalar());

        $sql = 'INSERT INTO type (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, CONVERT([varbinary], :blob_col), :numeric_col, :bool_col)';
        $command = $db->createCommand($sql);
        $intCol = 123;
        $charCol = 'abc';
        $floatCol = 1.230;
        $blobCol = "\x10\x11\x12";
        $numericCol = '1.23';
        $boolCol = false;
        $command->bindParam(':int_col', $intCol);
        $command->bindParam(':char_col', $charCol);
        $command->bindParam(':float_col', $floatCol);
        $command->bindParam(':blob_col', $blobCol);
        $command->bindParam(':numeric_col', $numericCol);
        $command->bindParam(':bool_col', $boolCol);
        $this->assertEquals(1, $command->execute());

        $sql = 'SELECT int_col, char_col, float_col, CONVERT([nvarchar], blob_col) AS blob_col, numeric_col FROM type';
        $row = $db->createCommand($sql)->queryOne();

        $this->assertEquals($intCol, $row['int_col']);
        $this->assertEquals($charCol, trim((string) $row['char_col']));
        $this->assertEquals($floatCol, (float) $row['float_col']);
        $this->assertEquals($blobCol, $row['blob_col']);
        $this->assertEquals($numericCol, $row['numeric_col']);

        // bindValue
        $sql = 'INSERT INTO customer(email, name, address) VALUES (:email, \'user5\', \'address5\')';
        $command = $db->createCommand($sql);
        $command->bindValue(':email', 'user5@example.com');
        $command->execute();

        $sql = 'SELECT email FROM customer WHERE name=:name';
        $command = $db->createCommand($sql);
        $command->bindValue(':name', 'user5');
        $this->assertEquals('user5@example.com', $command->queryScalar());
    }

    public static function paramsNonWhereProvider(): array
    {
        return[
            ['SELECT SUBSTRING(name, :len, 6) AS name FROM {{customer}} WHERE [[email]] = :email GROUP BY name'],
            ['SELECT SUBSTRING(name, :len, 6) as name FROM {{customer}} WHERE [[email]] = :email ORDER BY name'],
            ['SELECT SUBSTRING(name, :len, 6) FROM {{customer}} WHERE [[email]] = :email'],
        ];
    }

    public function testAddDropDefaultValue(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'test_def';
        $name = 'test_def_constraint';

        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            ['int1' => 'integer'],
        )->execute();

        $defaultValues = $schema->getTableDefaultValues($tableName, true);

        self::assertEmpty(
            $defaultValues,
            'Default constraints must be empty before adding a default value.',
        );

        $db->createCommand()->addDefaultValue(
            $name,
            $tableName,
            'int1',
            41,
        )->execute();

        $defaultValues = $schema->getTableDefaultValues($tableName, true);

        self::assertMatchesRegularExpression(
            '/^.*41.*$/',
            $defaultValues[0]->value,
            'Default constraint definition must contain the integer literal.',
        );

        $db->createCommand()->dropDefaultValue(
            $name,
            $tableName,
        )->execute();

        $defaultValues = $schema->getTableDefaultValues($tableName, true);

        self::assertEmpty(
            $defaultValues,
            'Default constraints must be empty after dropping the default value.',
        );

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
    }

    #[DataProviderExternal(CommandProvider::class, 'addDefaultValue')]
    public function testAddDropDefaultValueWithMssqlLiterals(
        string $tableName,
        string $name,
        string $column,
        string $columnType,
        mixed $value,
        string $expectedValuePattern,
    ): void {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [$column => $columnType],
        )->execute();

        self::assertEmpty(
            $schema->getTableDefaultValues($tableName, true),
            'Default constraints must be empty before adding a default value.',
        );

        $db->createCommand()->addDefaultValue(
            $name,
            $tableName,
            $column,
            $value,
        )->execute();

        $defaultValues = $schema->getTableDefaultValues($tableName, true);

        self::assertCount(
            1,
            $defaultValues,
            'Exactly one default constraint must be created.',
        );
        self::assertSame(
            $name,
            $defaultValues[0]->name,
            'Default constraint name must match the requested name.',
        );
        self::assertSame(
            [$column],
            $defaultValues[0]->columnNames,
            'Default constraint must be bound to the requested column.',
        );
        self::assertMatchesRegularExpression(
            $expectedValuePattern,
            $defaultValues[0]->value,
            'Default constraint definition must contain the expected MSSQL literal.',
        );

        $db->createCommand()->dropDefaultValue(
            $name,
            $tableName,
        )->execute();

        self::assertEmpty(
            $schema->getTableDefaultValues($tableName, true),
            'Default constraints must be empty after dropping the default value.',
        );

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
    }

    public function testAlterColumn(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            'varchar(255)',
        )->execute();

        $tableSchema = $db->getTableSchema('foo1', true);

        self::assertSame(
            'varchar(255)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );
        self::assertTrue(
            $tableSchema->getColumn('bar')->allowNull,
            'Column must stay nullable.',
        );

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)->notNull(),
        )->execute();

        $tableSchema = $db->getTableSchema('foo1', true);

        self::assertSame(
            'nvarchar(128)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );
        self::assertFalse(
            $tableSchema->getColumn('bar')->allowNull,
            'Column must be NOT NULL after the change.',
        );
    }

    public function testAlterColumnWithCheckConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)->null()->check('LEN(bar) > 5'),
        )->execute();

        $tableSchema = $db->getTableSchema('foo1', true);

        self::assertSame(
            'nvarchar(128)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );
        self::assertTrue(
            $tableSchema->getColumn('bar')->allowNull,
            'Column must stay nullable.',
        );
        self::assertSame(
            1,
            $db->createCommand(
                <<<SQL
                INSERT INTO [foo1]([bar]) VALUES('abcdef')
                SQL
            )->execute(),
            'Value satisfying the check must be accepted.',
        );
    }

    public function testThrowIntegrityExceptionWhenInsertViolatesAlterColumnCheckConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)->check('LEN(bar) > 5'),
        )->execute();

        $this->expectException(IntegrityException::class);

        $db->createCommand(
            <<<SQL
            INSERT INTO [foo1]([bar]) VALUES('abcde')
            SQL,
        )->execute();
    }

    public function testThrowIntegrityExceptionWhenInsertViolatesAlterColumnUniqueConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)->unique(),
        )->execute();

        self::assertSame(
            1,
            $db->createCommand(
                <<<SQL
                INSERT INTO [foo1]([bar]) VALUES('abcdef')
                SQL
            )->execute(),
            'First value must be accepted.',
        );

        $this->expectException(IntegrityException::class);

        $db->createCommand(
            <<<SQL
            INSERT INTO [foo1]([bar]) VALUES('abcdef')
            SQL
        )->execute();
    }

    public function testAlterColumnWithSchemaQualifiedTable(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'dbo.foo1',
            'bar',
            'varchar(255)',
        )->execute();

        $tableSchema = $db->getTableSchema('dbo.foo1', true);

        self::assertSame(
            'varchar(255)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );
        self::assertTrue(
            $tableSchema->getColumn('bar')->allowNull,
            'Column must stay nullable.',
        );
    }

    public function testAlterColumnWithCatalogQualifiedTable(): void
    {
        $db = $this->getConnection();

        $catalogName = (string) $db->createCommand(
            <<<SQL
            SELECT DB_NAME()
            SQL
        )->queryScalar();

        $table = "{$catalogName}.dbo.foo1";

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)->defaultValue('initial'),
        )->execute();

        $tableSchema = $db->getTableSchema($table, true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Schema must load for the catalog-qualified name.',
        );
        self::assertSame(
            $catalogName,
            $tableSchema->catalogName,
            'Catalog name must be preserved.',
        );
        self::assertSame(
            'nvarchar(128)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INTEGER)->defaultValue(0),
        )->execute();

        $tableSchema = $db->getTableSchema($table, true);

        self::assertSame(
            'int',
            $tableSchema->getColumn('bar')->dbType,
            'Type change must succeed with a default bound.',
        );

        $schema = $db->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema must be available.',
        );
        self::assertCount(
            1,
            $schema->getTableDefaultValues($table, true),
            'Old default constraint must be replaced, not duplicated.',
        );
    }

    public function testAlterColumnReplacesDefaultValue(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)->defaultValue('initial'),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_INTEGER)->defaultValue(0),
        )->execute();

        $tableSchema = $db->getTableSchema('foo1', true);

        self::assertSame(
            'int',
            $tableSchema->getColumn('bar')->dbType,
            'Type change must succeed with a default bound.',
        );

        $schema = $db->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema must be available.',
        );
        self::assertCount(
            1,
            $schema->getTableDefaultValues('foo1', true),
            'Old default constraint must be replaced, not duplicated.',
        );
    }

    public function testAlterColumnReplacesCheckConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)->check('LEN(bar) > 5'),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)->check('LEN(bar) > 3'),
        )->execute();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema must be available.',
        );
        self::assertCount(
            1,
            $schema->getTableChecks('foo1', true),
            'Old check constraint must be replaced, not duplicated.',
        );
        self::assertSame(
            1,
            $db->createCommand(
                <<<SQL
                INSERT INTO [foo1]([bar]) VALUES('abcd')
                SQL
            )->execute(),
            'New check must be in effect instead of the old one.',
        );
    }

    public function testAlterColumnReplacesUniqueConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)->unique(),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_STRING, 32)->unique(),
        )->execute();

        $tableSchema = $db->getTableSchema('foo1', true);

        self::assertSame(
            'nvarchar(32)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the new definition.',
        );

        $schema = $db->getSchema();

        self::assertInstanceOf(
            Schema::class,
            $schema,
            'Schema must be available.',
        );
        self::assertCount(
            1,
            $schema->getTableUniques('foo1', true),
            'Old unique constraint must be replaced, not duplicated.',
        );
    }

    public static function batchInsertSqlProvider(): array
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO [type] ([int_col], [float_col], [char_col]) VALUES (NULL, NULL, \'Kyiv {{city}}, Ukraine\')';
        $data['wrongBehavior']['expected'] = 'INSERT INTO [type] ([type].[int_col], [float_col], [char_col]) VALUES (\'\', \'\', \'Kyiv {{city}}, Ukraine\')';
        $data['batchInsert binds params from expression']['expected'] = 'INSERT INTO [type] ([int_col]) VALUES (:qp1)';
        unset($data['batchIsert empty rows represented by ArrayObject']);

        return $data;
    }

    public function testUpsertVarbinary(): void
    {
        $db = $this->getConnection();

        $qb = $db->getQueryBuilder();
        $testData = json_encode(['test' => 'string', 'test2' => 'integer'], JSON_THROW_ON_ERROR);

        $params = [];

        $sql = $qb->upsert('T_upsert_varbinary', ['id' => 1, 'blob_col' => $testData], ['blob_col' => $testData], $params);
        $result = $db->createCommand($sql, $params)->execute();

        $this->assertSame(1, $result);

        $query = (new Query())->select(['blob_col'])->from('T_upsert_varbinary')->where(['id' => 1]);
        $resultData = $query->createCommand($db)->queryOne();

        $this->assertSame($testData, $resultData['blob_col']);
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use Closure;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\mssql\Schema;
use yii\db\mssql\TableSchema;
use yii\db\Query;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\mssql\providers\CommandProvider;
use yiiunit\support\DbHelper;

use function array_key_exists;
use function array_keys;
use function json_encode;

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

    public function testSelectExistsReturnsScalarExistenceFlag(): void
    {
        $db = $this->getConnection();

        $qb = $db->getQueryBuilder();

        self::assertSame(
            1,
            (int) $db->createCommand(
                $qb->selectExists(
                    <<<SQL
                    SELECT 1 FROM [customer] WHERE [status] = 2
                    SQL,
                ),
            )->queryScalar(),
            "Matching row must yield '1'.",
        );
        self::assertSame(
            0,
            (int) $db->createCommand(
                $qb->selectExists(
                    <<<SQL
                    SELECT 1 FROM [customer] WHERE [status] = 3
                    SQL,
                ),
            )->queryScalar(),
            "No matching row must yield '0'.",
        );
    }

    #[DataProviderExternal(CommandProvider::class, 'zeroLimitQueries')]
    public function testZeroLimitQueriesReturnNoRows(Closure $query): void
    {
        $db = $this->getConnection();

        $result = $query()
            ->createCommand($db)
            ->queryAll();

        self::assertSame(
            [],
            $result,
            "Limit '0' query must return no rows.",
        );
    }

    #[DataProviderExternal(CommandProvider::class, 'addCommentOnColumn')]
    public function testAddUpdateDropCommentOnColumn(string $tableName, string $commentTarget, string $columnName): void
    {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => 'integer',
                $columnName => 'string',
            ],
        )->execute();

        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'Initial column comment.',
        )->execute();

        self::assertSame(
            'Initial column comment.',
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be created.',
        );

        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'Updated column comment.',
        )->execute();

        self::assertSame(
            'Updated column comment.',
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be updated.',
        );

        $db->createCommand()->dropCommentFromColumn(
            $commentTarget,
            $columnName,
        )->execute();

        self::assertEmpty(
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be removed.',
        );

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
    }

    #[DataProviderExternal(CommandProvider::class, 'checkIntegrity')]
    public function testThrowIntegrityExceptionWhenExistingRowsViolateForeignKey(string $tableName): void
    {
        $db = $this->getConnection();

        $db->createCommand()->checkIntegrity(
            false,
            '',
            $tableName,
        )->execute();
        $db->createCommand(
            <<<SQL
            INSERT INTO [T_constraints_3] ([C_id], [C_fk_id_1], [C_fk_id_2]) VALUES (1, 2, 3)
            SQL,
        )->execute();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessageMatches(
            '/FOREIGN KEY constraint/i',
        );

        $db->createCommand()->checkIntegrity(
            true,
            '',
            $tableName,
        )->execute();
    }

    #[DataProviderExternal(CommandProvider::class, 'checkIntegrity')]
    public function testCheckIntegrityEnablesTrustedForeignKeyWhenExistingRowsAreValid(string $tableName): void
    {
        $db = $this->getConnection();

        $db->createCommand(
            <<<SQL
            INSERT INTO [T_constraints_2] ([C_id_1], [C_id_2]) VALUES (2, 3)
            SQL,
        )->execute();
        $db->createCommand()->checkIntegrity(
            false,
            '',
            $tableName,
        )->execute();
        $db->createCommand(
            <<<SQL
            INSERT INTO [T_constraints_3] ([C_id], [C_fk_id_1], [C_fk_id_2]) VALUES (1, 2, 3)
            SQL,
        )->execute();
        $db->createCommand()->checkIntegrity(
            true,
            '',
            $tableName,
        )->execute();
        $constraintState = $db->createCommand(
            <<<SQL
            SELECT [is_disabled], [is_not_trusted]
            FROM [sys].[foreign_keys]
            WHERE [name] = 'CN_constraints_3'
            SQL,
        )->queryOne();

        self::assertIsArray(
            $constraintState,
            'Constraint state row must be found.',
        );
        self::assertSame(
            0,
            (int) $constraintState['is_disabled'],
            'Foreign key must be enabled.',
        );
        self::assertSame(
            0,
            (int) $constraintState['is_not_trusted'],
            'Foreign key must be trusted.',
        );
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

    public function testDropDefaultValueDoesNotDropNonDefaultConstraints(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'test_def_non_default';
        $defaultName = 'test_def_non_default_constraint';
        $checkName = 'test_def_non_default_check';

        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            ['int1' => 'integer'],
        )->execute();
        $db->createCommand()->addDefaultValue(
            $defaultName,
            $tableName,
            'int1',
            41,
        )->execute();
        $db->createCommand()->addCheck(
            $checkName,
            $tableName,
            '[[int1]] >= 0',
        )->execute();

        $db->createCommand()->dropDefaultValue(
            $defaultName,
            $tableName,
        )->execute();

        self::assertEmpty(
            $schema->getTableDefaultValues($tableName, true),
            'The default constraint must be removable by its own name.',
        );
        self::assertCount(
            1,
            $schema->getTableChecks($tableName, true),
            'The CHECK constraint must remain after dropping only the default constraint.',
        );

        if ($schema->getTableSchema($tableName, true) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
    }

    public function testThrowExceptionWhenDropDefaultValueUsesNonDefaultConstraint(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'test_def_non_default';
        $defaultName = 'test_def_non_default_constraint';
        $checkName = 'test_def_non_default_check';

        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            ['int1' => 'integer'],
        )->execute();
        $db->createCommand()->addDefaultValue(
            $defaultName,
            $tableName,
            'int1',
            41,
        )->execute();
        $db->createCommand()->addCheck(
            $checkName,
            $tableName,
            '[[int1]] >= 0',
        )->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches(
            '/Default constraint not found on table\./',
        );

        $db->createCommand()->dropDefaultValue(
            $checkName,
            $tableName,
        )->execute();
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

    #[DataProviderExternal(CommandProvider::class, 'resetSequence')]
    public function testResetSequence(
        string $tableName,
        string $rawTableName,
        int $rowsBeforeReset,
        bool $deleteBeforeReset,
        int|null $value,
        int $expectedId,
    ): void {
        $db = $this->getConnection(false);
        $schema = $db->getSchema();

        if ($schema->getTableSchema($rawTableName, true) !== null) {
            $db->createCommand()->dropTable($rawTableName)->execute();
        }

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => Schema::TYPE_PK,
                'description' => Schema::TYPE_STRING,
            ],
        )->execute();

        for ($i = 1; $i <= $rowsBeforeReset; ++$i) {
            $db->createCommand()->insert(
                $tableName,
                ['description' => "before reset {$i}"],
            )->execute();
        }

        if ($deleteBeforeReset) {
            $db->createCommand()->delete($tableName)->execute();
        }

        $command = $db->createCommand();

        if ($value === null) {
            $command->resetSequence(
                $tableName,
            )->execute();
        } else {
            $command->resetSequence(
                $tableName,
                $value,
            )->execute();
        }

        $db->createCommand()->insert(
            $tableName,
            ['description' => 'after reset'],
        )->execute();

        self::assertSame(
            $expectedId,
            (int) $db->createCommand(
                <<<SQL
                SELECT MAX([id]) FROM {$db->quoteTableName($rawTableName)}
                SQL,
            )->queryScalar(),
            'The generated identity value must match the expected next value.',
        );

        $db->createCommand()->dropTable($rawTableName)->execute();
    }

    #[DataProviderExternal(CommandProvider::class, 'alterColumn')]
    public function testAlterColumn(string $startColumn, array $setupSql, string|Closure $type, array $expected): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['alter_column']);

        foreach ($setupSql as $sql) {
            $db->createCommand($sql)->execute();
        }

        $command = $db->createCommand();

        $command->createTable(
            'alter_column',
            ['id' => 'pk', 'bar' => $startColumn],
        )->execute();

        if ($type instanceof Closure) {
            $type = $type($db);
        }

        $result = $command->alterColumn(
            'alter_column',
            'bar',
            $type,
        )->execute();

        self::assertSame(
            0,
            $result,
            'DDL must report zero affected rows.',
        );

        if (($expected['repeatable'] ?? false) === true) {
            $command->alterColumn(
                'alter_column',
                'bar',
                $type,
            )->execute();
        }

        if (isset($expected['type'])) {
            DbHelper::assertColumnType(
                $db,
                'alter_column',
                'bar',
                $expected['type'],
            );
        }

        if (isset($expected['dbType'])) {
            DbHelper::assertColumnDbType(
                $db,
                'alter_column',
                'bar',
                $expected['dbType'],
            );
        }

        if (isset($expected['allowNull'])) {
            DbHelper::assertColumnAllowNull(
                $db,
                'alter_column',
                'bar',
                $expected['allowNull'],
            );
        }

        if (isset($expected['checkContains'])) {
            DbHelper::assertCheckConstraintContains(
                $db,
                'alter_column',
                $expected['checkContains'],
            );
        }

        if (isset($expected['uniqueColumns'])) {
            DbHelper::assertSingleUniqueConstraintCovers(
                $db,
                'alter_column',
                $expected['uniqueColumns'],
            );
        }

        if (isset($expected['defaultExpressionContains'])) {
            DbHelper::assertDefaultConstraintContains(
                $db,
                'alter_column',
                $expected['defaultExpressionContains'],
            );
        }

        if (array_key_exists('defaultValue', $expected)) {
            DbHelper::assertColumnDefaultValue(
                $db,
                'alter_column',
                'bar',
                $expected['defaultValue'],
            );
        }

        if (array_key_exists('checkCount', $expected)) {
            DbHelper::assertCheckConstraintCount(
                $db,
                'alter_column',
                $expected['checkCount'],
            );
        }

        if (array_key_exists('defaultCount', $expected)) {
            DbHelper::assertDefaultConstraintCount(
                $db,
                'alter_column',
                $expected['defaultCount'],
            );
        }

        if (array_key_exists('uniqueCount', $expected)) {
            DbHelper::assertUniqueConstraintCount(
                $db,
                'alter_column',
                $expected['uniqueCount'],
            );
        }

        DbHelper::dropTablesIfExist($db, ['alter_column']);
    }

    #[DataProviderExternal(CommandProvider::class, 'alterColumnFailing')]
    public function testThrowExceptionForAlterColumnTypeStringWithConstraintClause(
        string $type,
        string $exceptionMessage,
    ): void {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['alter_column']);

        $command = $db->createCommand();

        $command->createTable(
            'alter_column',
            ['id' => 'pk', 'bar' => 'varchar(100)'],
        )->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            $exceptionMessage,
        );

        $command->alterColumn(
            'alter_column',
            'bar',
            $type,
        )->execute();
    }

    public function testAlterColumnRollsBackDroppedConstraintsWhenAlterationFails(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->defaultValue('fallback')
                ->check('LEN(bar) > 0')
                ->unique(),
        )->execute();
        $command->insert(
            'foo1',
            ['bar' => 'not numeric'],
        )->execute();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $originalDefaults = $schema->getTableDefaultValues('foo1', true);
        $originalChecks = $schema->getTableChecks('foo1', true);
        $originalUniques = $schema->getTableUniques('foo1', true);

        self::assertCount(
            1,
            $originalDefaults,
            'The test column must have one default constraint.',
        );
        self::assertCount(
            1,
            $originalChecks,
            'The test column must have one check constraint.',
        );
        self::assertCount(
            1,
            $originalUniques,
            'The test column must have one unique constraint.',
        );
        self::assertNull(
            $db->getTransaction(),
            'No explicit transaction should be active before altering the column.',
        );
        self::assertFalse(
            $db->getMasterPdo()->inTransaction(),
            'PDO should not have an active transaction before altering the column.',
        );

        $exception = null;

        try {
            $command->alterColumn(
                'foo1',
                'bar',
                $schema->createColumnSchemaBuilder(Schema::TYPE_INTEGER),
            )->execute();
        } catch (Exception $e) {
            $exception = $e;
        }

        self::assertInstanceOf(
            Exception::class,
            $exception,
            'Converting a non-numeric value to INTEGER must fail with a database exception.',
        );
        self::assertNull(
            $db->getTransaction(),
            'The command-owned transaction must be closed after the failure.',
        );
        self::assertFalse(
            $db->getMasterPdo()->inTransaction(),
            'PDO must not retain the command-owned transaction after the failure.',
        );

        $tableSchema = $schema->getTableSchema('foo1', true);

        self::assertSame(
            'nvarchar(64)',
            $tableSchema->getColumn('bar')->dbType,
            'The original column type must be restored after the failed alteration.',
        );

        $defaults = $schema->getTableDefaultValues('foo1', true);
        $checks = $schema->getTableChecks('foo1', true);
        $uniques = $schema->getTableUniques('foo1', true);

        self::assertCount(
            1,
            $defaults,
            'The original default constraint must remain after the failure.',
        );
        self::assertSame(
            $originalDefaults[0]->name,
            $defaults[0]->name,
            'The original default constraint must be restored.',
        );
        self::assertCount(
            1,
            $checks,
            'The original check constraint must remain after the failure.',
        );
        self::assertSame(
            $originalChecks[0]->name,
            $checks[0]->name,
            'The original check constraint must be restored.',
        );
        self::assertCount(
            1,
            $uniques,
            'The original unique constraint must remain after the failure.',
        );
        self::assertSame(
            $originalUniques[0]->name,
            $uniques[0]->name,
            'The original unique constraint must be restored.',
        );
    }

    public function testAlterColumnDoesNotCommitExternalTransaction(): void
    {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $originalColumnType = $schema
            ->getTableSchema('foo1', true)
            ->getColumn('bar')
            ->dbType;

        $transaction = $db->beginTransaction();

        try {
            $db->createCommand()->alterColumn(
                'foo1',
                'bar',
                $schema->createColumnSchemaBuilder(Schema::TYPE_STRING, 64),
            )->execute();

            self::assertSame(
                $transaction,
                $db->getTransaction(),
                'Alter column must participate in the existing transaction.',
            );
            self::assertTrue(
                $transaction->isActive,
                'Alter column must not commit the existing transaction.',
            );
            self::assertSame(
                'nvarchar(64)',
                $schema->getTableSchema('foo1', true)->getColumn('bar')->dbType,
                'Alter column must be visible inside the existing transaction.',
            );
        } finally {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
        }

        self::assertNull(
            $db->getTransaction(),
            'No transaction should remain active after the external rollback.',
        );
        self::assertFalse(
            $db->getMasterPdo()->inTransaction(),
            'PDO must not retain the external transaction after rollback.',
        );
        self::assertSame(
            $originalColumnType,
            $schema->getTableSchema('foo1', true)->getColumn('bar')->dbType,
            'Rolling back the external transaction must restore the original column type.',
        );
    }

    public function testFailedAlterColumnDoesNotRollBackExternalTransaction(): void
    {
        $db = $this->getConnection();

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $schema
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->defaultValue('fallback')
                ->check('LEN(bar) > 0')
                ->unique(),
        )->execute();
        $db->createCommand()->insert(
            'foo1',
            ['bar' => 'not numeric'],
        )->execute();

        $originalColumnType = $schema
            ->getTableSchema('foo1', true)
            ->getColumn('bar')
            ->dbType;

        $originalDefaults = $schema->getTableDefaultValues('foo1', true);
        $originalChecks = $schema->getTableChecks('foo1', true);
        $originalUniques = $schema->getTableUniques('foo1', true);

        self::assertCount(
            1,
            $originalDefaults,
            'The test column must have one default constraint.',
        );
        self::assertCount(
            1,
            $originalChecks,
            'The test column must have one check constraint.',
        );
        self::assertCount(
            1,
            $originalUniques,
            'The test column must have one unique constraint.',
        );

        $transaction = $db->beginTransaction();

        try {
            $exception = null;

            try {
                $db->createCommand()->alterColumn(
                    'foo1',
                    'bar',
                    $schema->createColumnSchemaBuilder(Schema::TYPE_INTEGER),
                )->execute();
            } catch (Exception $e) {
                $exception = $e;
            }

            self::assertInstanceOf(
                Exception::class,
                $exception,
                'Converting a non-numeric value to INTEGER must fail with a database exception.',
            );
            self::assertSame(
                $transaction,
                $db->getTransaction(),
                'A failed alter column must preserve the existing transaction.',
            );
            self::assertTrue(
                $transaction->isActive,
                'A failed alter column must leave rollback responsibility to the caller.',
            );
        } finally {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
        }

        self::assertNull(
            $db->getTransaction(),
            'No transaction should remain active after the external rollback.',
        );
        self::assertFalse(
            $db->getMasterPdo()->inTransaction(),
            'PDO must not retain the external transaction after rollback.',
        );
        self::assertSame(
            $originalColumnType,
            $schema->getTableSchema('foo1', true)->getColumn('bar')->dbType,
            'The external rollback must restore the original column type.',
        );

        $defaults = $schema->getTableDefaultValues('foo1', true);
        $checks = $schema->getTableChecks('foo1', true);
        $uniques = $schema->getTableUniques('foo1', true);

        self::assertCount(
            1,
            $defaults,
            'The external rollback must restore the original default constraint.',
        );
        self::assertSame(
            $originalDefaults[0]->name,
            $defaults[0]->name,
            'The restored default constraint must preserve its original name.',
        );
        self::assertCount(
            1,
            $checks,
            'The external rollback must restore the original check constraint.',
        );
        self::assertSame(
            $originalChecks[0]->name,
            $checks[0]->name,
            'The restored check constraint must preserve its original name.',
        );
        self::assertCount(
            1,
            $uniques,
            'The external rollback must restore the original unique constraint.',
        );
        self::assertSame(
            $originalUniques[0]->name,
            $uniques[0]->name,
            'The restored unique constraint must preserve its original name.',
        );
    }

    public function testAlterColumnWithCheckConstraint(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)
                ->null()
                ->check('LEN(bar) > 5'),
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
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->check('LEN(bar) > 5'),
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
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->unique(),
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

        $quotedTable = $db->quoteTableName($table);

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)
                ->defaultValue('initial'),
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
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                ->defaultValue(0),
        )->execute();

        $tableSchema = $db->getTableSchema($table, true);

        self::assertSame(
            'int',
            $tableSchema->getColumn('bar')->dbType,
            'Type change must succeed with a default bound.',
        );

        /** @var Schema $schema */
        $schema = $db->getSchema();

        self::assertCount(
            1,
            $schema->getTableDefaultValues($table, true),
            'Old default constraint must be replaced, not duplicated.',
        );

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 96)
                ->defaultValue('catalog')
                ->check('LEN(bar) > 3')
                ->unique(),
        )->execute();

        $tableSchema = $db->getTableSchema($table, true);

        self::assertSame(
            'nvarchar(96)',
            $tableSchema->getColumn('bar')->dbType,
            'Column type must reflect the catalog-qualified ALTER COLUMN definition.',
        );
        self::assertCount(
            1,
            $schema->getTableDefaultValues($table, true),
            'Exactly one default constraint must be created for the catalog-qualified table.',
        );
        self::assertCount(
            1,
            $schema->getTableChecks($table, true),
            'Exactly one check constraint must be created for the catalog-qualified table.',
        );
        self::assertCount(
            1,
            $schema->getTableUniques($table, true),
            'Exactly one unique constraint must be created for the catalog-qualified table.',
        );
        self::assertSame(
            1,
            $db->createCommand(
                <<<SQL
                INSERT INTO {$quotedTable} DEFAULT VALUES
                SQL,
            )->execute(),
            'The catalog-qualified default constraint must be usable by INSERT.',
        );
        self::assertSame(
            'catalog',
            $db->createCommand(
                <<<SQL
                SELECT TOP 1 [bar] FROM {$quotedTable} ORDER BY [id] DESC
                SQL,
            )->queryScalar(),
            'The default constraint must populate the column value.',
        );

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)
                ->defaultValue('changed')
                ->check('LEN(bar) > 5')
                ->unique(),
        )->execute();

        $tableSchema = $db->getTableSchema($table, true);

        self::assertSame(
            'nvarchar(128)',
            $tableSchema->getColumn('bar')->dbType,
            'A second catalog-qualified ALTER COLUMN must change the column type.',
        );
        self::assertCount(
            1,
            $schema->getTableDefaultValues($table, true),
            'The old default constraint must be replaced, not duplicated.',
        );
        self::assertCount(
            1,
            $schema->getTableChecks($table, true),
            'The old check constraint must be replaced, not duplicated.',
        );
        self::assertCount(
            1,
            $schema->getTableUniques($table, true),
            'The old unique constraint must be replaced, not duplicated.',
        );
        self::assertSame(
            1,
            $db->createCommand(
                <<<SQL
                INSERT INTO {$quotedTable} DEFAULT VALUES
                SQL,
            )->execute(),
            'The replacement default constraint must be usable by INSERT.',
        );
        self::assertSame(
            'changed',
            $db->createCommand(
                <<<SQL
                SELECT TOP 1 [bar] FROM {$quotedTable} ORDER BY [id] DESC
                SQL,
            )->queryScalar(),
            'The replacement default constraint must populate the column value.',
        );
    }

    public function testThrowIntegrityExceptionWhenInsertViolatesCatalogQualifiedAlterColumnCheckConstraint(): void
    {
        $db = $this->getConnection();

        $catalogName = (string) $db->createCommand(
            <<<SQL
            SELECT DB_NAME()
            SQL
        )->queryScalar();

        $table = "{$catalogName}.dbo.foo1";
        $quotedTable = $db->quoteTableName($table);

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 96)
                ->defaultValue('catalog')
                ->check('LEN(bar) > 3')
                ->unique(),
        )->execute();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessageMatches(
            '/CHECK constraint/i',
        );

        $db->createCommand(
            <<<SQL
            INSERT INTO {$quotedTable} ([bar]) VALUES ('abc')
            SQL,
        )->execute();
    }

    public function testThrowIntegrityExceptionWhenInsertViolatesCatalogQualifiedAlterColumnReplacementCheckConstraint(): void
    {
        $db = $this->getConnection();

        $catalogName = (string) $db->createCommand(
            <<<SQL
            SELECT DB_NAME()
            SQL
        )->queryScalar();

        $table = "{$catalogName}.dbo.foo1";
        $quotedTable = $db->quoteTableName($table);

        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 96)
                ->defaultValue('catalog')
                ->check('LEN(bar) > 3')
                ->unique(),
        )->execute();
        $db->createCommand()->alterColumn(
            $table,
            'bar',
            $db->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)
                ->defaultValue('changed')
                ->check('LEN(bar) > 5')
                ->unique(),
        )->execute();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessageMatches(
            '/CHECK constraint/i',
        );

        $db->createCommand(
            <<<SQL
            INSERT INTO {$quotedTable} ([bar]) VALUES ('abcd')
            SQL,
        )->execute();
    }

    public function testAlterColumnReplacesDefaultValue(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 128)
                ->defaultValue('initial'),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_INTEGER)
                ->defaultValue(0),
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
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->check('LEN(bar) > 5'),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->check('LEN(bar) > 3'),
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
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 64)
                ->unique(),
        )->execute();
        $db->createCommand()->alterColumn(
            'foo1',
            'bar',
            $db
                ->getSchema()
                ->createColumnSchemaBuilder(Schema::TYPE_STRING, 32)
                ->unique(),
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

        $testData = json_encode(['test' => 'string', 'test2' => 'integer'], JSON_THROW_ON_ERROR);

        $params = [];

        $sql = $db->getQueryBuilder()->upsert(
            'T_upsert_varbinary',
            ['id' => 1, 'blob_col' => $testData],
            ['blob_col' => $testData],
            $params,
        );
        $result = $db->createCommand($sql, $params)->execute();

        self::assertSame(
            1,
            $result,
            'Executing the merge command must affect exactly one row.',
        );

        $query = (new Query())
            ->select(['blob_col'])
            ->from('T_upsert_varbinary')
            ->where(['id' => 1]);

        $resultData = $query->createCommand($db)->queryOne();

        self::assertSame(
            $testData,
            $resultData['blob_col'],
            'The varbinary column must store and return the JSON payload unchanged.',
        );
    }

    #[DataProviderExternal(CommandProvider::class, 'insert')]
    public function testInsertWithCatalogQualifiedTableName(string $table, array $columns, array $expected): void
    {
        $db = $this->getConnection();

        $db->createCommand()->delete(
            $table,
            ['email' => $expected['email']],
        )->execute();
        $db->createCommand()->insert(
            $table,
            $columns,
        )->execute();

        $actual = (new Query())
            ->select(['email', 'name', 'address'])
            ->from($table)
            ->where(['email' => $expected['email']])
            ->one($db);

        self::assertEquals(
            $expected,
            $actual,
            'Catalog qualified insert must persist the row.',
        );

        $db->createCommand()->delete(
            $table,
            ['email' => $expected['email']],
        )->execute();
    }

    #[DataProviderExternal(CommandProvider::class, 'upsert')]
    public function testUpsert(
        string $table,
        array|Query $firstInsert,
        array|Query $secondInsert,
        array|bool $updateColumns,
        array $expected,
    ): void {
        $db = $this->getConnection();

        $command = $db->createCommand();

        self::assertSame(
            0,
            (int) (new Query())
                ->from($table)
                ->count('*', $db),
            'Target table must start empty.',
        );

        $command->upsert(
            $table,
            $firstInsert,
            $updateColumns,
        )->execute();

        self::assertSame(
            1,
            (int) (new Query())
                ->from($table)
                ->count('*', $db),
            'Insert path must create exactly one row.',
        );

        $command->upsert(
            $table,
            $secondInsert,
            $updateColumns,
        )->execute();

        $select = [];

        foreach (array_keys($expected) as $column) {
            $select[$column] = $column === 'address'
                ? new Expression('CAST([[address]] AS VARCHAR(255))')
                : $column;
        }

        self::assertEquals(
            $expected,
            (new Query())
                ->select($select)
                ->from($table)
                ->one($db),
            'Conflict must apply the update behavior.',
        );
    }

    #[DataProviderExternal(CommandProvider::class, 'upsertWithCatalogQualifiedTableName')]
    public function testUpsertWithCatalogQualifiedTableName(
        string $table,
        array $insertColumns,
        array $updateColumns,
        array $expected,
    ): void {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $command->delete(
            $table,
            ['email' => $expected['email']],
        )->execute();
        $command->upsert(
            $table,
            $insertColumns,
        )->execute();

        self::assertSame(
            1,
            (int) (new Query())
                ->from($table)
                ->where(['email' => $expected['email']])
                ->count('*', $db),
            'Upsert insert path must create exactly one row.',
        );

        $command->upsert(
            $table,
            $updateColumns,
        )->execute();

        $actual = (new Query())
            ->select(
                [
                    'email',
                    'address',
                    'status',
                ],
            )
            ->from($table)
            ->where(['email' => $expected['email']])
            ->one($db);

        self::assertEquals(
            $expected,
            $actual,
            'Upsert update path must overwrite the matched row.',
        );

        $command->delete(
            $table,
            ['email' => $expected['email']],
        )->execute();
    }

    public function testCreateTableAndDropTableWithSchemaQualifiedName(): void
    {
        $db = $this->getConnection();

        $tableName = 'dbo.T_migration_schema';

        DbHelper::dropTablesIfExist($db, [$tableName]);

        $db->createCommand()->createTable(
            $tableName,
            ['id' => Schema::TYPE_PK],
        )->execute();

        $tableSchema = $db->getTableSchema($tableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $tableSchema,
            'Table must exist in the schema after creation.',
        );
        self::assertSame(
            'dbo',
            $tableSchema->schemaName,
            'Schema name must match the qualified prefix.',
        );
        self::assertNotNull(
            $tableSchema->getColumn('id'),
            'Primary key column must be present.',
        );

        $db->createCommand()->dropTable($tableName)->execute();

        self::assertNull(
            $db->getTableSchema($tableName, true),
            'Table must be absent from the schema after drop.',
        );

        DbHelper::dropTablesIfExist($db, [$tableName]);
    }

    public function testMigrationDdlLifecycleWithSchemaQualifiedName(): void
    {
        $db = $this->getConnection();

        $tableName = 'dbo.T_migration_ddl';

        DbHelper::dropTablesIfExist($db, [$tableName]);

        $db->createCommand()->createTable(
            $tableName,
            ['id' => Schema::TYPE_PK],
        )->execute();

        self::assertInstanceOf(
            TableSchema::class,
            $db->getTableSchema($tableName, true),
            'Table must exist after creation.',
        );

        $db->createCommand()->addColumn(
            $tableName,
            'label',
            Schema::TYPE_STRING,
        )->execute();

        $tableSchema = $db->getTableSchema($tableName, true);

        self::assertNotNull(
            $tableSchema->getColumn('label'),
            'Added column must appear in refreshed schema.',
        );

        $db->createCommand()->createIndex(
            'idx_T_migration_ddl_label',
            $tableName,
            'label',
        )->execute();

        $db->createCommand()->dropIndex(
            'idx_T_migration_ddl_label',
            $tableName,
        )->execute();

        $db->createCommand()->dropTable($tableName)->execute();

        self::assertNull(
            $db->getTableSchema($tableName, true),
            'Table must be absent from the schema after drop.',
        );

        DbHelper::dropTablesIfExist($db, [$tableName]);
    }
}

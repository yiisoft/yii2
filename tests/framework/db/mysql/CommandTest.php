<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\ConstraintFinderInterface;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\db\mysql\QueryBuilder;
use yii\db\mysql\Schema;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\mysql\providers\CommandProvider;
use yiiunit\support\DbHelper;

use function array_keys;

/**
 * Unit tests for {@see \yii\db\Command} functionality for the MySQL driver.
 *
 * {@see CommandProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('command')]
final class CommandTest extends BaseCommand
{
    public $driverName = 'mysql';

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
                ? new Expression('CONVERT([[address]], CHAR)')
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

    public function testInsertReflectedExpressionDefaults(): void
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand();
        /** @var QueryBuilder $qb */
        $qb = $db->getQueryBuilder();

        DbHelper::dropTablesIfExist($db, ['expression_default_command_test']);

        $command->createTable(
            'expression_default_command_test',
            [
                'date_expression' => 'date NOT NULL DEFAULT (CURRENT_DATE + INTERVAL 2 YEAR)',
                'text_expression' => "text NOT NULL DEFAULT ('abc')",
                'json_expression' => 'json NOT NULL DEFAULT (JSON_ARRAY())',
                'literal' => "date NOT NULL DEFAULT '2011-11-11'",
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        )->execute();

        $columns = $db->getTableSchema('expression_default_command_test', true)->columns;

        if ($qb->isMariaDb()) {
            // MariaDB reflects the date expression default as a plain string that cannot round-trip through an
            // insert: omit it and let the engine apply the stored default.
            $insert = [
                'text_expression' => $columns['text_expression']->defaultValue,
                'json_expression' => $columns['json_expression']->defaultValue,
                'literal' => $columns['literal']->defaultValue,
            ];
        } else {
            $insert = [
                'date_expression' => $columns['date_expression']->defaultValue,
                'text_expression' => $columns['text_expression']->defaultValue,
                'json_expression' => $columns['json_expression']->defaultValue,
                'literal' => $columns['literal']->defaultValue,
            ];
        }

        $command->insert(
            'expression_default_command_test',
            $insert,
        )->execute();

        self::assertSame(
            [
                'date_expression' => $command->setSql(
                    <<<SQL
                    SELECT CURRENT_DATE + INTERVAL 2 YEAR
                    SQL
                )->queryScalar(),
                'text_expression' => 'abc',
                'json_expression' => '[]',
                'literal' => '2011-11-11',
            ],
            (new Query())
                ->from('expression_default_command_test')
                ->one($db),
            'Row must match the engine-applied defaults.',
        );

        DbHelper::dropTablesIfExist($db, ['expression_default_command_test']);
    }

    #[DataProviderExternal(CommandProvider::class, 'addCommentOnColumn')]
    public function testAddUpdateDropCommentOnColumn(string $table, string $commentTarget, string $columnName): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$table]);

        $db->createCommand()->createTable(
            $table,
            [
                'id' => 'integer',
                $columnName => 'string',
            ],
        )->execute();
        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'It\'s an initial comment.',
        )->execute();

        self::assertSame(
            'It\'s an initial comment.',
            $schema->getTableSchema($table, true)->getColumn($columnName)->comment,
            'Column comment must be created.',
        );

        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'It\'s an updated comment.',
        )->execute();

        self::assertSame(
            'It\'s an updated comment.',
            $schema->getTableSchema($table, true)->getColumn($columnName)->comment,
            'Column comment must be updated.',
        );

        $db->createCommand()->dropCommentFromColumn(
            $commentTarget,
            $columnName,
        )->execute();

        self::assertEmpty(
            $schema->getTableSchema($table, true)->getColumn($columnName)->comment,
            'Column comment must be removed.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
    }

    #[DataProviderExternal(CommandProvider::class, 'commentSpecialCharacters')]
    public function testAddCommentOnColumnRoundTripsSpecialCharacters(string $comment): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();
        $sqlMode = $db->createCommand(
            <<<SQL
            SELECT @@SESSION.sql_mode
            SQL,
        )->queryScalar();

        $modes = [
            'default' => '',
            'no_backslash_escapes' => 'NO_BACKSLASH_ESCAPES',
        ];

        foreach ($modes as $label => $mode) {
            $db->createCommand(
                <<<SQL
                SET SESSION sql_mode = '$mode'
                SQL,
            )->execute();

            DbHelper::dropTablesIfExist($db, ['comment_special']);

            $db->createCommand()->createTable(
                'comment_special',
                [
                    'id' => 'integer',
                    'description' => 'string',
                ],
            )->execute();
            $db->createCommand()->addCommentOnColumn(
                'comment_special',
                'description',
                $comment,
            )->execute();

            self::assertSame(
                $comment,
                $schema->getTableSchema('comment_special', true)->getColumn('description')->comment,
                "Comment must round-trip under the `$label` sql_mode.",
            );

            DbHelper::dropTablesIfExist($db, ['comment_special']);
        }

        $db->createCommand(
            <<<SQL
            SET SESSION sql_mode = :sqlMode
            SQL,
            [':sqlMode' => $sqlMode],
        )->execute();
    }

    public function testAddCommentOnColumnExecutesWithCheckContainingQuotedParenthesis(): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, ['quoted_paren']);

        $db->createCommand(
            <<<SQL
            CREATE TABLE `quoted_paren` (`status` varchar(32) CHECK (`status` <> '('))
            SQL,
        )->execute();

        $db->createCommand()->addCommentOnColumn(
            'quoted_paren',
            'status',
            'A column comment.',
        )->execute();

        self::assertSame(
            'A column comment.',
            $schema->getTableSchema('quoted_paren', true)->getColumn('status')->comment,
            'Comment must round-trip when the CHECK contains a quoted parenthesis.',
        );

        DbHelper::dropTablesIfExist($db, ['quoted_paren']);
    }

    public function testThrowExceptionWhenColumnIsMissing(): void
    {
        $db = $this->getConnection(false);

        DbHelper::dropTablesIfExist($db, ['missing_column']);

        $db->createCommand(
            <<<SQL
            CREATE TABLE `missing_column` (`id` int)
            SQL,
        )->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Unable to find column 'nonexistent' in table 'missing_column'.",
        );

        $db->createCommand()->addCommentOnColumn(
            'missing_column',
            'nonexistent',
            'A column comment.',
        );
    }

    #[DataProviderExternal(CommandProvider::class, 'renameColumn')]
    public function testRenameColumn(
        string $table,
        string $renameTarget,
        string $oldColumnName,
        string $newColumnName,
    ): void {
        $db = $this->getConnection(false);

        $command = $db->createCommand();
        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$table]);

        $command->createTable(
            $table,
            [$oldColumnName => 'integer'],
        )->execute();

        self::assertNotNull(
            $schema->getTableSchema($table, true)->getColumn($oldColumnName),
            'Old column must exist before renaming.',
        );
        self::assertNull(
            $schema->getTableSchema($table, true)->getColumn($newColumnName),
            'New column must not exist before renaming.',
        );

        $command->renameColumn(
            $renameTarget,
            $oldColumnName,
            $newColumnName,
        )->execute();

        self::assertNull(
            $schema->getTableSchema($table, true)->getColumn($oldColumnName),
            'Old column must not exist after renaming.',
        );
        self::assertNotNull(
            $schema->getTableSchema($table, true)->getColumn($newColumnName),
            'New column must exist after renaming.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
    }

    public function testRenameColumnPreservesColumnDefinition(): void
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand();
        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, ['rename_column_definition']);

        $command->createTable(
            'rename_column_definition',
            ['old_col' => "varchar(255) NOT NULL DEFAULT 'keep' COMMENT 'Keep me.'"],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        )->execute();
        $command->renameColumn(
            'rename_column_definition',
            'old_col',
            'new_col',
        )->execute();

        $column = $schema->getTableSchema('rename_column_definition', true)->getColumn('new_col');

        self::assertNotNull(
            $column,
            'Renamed column must exist.',
        );
        self::assertFalse(
            $column->allowNull,
            "'NOT NULL' must be preserved.",
        );
        self::assertSame(
            'keep',
            $column->defaultValue,
            'Default value must be preserved.',
        );
        self::assertSame(
            'Keep me.',
            $column->comment,
            'Comment must be preserved.',
        );

        DbHelper::dropTablesIfExist($db, ['rename_column_definition']);
    }

    public function testThrowExceptionWhenRenamingColumnReferencedByCheckConstraint(): void
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand();
        /** @var QueryBuilder $qb */
        $qb = $db->getQueryBuilder();

        if ($qb->isMariaDb()) {
            self::markTestSkipped(
                'MariaDB rewrites CHECK constraint expressions on native RENAME COLUMN (MDEV-13508).',
            );
        }

        DbHelper::dropTablesIfExist($db, ['rename_column_check']);

        $command->createTable(
            'rename_column_check',
            ['status' => "varchar(32) CHECK (`status` <> '(')"],
        )->execute();

        // MySQL 8.0.16+ rejects with error 3959 because the table-level CHECK still references the renamed column.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Check constraint 'rename_column_check_chk_1' uses column 'status', hence column cannot be dropped or "
            . 'renamed.',
        );

        $command->renameColumn(
            'rename_column_check',
            'status',
            'new_status',
        )->execute();
    }

    public function testRenameColumnRewritesCheckConstraintExpression(): void
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand();
        /** @var QueryBuilder $qb */
        $qb = $db->getQueryBuilder();

        if ($qb->isMariaDb() === false) {
            self::markTestSkipped(
                'MySQL rejects renaming a column referenced by a CHECK constraint (error 3959).',
            );
        }

        DbHelper::dropTablesIfExist($db, ['rename_column_check']);

        $command->createTable(
            'rename_column_check',
            ['status' => "varchar(32) CHECK (`status` <> '(')"],
        )->execute();
        $command->renameColumn(
            'rename_column_check',
            'status',
            'new_status',
        )->execute();

        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema must implement the constraint finder contract.',
        );
        self::assertNull(
            $schema->getTableSchema('rename_column_check', true)->getColumn('status'),
            'Old column must not exist after renaming.',
        );
        self::assertNotNull(
            $schema->getTableSchema('rename_column_check', true)->getColumn('new_status'),
            'New column must exist after renaming.',
        );

        $checks = $schema->getTableChecks('rename_column_check', true);

        self::assertCount(
            1,
            $checks,
            'Table must keep exactly one CHECK constraint.',
        );
        self::assertStringContainsString(
            '`new_status`',
            $checks[0]->expression,
            'CHECK expression must reference the renamed column.',
        );

        DbHelper::dropTablesIfExist($db, ['rename_column_check']);
    }

    public function testThrowExceptionWhenRenamingMissingColumn(): void
    {
        $db = $this->getConnection(false);

        DbHelper::dropTablesIfExist($db, ['rename_missing_column']);

        $command = $db->createCommand();

        $command->createTable(
            'rename_missing_column',
            ['id' => 'integer'],
        )->execute();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Unknown column 'nonexistent' in 'rename_missing_column'",
        );

        $command->renameColumn(
            'rename_missing_column',
            'nonexistent',
            'new_col',
        )->execute();
    }

    public function testThrowExceptionWhenRenamingColumnInMissingTable(): void
    {
        $db = $this->getConnection(false);

        DbHelper::dropTablesIfExist($db, ['rename_absent_table']);

        $command = $db->createCommand();

        // Both MySQL and MariaDB surface the missing table as error 1146 (`ER_NO_SUCH_TABLE`) when the native
        // `ALTER TABLE ... RENAME COLUMN` statement executes.
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'yiitest.rename_absent_table' doesn't exist",
        );

        $command->renameColumn(
            'rename_absent_table',
            'old_col',
            'new_col',
        )->execute();
    }

    public function testAddDropCheckSeveral(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'test_ck_several';
        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer',
            'int2' => 'integer',
            'int3' => 'integer',
            'int4' => 'integer',
        ])->execute();

        $this->assertEmpty($schema->getTableChecks($tableName, true));

        $constraints = [
            ['name' => 'check_int1_positive', 'expression' => '[[int1]] > 0', 'expected' => '(`int1` > 0)'],
            ['name' => 'check_int2_nonzero', 'expression' => '[[int2]] <> 0', 'expected' => '(`int2` <> 0)'],
            ['name' => 'check_int3_less_than_100', 'expression' => '[[int3]] < 100', 'expected' => '(`int3` < 100)'],
            ['name' => 'check_int1_less_than_int2', 'expression' => '[[int1]] < [[int2]]', 'expected' => '(`int1` < `int2`)'],
        ];

        if (\stripos($db->getServerVersion(), 'MariaDb') !== false) {
            $constraints[0]['expected'] = '`int1` > 0';
            $constraints[1]['expected'] = '`int2` <> 0';
            $constraints[2]['expected'] = '`int3` < 100';
            $constraints[3]['expected'] = '`int1` < `int2`';
        }

        foreach ($constraints as $constraint) {
            $db->createCommand()->addCheck($constraint['name'], $tableName, $constraint['expression'])->execute();
        }

        $tableChecks = $schema->getTableChecks($tableName, true);
        $this->assertCount(4, $tableChecks);

        foreach ($constraints as $index => $constraint) {
            $this->assertSame(
                $constraints[$index]['expected'],
                $tableChecks[$index]->expression
            );
        }

        foreach ($constraints as $constraint) {
            $db->createCommand()->dropCheck($constraint['name'], $tableName)->execute();
        }

        $this->assertEmpty($schema->getTableChecks($tableName, true));
    }

    #[DataProviderExternal(CommandProvider::class, 'createIndex')]
    public function testCreateIndexWithQualifiedTableNames(
        string $table,
        string $indexTarget,
        string $indexName,
        array|string $columns,
        bool $unique,
        array $expectedColumns,
        bool $expectedUnique,
    ): void {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$table]);

        $db->createCommand()->createTable(
            $table,
            [
                'int1' => 'integer not null',
                'int2' => 'integer not null',
            ],
        )->execute();

        self::assertEmpty(
            $schema->getTableIndexes($table, true),
            'No index must exist before creation.',
        );

        $db->createCommand()->createIndex(
            $indexName,
            $indexTarget,
            $columns,
            $unique,
        )->execute();

        $indexes = $schema->getTableIndexes($table, true);

        self::assertCount(
            1,
            $indexes,
            'Exactly one index must exist after creation.',
        );
        self::assertSame(
            $expectedColumns,
            $indexes[0]->columnNames,
            'Index must cover the requested columns in order.',
        );
        self::assertSame(
            $expectedUnique,
            $indexes[0]->isUnique,
            'Uniqueness must match the requested flag.',
        );

        $db->createCommand()->dropIndex(
            $indexName,
            $indexTarget,
        )->execute();

        self::assertEmpty(
            $schema->getTableIndexes($table, true),
            'Index must be removed after drop.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
    }

    #[DataProviderExternal(CommandProvider::class, 'dropForeignKey')]
    public function testDropForeignKeyWithQualifiedTableNames(
        string $table,
        string $fkTarget,
        string $fkName,
        array $fkColumns,
        array $refColumns,
    ): void {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$table]);

        $db->createCommand()->createTable(
            $table,
            [
                'int1' => 'integer not null unique',
                'int2' => 'integer not null unique',
                'int3' => 'integer not null unique',
                'int4' => 'integer not null unique',
                'unique ([[int1]], [[int2]])',
                'unique ([[int3]], [[int4]])',
            ],
        )->execute();
        $db->createCommand()->addForeignKey(
            $fkName,
            $fkTarget,
            $fkColumns,
            $fkTarget,
            $refColumns,
        )->execute();

        $foreignKeys = $schema->getTableForeignKeys($table, true);

        self::assertCount(
            1,
            $foreignKeys,
            'Foreign key must exist before drop.',
        );
        self::assertSame(
            $fkColumns,
            $foreignKeys[0]->columnNames,
            'Foreign key must cover the requested columns.',
        );

        $db->createCommand()->dropForeignKey(
            $fkName,
            $fkTarget,
        )->execute();

        self::assertEmpty(
            $schema->getTableForeignKeys($table, true),
            'Foreign key must be removed after drop.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
    }

    #[DataProviderExternal(CommandProvider::class, 'dropPrimaryKey')]
    public function testDropPrimaryKeyWithQualifiedTableNames(
        string $table,
        string $pkTarget,
        string $pkName,
        array $pkColumns,
    ): void {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$table]);

        $db->createCommand()->createTable(
            $table,
            [
                'int1' => 'integer not null',
                'int2' => 'integer not null',
            ],
        )->execute();

        self::assertNull(
            $schema->getTablePrimaryKey($table, true),
            'No primary key must exist before creation.',
        );

        $db->createCommand()->addPrimaryKey(
            $pkName,
            $pkTarget,
            $pkColumns,
        )->execute();

        $primaryKey = $schema->getTablePrimaryKey($table, true);

        self::assertNotNull(
            $primaryKey,
            'Primary key must exist after creation.',
        );
        self::assertSame(
            $pkColumns,
            $primaryKey->columnNames,
            'Primary key must cover the requested columns.',
        );

        $db->createCommand()->dropPrimaryKey(
            $pkName,
            $pkTarget,
        )->execute();

        self::assertNull(
            $schema->getTablePrimaryKey($table, true),
            'Primary key must be removed after drop.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
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

        DbHelper::dropTablesIfExist($db, [$rawTableName]);

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
            $command->resetSequence($tableName)->execute();
        } else {
            $command->resetSequence($tableName, $value)->execute();
        }

        $db->createCommand()->insert(
            $tableName,
            ['description' => 'after reset'],
        )->execute();

        self::assertSame(
            $expectedId,
            (int) $db->createCommand(
                <<<SQL
                SELECT MAX(`id`) FROM {$db->quoteTableName($rawTableName)}
                SQL,
            )->queryScalar(),
            'The generated identity value must match the expected next value.',
        );

        DbHelper::dropTablesIfExist($db, [$rawTableName]);
    }
}

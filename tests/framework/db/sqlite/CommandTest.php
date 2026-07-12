<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\db\sqlite\Schema;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\sqlite\providers\CommandProvider;
use yiiunit\support\DbHelper;

use function array_keys;

/**
 * Unit tests for {@see \yii\db\sqlite\Command} functionality for the SQLite driver.
 *
 * {@see CommandProvider} for test case data providers.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('command')]
class CommandTest extends BaseCommand
{
    protected $driverName = 'sqlite';

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

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT `id`, `t`.`name` FROM `customer` t', $command->sql);
    }

    public function testUpsertUsesAnyUniqueConstraint(): void
    {
        $db = $this->getConnection();

        $table = 'upsert_multiple_unique';

        DbHelper::dropTablesIfExist($db, [$table]);

        $command = $db->createCommand();

        $command->createTable(
            $table,
            [
                'id' => Schema::TYPE_PK,
                'email' => 'text NOT NULL UNIQUE',
                'username' => 'text NOT NULL UNIQUE',
                'status' => 'integer NOT NULL',
            ],
        )->execute();

        self::assertSame(
            1,
            $command->upsert(
                $table,
                ['email' => 'first@example.com', 'username' => 'shared', 'status' => 1],
            )->execute(),
            'Initial upsert must insert one row.',
        );
        self::assertSame(
            1,
            $command->upsert(
                $table,
                ['email' => 'second@example.com', 'username' => 'shared', 'status' => 2],
            )->execute(),
            'A username conflict must update one row.',
        );
        self::assertSame(
            1,
            $command->upsert(
                $table,
                ['email' => 'first@example.com', 'username' => 'different', 'status' => 3],
            )->execute(),
            'An email conflict must update one row.',
        );
        self::assertSame(
            [
                'email' => 'first@example.com',
                'username' => 'shared',
                'status' => '3',
            ],
            (new Query())
                ->select(['email', 'username', 'status'])
                ->from($table)
                ->one($db),
            'Conflicts on either unique constraint must update the same row.',
        );

        DbHelper::dropTablesIfExist($db, [$table]);
    }

    public function testUpsertFromUnionQueryWithoutFinalWhere(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $query = (new Query())
            ->select(
                [
                    'email',
                    'status' => new Expression('2'),
                ],
            )
            ->from('customer')
            ->where(['id' => -1])
            ->union(
                (new Query())
                    ->select(
                        [
                            'email',
                            'status' => new Expression('2'),
                        ],
                    )
                    ->from('customer'),
            );

        self::assertSame(
            3,
            $command->upsert(
                'T_upsert',
                $query,
            )->execute(),
            'A UNION source without a final WHERE clause must upsert every selected row.',
        );
        self::assertSame(
            3,
            (int) $command->setSql(
                <<<SQL
                SELECT COUNT(*) FROM {{T_upsert}}
                SQL
            )->queryScalar(),
            'The upsert must persist every row selected by the UNION source.',
        );
    }

    public function testUpsertDoNothingAffectedRows(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $values = [
            'email' => 'same@example.com',
            'status' => 1,
        ];

        self::assertSame(
            1,
            $command->upsert(
                'T_upsert',
                $values,
                false,
            )->execute(),
            'The first insert-only upsert must insert one row.',
        );
        self::assertSame(
            0,
            $command->upsert(
                'T_upsert',
                $values,
                false,
            )->execute(),
            'A conflicting insert-only upsert must report no affected rows.',
        );
    }

    public function testThrowIntegrityExceptionWhenUpsertViolatesNotNullConstraint(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();

        $this->expectException(IntegrityException::class);
        $this->expectExceptionMessage(
            'NOT NULL constraint failed: T_upsert.email',
        );

        $command->upsert(
            'T_upsert',
            ['email' => null],
            false,
        )->execute();
    }

    /**
     * @dataProvider addPrimaryKeyProvider
     *
     * @param string $name
     * @param string $tableName
     * @param list<string>|string $pk
     */
    public function testAddDropPrimaryKey(string $name, string $tableName, $pk): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addPrimaryKey|dropPrimaryKey) is not supported by SQLite\.$/',
        );

        parent::testAddDropPrimaryKey($name, $tableName, $pk);
    }

    /**
     * @dataProvider addForeignKeyProvider
     *
     * @param string $name
     * @param string $tableName
     * @param list<string>|string $fkColumns
     * @param list<string>|string $refColumns
     */
    public function testAddDropForeignKey(string $name, string $tableName, $fkColumns, $refColumns): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addForeignKey|dropForeignKey) is not supported by SQLite\.$/',
        );

        parent::testAddDropForeignKey($name, $tableName, $fkColumns, $refColumns);
    }

    /**
     * @dataProvider addUniqueProvider
     *
     * @param string $name
     * @param string $tableName
     * @param list<string>|string $columns
     */
    public function testAddDropUnique(string $name, string $tableName, $columns): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addUnique|dropUnique) is not supported by SQLite\.$/',
        );

        parent::testAddDropUnique($name, $tableName, $columns);
    }

    public function testAddDropCheck(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessageMatches(
            '/^.*::(addCheck|dropCheck) is not supported by SQLite\.$/',
        );

        parent::testAddDropCheck();
    }

    public function testMultiStatementSupport(): void
    {
        $db = $this->getConnection(false);

        $sql = <<<SQL
        DROP TABLE IF EXISTS {{T_multistatement}};
        CREATE TABLE {{T_multistatement}} (
            [[intcol]] INTEGER,
            [[textcol]] TEXT
        );
        INSERT INTO {{T_multistatement}} VALUES(41, :val1);
        INSERT INTO {{T_multistatement}} VALUES(42, :val2);
        SQL;

        $db->createCommand(
            $sql,
            [
                'val1' => 'foo',
                'val2' => 'bar',
            ],
        )->execute();

        $this->assertSame(
            [
                [
                    'intcol' => '41',
                    'textcol' => 'foo',
                ],
                [
                    'intcol' => '42',
                    'textcol' => 'bar',
                ],
            ],
            $db->createCommand('SELECT * FROM {{T_multistatement}}')->queryAll(),
        );

        $sql = <<<SQL
        UPDATE {{T_multistatement}} SET [[intcol]] = :newInt WHERE [[textcol]] = :val1;
        DELETE FROM {{T_multistatement}} WHERE [[textcol]] = :val2;
        SELECT * FROM {{T_multistatement}}
        SQL;

        $this->assertSame(
            [
                [
                    'intcol' => '410',
                    'textcol' => 'foo',
                ],
            ],
            $db->createCommand(
                $sql,
                [
                    'newInt' => 410,
                    'val1' => 'foo',
                    'val2' => 'bar',
                ],
            )->queryAll(),
        );
    }

    public static function batchInsertSqlProvider(): array
    {
        $parent = parent::batchInsertSqlProvider();
        unset($parent['wrongBehavior']); // Produces SQL syntax error: General error: 1 near ".": syntax error

        return $parent;
    }

    public function testResetSequence(): void
    {
        $db = $this->getConnection();

        if ($db->getTableSchema('reset_sequence', true) !== null) {
            $db->createCommand()->dropTable('reset_sequence')->execute();
        }

        // create table reset_sequence
        $db->createCommand()->createTable(
            'reset_sequence',
            [
                'id' => Schema::TYPE_PK,
                'description' => Schema::TYPE_TEXT,
            ]
        )->execute();

        // ensure auto increment is working
        $db->createCommand()->insert('reset_sequence', ['description' => 'test'])->execute();
        $this->assertEquals(1, $db->createCommand('SELECT MAX([[id]]) FROM {{reset_sequence}}')->queryScalar());

        // remove all records
        $db->createCommand()->delete('reset_sequence')->execute();
        $this->assertEquals(0, $db->createCommand('SELECT COUNT(*) FROM {{reset_sequence}}')->queryScalar());

        // counter should be reset to 1
        $db->createCommand()->resetSequence('reset_sequence')->execute();
        $db->createCommand()->insert('reset_sequence', ['description' => 'test'])->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{reset_sequence}}')->queryScalar());
        $this->assertEquals(1, $db->createCommand('SELECT MAX([[id]]) FROM {{reset_sequence}}')->queryScalar());

        // counter should be reset to 5, so next record gets ID 5
        $db->createCommand()->resetSequence('reset_sequence', 5)->execute();
        $db->createCommand()->insert('reset_sequence', ['description' => 'test'])->execute();
        $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM {{reset_sequence}}')->queryScalar());
        $this->assertEquals(5, $db->createCommand('SELECT MAX([[id]]) FROM {{reset_sequence}}')->queryScalar());
    }

    public function testResetSequenceExceptionTableNoExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table not found: no_exist_table');

        $db = $this->getConnection(false);
        $db->createCommand()->resetSequence('no_exist_table', 5)->execute();
    }

    public function testResetSequenceExceptionSequenceNoExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("There is not sequence associated with table 'type'.");

        $db = $this->getConnection(false);
        $db->createCommand()->resetSequence('type', 5)->execute();
    }

    public function testAlterTable(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            'yii\db\sqlite\QueryBuilder::alterColumn is not supported by SQLite.',
        );

        $db = $this->getConnection(false);
        $db->createCommand()->alterColumn('table1', 'column1', 'INTEGER')->execute();
    }

    public function testAddDropDefaultValue(): void
    {
        $db = $this->getConnection(false);

        try {
            $db->createCommand()->addDefaultValue(
                'test_def_constraint',
                'test_def',
                'int1',
                41,
            )->execute();

            $this->fail("Expected 'NotSupportedException' for 'addDefaultValue' not thrown.");
        } catch (NotSupportedException $e) {
            $this->assertStringContainsString(
                'yii\db\sqlite\QueryBuilder::addDefaultValue is not supported by SQLite.',
                $e->getMessage(),
            );
        }

        try {
            $db->createCommand()->dropDefaultValue(
                'test_def_constraint',
                'test_def',
            )->execute();

            $this->fail("Expected 'NotSupportedException' for 'dropDefaultValue' not thrown.");
        } catch (NotSupportedException $e) {
            $this->assertStringContainsString(
                'yii\db\sqlite\QueryBuilder::dropDefaultValue is not supported by SQLite.',
                $e->getMessage(),
            );
        }
    }
}

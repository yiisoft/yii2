<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\sqlite\Schema;

/**
 * @group db
 * @group sqlite
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'sqlite';

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT `id`, `t`.`name` FROM `customer` t', $command->sql);
    }

    /**
     * @dataProvider upsertProvider
     * @param array $firstData
     * @param array $secondData
     */
    public function testUpsert(array $firstData, array $secondData): void
    {
        if (version_compare($this->getConnection(false)->getServerVersion(), '3.8.3', '<')) {
            $this->markTestSkipped('SQLite < 3.8.3 does not support "WITH" keyword.');
            return;
        }

        parent::testUpsert($firstData, $secondData);
    }

    /**
     * @dataProvider addPrimaryKeyProvider
     *
     * @param string $name
     * @param string $tableName
     * @param array|string $pk
     *
     * @phpstan-param list<string> $pk
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
     * @param array|string $fkColumns
     * @param array|string $refColumns
     *
     * @phpstan-param list<string> $fkColumns
     * @phpstan-param list<string> $refColumns
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
     * @param array|string $columns
     *
     * @phpstan-param list<string> $columns
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

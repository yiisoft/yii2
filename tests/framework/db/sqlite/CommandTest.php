<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yii\db\sqlite\Schema;

/**
 * @group db
 * @group sqlite
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'sqlite';

    public function testAutoQuoting()
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
    public function testUpsert(array $firstData, array $secondData)
    {
        if (version_compare($this->getConnection(false)->getServerVersion(), '3.8.3', '<')) {
            $this->markTestSkipped('SQLite < 3.8.3 does not support "WITH" keyword.');
            return;
        }

        parent::testUpsert($firstData, $secondData);
    }

    public function testAddDropPrimaryKey()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping primary keys.');
    }

    public function testAddDropForeignKey()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping foreign keys.');
    }

    public function testAddDropUnique()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping unique constraints.');
    }

    public function testAddDropCheck()
    {
        $this->markTestSkipped('SQLite does not support adding/dropping check constraints.');
    }

    public function testMultiStatementSupport()
    {
        $db = $this->getConnection(false);
        $sql = <<<'SQL'
DROP TABLE IF EXISTS {{T_multistatement}};
CREATE TABLE {{T_multistatement}} (
    [[intcol]] INTEGER,
    [[textcol]] TEXT
);
INSERT INTO {{T_multistatement}} VALUES(41, :val1);
INSERT INTO {{T_multistatement}} VALUES(42, :val2);
SQL;
        $db->createCommand($sql, [
            'val1' => 'foo',
            'val2' => 'bar',
        ])->execute();
        $this->assertSame([
            [
                'intcol' => '41',
                'textcol' => 'foo',
            ],
            [
                'intcol' => '42',
                'textcol' => 'bar',
            ],
        ], $db->createCommand('SELECT * FROM {{T_multistatement}}')->queryAll());
        $sql = <<<'SQL'
UPDATE {{T_multistatement}} SET [[intcol]] = :newInt WHERE [[textcol]] = :val1;
DELETE FROM {{T_multistatement}} WHERE [[textcol]] = :val2;
SELECT * FROM {{T_multistatement}}
SQL;
        $this->assertSame([
            [
                'intcol' => '410',
                'textcol' => 'foo',
            ],
        ], $db->createCommand($sql, [
            'newInt' => 410,
            'val1' => 'foo',
            'val2' => 'bar',
        ])->queryAll());
    }

    public function batchInsertSqlProvider()
    {
        $parent = parent::batchInsertSqlProvider();
        unset($parent['wrongBehavior']); // Produces SQL syntax error: General error: 1 near ".": syntax error

        return $parent;
    }

    public function testResetSequence()
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

    public function testResetSequenceExceptionTableNoExist()
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage('Table not found: no_exist_table');

        $db = $this->getConnection();
        $db->createCommand()->resetSequence('no_exist_table', 5)->execute();
    }

    public function testResetSequenceExceptionSquenceNoExist()
    {
        $this->expectException('yii\base\InvalidArgumentException');
        $this->expectExceptionMessage("There is not sequence associated with table 'type'.");

        $db = $this->getConnection();
        $db->createCommand()->resetSequence('type', 5)->execute();
    }
}

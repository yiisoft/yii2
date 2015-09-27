<?php
namespace yiiunit\framework\db\oci;

use yii\db\oci\Schema;
use yiiunit\framework\db\CommandTest;

/**
 * @group db
 * @group oci
 */
class OracleCommandTest extends CommandTest
{
    protected $driverName = 'oci';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('profile_SEQ'));
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();
        $db->createCommand("BEGIN EXECUTE IMMEDIATE 'DROP TABLE \"testCreateTable\"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;")->execute();

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testCreateTable', ['id' => 1, 'bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}}')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testAlterTable()
    {
        if ($this->driverName === 'sqlite'){
            $this->markTestSkipped('Sqlite does not support alterTable');
        }

        $db = $this->getConnection();
        $db->createCommand("BEGIN EXECUTE IMMEDIATE 'DROP TABLE \"testAlterTable\"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;")->execute();

        $db->createCommand()->createTable('testAlterTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testAlterTable', ['id' => 1, 'bar' => null])->execute();

        $db->createCommand()->alterColumn('testAlterTable', 'bar', Schema::TYPE_STRING)->execute();

        $db->createCommand()->insert('testAlterTable', ['id' => 2, 'bar' => 'hello'])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}}')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => null],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }
}

<?php
namespace yiiunit\framework\db\oci;

use yii\db\Schema;
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
        // on the first run the 'testCreateTable' table does not exist
        try {
            $db->createCommand()->dropTable('testCreateTable')->execute();
        } catch (\Exception $ex) {
            // 'testCreateTable' table does not exist
        }

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testCreateTable', ['bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}};')->queryAll();
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
        // on the first run the 'testAlterTable' table does not exist
        try {
            $db->createCommand()->dropTable('testAlterTable')->execute();
        } catch (\Exception $ex) {
            // 'testAlterTable' table does not exist
        }

        $db->createCommand()->createTable('testAlterTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testAlterTable', ['bar' => 1])->execute();

        $db->createCommand()->alterColumn('testAlterTable', 'bar', Schema::TYPE_STRING)->execute();

        $db->createCommand()->insert('testAlterTable', ['bar' => 'hello'])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }
}

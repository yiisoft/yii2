<?php

namespace yiiunit\framework\db\sqlite;

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
        $this->assertEquals("SELECT `id`, `t`.`name` FROM `customer` t", $command->sql);
    }

    public function testBatchInsertIgnore()
    {
        parent::testBatchInsert();

        $command = $this->getConnection()->createCommand();
        $command->batchInsertIgnore(
            '{{customer}}',
            ['id','email', 'name', 'address'],
            [
                [1,'t1@example.com', 't1', 't1 address'],
                [2,'t2@example.com', null, false],
            ]
        );
        $this->assertEquals(0, $command->execute());
    }


    public function testBatchInsertReplace(){
        $command = $this->getConnection()->createCommand();
        $command->batchInsertReplace(
            '{{customer}}',
            ['id','email', 'name', 'address'],
            [
                [1,'t1@example.com', 't1', 't1 address'],
                [2,'t2@example.com', null, false],
            ]
        );
        $this->assertGreaterThanOrEqual(2, $command->execute());
    }
}

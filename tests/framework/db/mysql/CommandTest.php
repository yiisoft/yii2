<?php

namespace yiiunit\framework\db\mysql;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'mysql';

    public function testBatchInsert()
    {
        parent::testBatchInsert();

        $command = $this->getConnection()->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['id','email', 'name', 'address'],
            [
                [1,'t1@example.com', 't1', 't1 address'],
                [2,'t2@example.com', null, false],
            ],true
        );
        $this->assertEquals(0, $command->execute());

        $command = $this->getConnection()->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['id','email', 'name', 'address'],
            [
                [1,'t1@example.com', 't1', 't1 address'],
                [2,'t2@example.com', null, false],
            ],false,true
        );
        $this->assertGreaterThanOrEqual(2, $command->execute());
    }
}

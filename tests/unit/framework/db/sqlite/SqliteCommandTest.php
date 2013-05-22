<?php

namespace yiiunit\framework\db\sqlite;

class SqliteCommandTest extends \yiiunit\framework\db\CommandTest
{
    protected function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals("SELECT \"id\", 't'.\"name\" FROM 'tbl_customer' t", $command->sql);
    }
}

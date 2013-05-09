<?php

namespace yiiunit\framework\db\sqlite;

class SqliteCommandTest extends \yiiunit\framework\db\CommandTest
{
    public function setUp()
    {
        $this->driverName = 'sqlite';
        parent::setUp();
    }

    function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals("SELECT \"id\", 't'.\"name\" FROM 'tbl_customer' t", $command->sql);
    }
}

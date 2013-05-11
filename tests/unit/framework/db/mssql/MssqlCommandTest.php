<?php

namespace yiiunit\framework\db\mssql;

class MssqlCommandTest extends \yiiunit\framework\db\CommandTest
{
    public function setUp()
    {
        $this->driverName = 'sqlsrv';
        parent::setUp();
    }

	function testAutoQuoting()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
		$command = $db->createCommand($sql);
		$this->assertEquals("SELECT [id], [t].[name] FROM [tbl_customer] t", $command->sql);
	}

	function testPrepareCancel()
	{
		$this->markTestIncomplete();
	}

	function testBindParamValue()
	{
		$this->markTestIncomplete();
	}
}

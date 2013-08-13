<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\CommandTest;

class SqliteCommandTest extends CommandTest
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

<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\CommandTest;

class SqliteCommandTest extends CommandTest
{
	protected $driverName = 'sqlite';

	public function testAutoQuoting()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
		$command = $db->createCommand($sql);
		$this->assertEquals("SELECT \"id\", 't'.\"name\" FROM 'tbl_customer' t", $command->sql);
	}
}

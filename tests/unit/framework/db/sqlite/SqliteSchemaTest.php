<?php
namespace yiiunit\framework\db\sqlite;

use yiiunit\framework\db\SchemaTest;

class SqliteSchemaTest extends SchemaTest
{
	protected $driverName = 'sqlite';

	public function testCompositeFk()
	{
		$this->markTestSkipped('sqlite does not allow getting enough information about composite FK.');
	}
}

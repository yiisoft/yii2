<?php

namespace yiiunit\framework\db\mssql;

use yiiunit\framework\db\ActiveRecordTest;

class MssqlActiveRecordTest extends ActiveRecordTest
{
	protected function setUp()
	{
		$this->driverName = 'sqlsrv';
		parent::setUp();
	}
}

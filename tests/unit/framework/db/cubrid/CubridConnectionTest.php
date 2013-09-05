<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\ConnectionTest;

class CubridConnectionTest extends ConnectionTest
{
	protected function setUp()
	{
		$this->driverName = 'cubrid';
		parent::setUp();
	}
}

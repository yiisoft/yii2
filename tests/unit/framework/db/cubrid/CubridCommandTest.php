<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\CommandTest;

class CubridCommandTest extends CommandTest
{
	protected function setUp()
	{
		$this->driverName = 'cubrid';
		parent::setUp();
	}
}

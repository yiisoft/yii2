<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\QueryTest;

class CubridQueryTest extends QueryTest
{
	protected function setUp()
	{
		$this->driverName = 'cubrid';
		parent::setUp();
	}
}

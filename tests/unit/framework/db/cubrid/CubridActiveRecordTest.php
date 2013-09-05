<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\ActiveRecordTest;

class CubridActiveRecordTest extends ActiveRecordTest
{
	protected function setUp()
	{
		$this->driverName = 'cubrid';
		parent::setUp();
	}
}

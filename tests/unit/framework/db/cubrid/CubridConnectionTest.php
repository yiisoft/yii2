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

	public function testQuoteValue()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
	}
}

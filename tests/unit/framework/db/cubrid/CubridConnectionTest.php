<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\ConnectionTest;

class CubridConnectionTest extends ConnectionTest
{
	public $driverName = 'cubrid';

	public function testQuoteValue()
	{
		$connection = $this->getConnection(false);
		$this->assertEquals(123, $connection->quoteValue(123));
		$this->assertEquals("'string'", $connection->quoteValue('string'));
		$this->assertEquals("'It''s interesting'", $connection->quoteValue("It's interesting"));
	}
}

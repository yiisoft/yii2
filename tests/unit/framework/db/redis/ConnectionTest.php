<?php

namespace yiiunit\framework\db\redis;

use yii\db\redis\Connection;

/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ConnectionTest extends RedisTestCase
{
	public function testConstruct()
	{
		$db = new Connection();
	}

	public function storeGetData()
	{
		return array(
			array(123),
			array(-123),
			array(0),
			array('test'),
			array("test\r\ntest"),
			array(json_encode($this)),
		);
	}

	/**
	 * @dataProvider storeGetData
	 */
	public function testStoreGet($data)
	{
		$db = $this->getConnection(true);

		$db->SET('hi', $data);
		$this->assertEquals($data, $db->GET('hi'));
	}
}
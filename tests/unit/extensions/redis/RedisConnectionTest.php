<?php

namespace yiiunit\extensions\redis;

use yii\redis\Connection;

/**
 * @group redis
 */
class RedisConnectionTest extends RedisTestCase
{
	/**
	 * test connection to redis and selection of db
	 */
	public function testConnect()
	{
		$db = $this->getConnection(false);
		$db->open();
		$this->assertTrue($db->ping());
		$db->set('YIITESTKEY', 'YIITESTVALUE');
		$db->close();

		$db = $this->getConnection(false);
		$db->database = 0;
		$db->open();
		$this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));
		$db->close();

		$db = $this->getConnection(false);
		$db->database = 1;
		$db->open();
		$this->assertNull($db->get('YIITESTKEY'));
		$db->close();
	}

	public function keyValueData()
	{
		return [
			[123],
			[-123],
			[0],
			['test'],
			["test\r\ntest"],
			[''],
		];
	}

	/**
	 * @dataProvider keyValueData
	 */
	public function testStoreGet($data)
	{
		$db = $this->getConnection(true);

		$db->set('hi', $data);
		$this->assertEquals($data, $db->get('hi'));
	}
}

<?php

namespace yiiunit\framework\redis;

use yii\redis\Connection;

class RedisConnectionTest extends RedisTestCase
{
	/**
	 * Empty DSN should throw exception
	 * @expectedException \yii\base\InvalidConfigException
	 */
	public function testEmptyDSN()
	{
		$db = new Connection();
		$db->open();
	}

	/**
	 * test connection to redis and selection of db
	 */
	public function testConnect()
	{
		$db = new Connection();
		$db->dsn = 'redis://localhost:6379';
		$db->open();
		$this->assertTrue($db->ping());
		$db->set('YIITESTKEY', 'YIITESTVALUE');
		$db->close();

		$db = new Connection();
		$db->dsn = 'redis://localhost:6379/0';
		$db->open();
		$this->assertEquals('YIITESTVALUE', $db->get('YIITESTKEY'));
		$db->close();

		$db = new Connection();
		$db->dsn = 'redis://localhost:6379/1';
		$db->open();
		$this->assertNull($db->get('YIITESTKEY'));
		$db->close();
	}

	public function keyValueData()
	{
		return array(
			array(123),
			array(-123),
			array(0),
			array('test'),
			array("test\r\ntest"),
			array(''),
		);
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
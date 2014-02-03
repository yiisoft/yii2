<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\test;

use yii\test\ActiveFixture;
use yiiunit\data\ar\ActiveRecord;
use yii\test\FixtureTrait;
use yii\test\InitDbFixture;
use yiiunit\data\ar\Customer;
use yiiunit\framework\db\DatabaseTestCase;

class CustomerFixture extends ActiveFixture
{
	public $modelClass = 'yiiunit\data\ar\Customer';
}

class MyDbTestCase
{
	use FixtureTrait;

	public function setUp()
	{
		$this->loadFixtures();
	}

	public function tearDown()
	{
		$this->unloadFixtures();
	}

	public function fixtures()
	{
		return [
			'customers' => CustomerFixture::className(),
		];
	}

	public function globalFixtures()
	{
		return [
			InitDbFixture::className(),
		];
	}
}

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFixtureTest extends DatabaseTestCase
{
	public function setUp()
	{
		parent::setUp();
		\Yii::$app->setComponent('db', $this->getConnection());
		ActiveRecord::$db = $this->getConnection();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function testGetData()
	{
		$test = new MyDbTestCase();
		$test->setUp();
		$fixture = $test->getFixture('customers');
		$this->assertEquals(CustomerFixture::className(), get_class($fixture));
		$this->assertEquals(2, count($fixture));
		$this->assertEquals(1, $fixture['customer1']['id']);
		$this->assertEquals('customer1@example.com', $fixture['customer1']['email']);
		$this->assertEquals(2, $fixture['customer2']['id']);
		$this->assertEquals('customer2@example.com', $fixture['customer2']['email']);
		$test->tearDown();
	}

	public function testGetModel()
	{
		$test = new MyDbTestCase();
		$test->setUp();
		$fixture = $test->getFixture('customers');
		$this->assertEquals(Customer::className(), get_class($fixture->getModel('customer1')));
		$this->assertEquals(1, $fixture->getModel('customer1')->id);
		$this->assertEquals('customer1@example.com', $fixture->getModel('customer1')->email);
		$this->assertEquals(2, $fixture->getModel('customer2')->id);
		$this->assertEquals('customer2@example.com', $fixture->getModel('customer2')->email);
		$test->tearDown();
	}
}

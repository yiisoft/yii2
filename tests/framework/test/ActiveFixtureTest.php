<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\test;

use yii\test\ActiveFixture;
use yii\test\FixtureTrait;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\framework\db\DatabaseTestCase;

class ProfileFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Profile';
}

class CustomerFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Customer';

    public $depends = [
        'yiiunit\framework\test\ProfileFixture',
    ];
}

class OrderFixture extends ActiveFixture
{
    public $modelClass = 'yiiunit\data\ar\Order';

    public $dataExtra = [
        'order_item.php'
    ];
}

class BaseDbTestCase {

    use FixtureTrait;

    public function setUp()
    {
        $this->initFixtures();
    }

    public function tearDown()
    {
    }

}

class MyDbTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'customers' => CustomerFixture::className()
        ];
    }
}

class ExtraDataTestCase extends BaseDbTestCase
{
    public function fixtures()
    {
        return [
            'customers' => CustomerFixture::className(),
            'orders' => OrderFixture::className()
        ];
    }

}

/**
 * @group fixture
 * @group db
 */
class ActiveFixtureTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function setUp()
    {
        parent::setUp();
        $db = $this->getConnection();
        \Yii::$app->set('db', $db);
        ActiveRecord::$db = $db;
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
        $this->assertCount(2, $fixture);
        $this->assertEquals(1, $fixture['customer1']['id']);
        $this->assertEquals('customer1@example.com', $fixture['customer1']['email']);
        $this->assertEquals(1, $fixture['customer1']['profile_id']);

        $this->assertEquals(2, $fixture['customer2']['id']);
        $this->assertEquals('customer2@example.com', $fixture['customer2']['email']);
        $this->assertEquals(2, $fixture['customer2']['profile_id']);

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
        $this->assertEquals(1, $fixture['customer1']['profile_id']);

        $this->assertEquals(2, $fixture->getModel('customer2')->id);
        $this->assertEquals('customer2@example.com', $fixture->getModel('customer2')->email);
        $this->assertEquals(2, $fixture['customer2']['profile_id']);

        $test->tearDown();
    }

    public function testDataExtra()
    {
        $test = new ExtraDataTestCase();
        $test->setUp();

        $fixture = $test->getFixture('orders');
        $items = $fixture->getModel('order1')->items;

        $this->assertCount(2, $items);
        $this->assertEquals('Agile Web Application Development with Yii1.1 and PHP5', $items[0]['name']);
        $this->assertEquals(1, $items[0]['category_id']);

        $this->assertEquals('Ice Age', $items[1]['name']);
        $this->assertEquals(2, $items[1]['category_id']);

        $test->tearDown();
    }
}

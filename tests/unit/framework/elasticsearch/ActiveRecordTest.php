<?php

namespace yiiunit\framework\elasticsearch;

use yii\elasticsearch\Connection;
use yii\elasticsearch\ActiveQuery;
use yiiunit\framework\ar\ActiveRecordTestTrait;
use yiiunit\data\ar\elasticsearch\ActiveRecord;
use yiiunit\data\ar\elasticsearch\Customer;
use yiiunit\data\ar\elasticsearch\OrderItem;
use yiiunit\data\ar\elasticsearch\Order;
use yiiunit\data\ar\elasticsearch\Item;

class ActiveRecordTest extends ElasticSearchTestCase
{
	use ActiveRecordTestTrait;

	public function callCustomerFind($q = null)	 { return Customer::find($q); }
	public function callOrderFind($q = null)     { return Order::find($q); }
	public function callOrderItemFind($q = null) { return OrderItem::find($q); }
	public function callItemFind($q = null)      { return Item::find($q); }

	public function getCustomerClass() { return Customer::className(); }
	public function getItemClass() { return Item::className(); }
	public function getOrderClass() { return Order::className(); }
	public function getOrderItemClass() { return OrderItem::className(); }

	/**
	 * can be overridden to do things after save()
	 */
	public function afterSave()
	{
		$this->getConnection()->createCommand()->flushIndex();
	}

	public function setUp()
	{
		parent::setUp();

		/** @var Connection $db */
		$db = ActiveRecord::$db = $this->getConnection();

		// delete all indexes
		$db->http()->delete('_all')->send();

		$customer = new Customer();
		$customer->primaryKey = 1;
		$customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1], false);
		$customer->save(false);
		$customer = new Customer();
		$customer->primaryKey = 2;
		$customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1], false);
		$customer->save(false);
		$customer = new Customer();
		$customer->primaryKey = 3;
		$customer->setAttributes(['email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2], false);
		$customer->save(false);

//		INSERT INTO tbl_category (name) VALUES ('Books');
//		INSERT INTO tbl_category (name) VALUES ('Movies');

		$item = new Item();
		$item->primaryKey = 1;
		$item->setAttributes(['name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1], false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 2;
		$item->setAttributes(['name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1], false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 3;
		$item->setAttributes(['name' => 'Ice Age', 'category_id' => 2], false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 4;
		$item->setAttributes(['name' => 'Toy Story', 'category_id' => 2], false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 5;
		$item->setAttributes(['name' => 'Cars', 'category_id' => 2], false);
		$item->save(false);

		$order = new Order();
		$order->primaryKey = 1;
		$order->setAttributes(['customer_id' => 1, 'create_time' => 1325282384, 'total' => 110.0], false);
		$order->save(false);
		$order = new Order();
		$order->primaryKey = 2;
		$order->setAttributes(['customer_id' => 2, 'create_time' => 1325334482, 'total' => 33.0], false);
		$order->save(false);
		$order = new Order();
		$order->primaryKey = 3;
		$order->setAttributes(['customer_id' => 2, 'create_time' => 1325502201, 'total' => 40.0], false);
		$order->save(false);

		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
		$orderItem->save(false);

		Customer::getDb()->createCommand()->flushIndex();
	}

	public function testFind()
	{
		// find one
		$result = Customer::find();
		$this->assertTrue($result instanceof ActiveQuery);
		$customer = $result->one();
		$this->assertTrue($customer instanceof Customer);

		// find all
		$customers = Customer::find()->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers[0] instanceof Customer);
		$this->assertTrue($customers[1] instanceof Customer);
		$this->assertTrue($customers[2] instanceof Customer);

		// find all asArray
		$customers = Customer::find()->asArray()->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('primaryKey', $customers[0]);
		$this->assertArrayHasKey('name', $customers[0]);
		$this->assertArrayHasKey('email', $customers[0]);
		$this->assertArrayHasKey('address', $customers[0]);
		$this->assertArrayHasKey('status', $customers[0]);
		$this->assertArrayHasKey('primaryKey', $customers[1]);
		$this->assertArrayHasKey('name', $customers[1]);
		$this->assertArrayHasKey('email', $customers[1]);
		$this->assertArrayHasKey('address', $customers[1]);
		$this->assertArrayHasKey('status', $customers[1]);
		$this->assertArrayHasKey('primaryKey', $customers[2]);
		$this->assertArrayHasKey('name', $customers[2]);
		$this->assertArrayHasKey('email', $customers[2]);
		$this->assertArrayHasKey('address', $customers[2]);
		$this->assertArrayHasKey('status', $customers[2]);

		// find by a single primary key
		$customer = Customer::find(2);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(5);
		$this->assertNull($customer);

		// query scalar
		$customerName = Customer::find()->where(['status' => 2])->scalar('name');
		$this->assertEquals('user3', $customerName);
		$customerName = Customer::find()->where(['status' => 2])->scalar('noname');
		$this->assertNull($customerName);
		$customerId = Customer::find()->where(['status' => 2])->scalar('primaryKey');
		$this->assertEquals(3, $customerId);

		// find by column values
		$customer = Customer::find(['name' => 'user2']);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(['name' => 'user1', 'primaryKey' => 2]);
		$this->assertNull($customer);
		$customer = Customer::find(['primaryKey' => 5]);
		$this->assertNull($customer);
		$customer = Customer::find(['name' => 'user5']);
		$this->assertNull($customer);

		// find by attributes
		$customer = Customer::find()->where(['name' => 'user2'])->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);

		// find count
		$this->assertEquals(3, Customer::find()->count());
		$this->assertEquals(2, Customer::find()->where(['or', ['primaryKey' => 1], ['primaryKey' => 2]])->count());
//		$this->assertEquals(6, Customer::find()->sum('id'));
//		$this->assertEquals(2, Customer::find()->average('id'));
//		$this->assertEquals(1, Customer::find()->min('id'));
//		$this->assertEquals(3, Customer::find()->max('id'));

		// scope
		$this->assertEquals(2, count(Customer::find()->active()->all()));
//		$this->assertEquals(2, Customer::find()->active()->count());

		// asArray
		$customer = Customer::find()->where(['name' => 'user2'])->asArray()->one();
		$this->assertEquals(array(
			'email' => 'user2@example.com',
			'name' => 'user2',
			'address' => 'address2',
			'status' => '1',
			'primaryKey' => 2,
		), $customer);

		// indexBy
		$customers = Customer::find()->indexBy('name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['user1'] instanceof Customer);
		$this->assertTrue($customers['user2'] instanceof Customer);
		$this->assertTrue($customers['user3'] instanceof Customer);

		// indexBy callable
		$customers = Customer::find()->indexBy(function ($customer) {
			return $customer->status . '-' . $customer->name;
		})->orderBy('name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['1-user1'] instanceof Customer);
		$this->assertTrue($customers['1-user2'] instanceof Customer);
		$this->assertTrue($customers['2-user3'] instanceof Customer);
	}

	public function testGetDb()
	{
		$this->mockApplication(['components' => ['elasticsearch' => Connection::className()]]);
		$this->assertInstanceOf(Connection::className(), ActiveRecord::getDb());
	}

	public function testGet()
	{
		$this->assertInstanceOf(Customer::className(), Customer::get(1));
		$this->assertNull(Customer::get(5));
	}

	public function testMget()
	{
		$this->assertEquals([], Customer::mget([]));

		$records = Customer::mget([1]);
		$this->assertEquals(1, count($records));
		$this->assertInstanceOf(Customer::className(), reset($records));

		$records = Customer::mget([5]);
		$this->assertEquals(0, count($records));

		$records = Customer::mget([1,3,5]);
		$this->assertEquals(2, count($records));
		$this->assertInstanceOf(Customer::className(), $records[0]);
		$this->assertInstanceOf(Customer::className(), $records[1]);
	}

	public function testFindLazy()
	{
		/** @var $customer Customer */
		$customer = Customer::find(2);
		$orders = $customer->orders;
		$this->assertEquals(2, count($orders));

		$orders = $customer->getOrders()->where(['between', 'create_time', 1325334000, 1325400000])->all();
		$this->assertEquals(1, count($orders));
		$this->assertEquals(2, $orders[0]->primaryKey);
	}

	public function testFindEagerViaRelation()
	{
		// this test is currently failing randomly because of https://github.com/yiisoft/yii2/issues/1310
		$orders = Order::find()->with('items')->orderBy('create_time')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->primaryKey);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->primaryKey);
		$this->assertEquals(2, $order->items[1]->primaryKey);
	}


	public function testInsertNoPk()
	{
		$this->assertEquals(['primaryKey'], Customer::primaryKey());

		$customer = new Customer;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->primaryKey);
		$this->assertNull($customer->oldPrimaryKey);
		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertNotNull($customer->primaryKey);
		$this->assertNotNull($customer->oldPrimaryKey);
		$this->assertEquals($customer->primaryKey, $customer->oldPrimaryKey);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testInsertPk()
	{
		$customer = new Customer;
		$customer->primaryKey = 5;
		$customer->email = 'user5@example.com';
		$customer->name = 'user5';
		$customer->address = 'address5';

		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertEquals(5, $customer->primaryKey);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testUpdatePk()
	{
		$pk = ['primaryKey' => 2];
		$orderItem = Order::find($pk);
		$this->assertEquals(2, $orderItem->primaryKey);

		$this->setExpectedException('yii\base\InvalidCallException');
		$orderItem->primaryKey = 13;
		$orderItem->save();
	}
}
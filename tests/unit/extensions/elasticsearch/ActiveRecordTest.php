<?php

namespace yiiunit\extensions\elasticsearch;

use yii\elasticsearch\Connection;
use yii\helpers\Json;
use yiiunit\framework\ar\ActiveRecordTestTrait;
use yiiunit\data\ar\elasticsearch\ActiveRecord;
use yiiunit\data\ar\elasticsearch\Customer;
use yiiunit\data\ar\elasticsearch\OrderItem;
use yiiunit\data\ar\elasticsearch\Order;
use yiiunit\data\ar\elasticsearch\Item;

/**
 * @group elasticsearch
 */
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
		$this->getConnection()->createCommand()->flushIndex('yiitest');
	}

	public function setUp()
	{
		parent::setUp();

		/** @var Connection $db */
		$db = ActiveRecord::$db = $this->getConnection();

		// delete index
		if ($db->createCommand()->indexExists('yiitest')) {
			$db->createCommand()->deleteIndex('yiitest');
		}
		$db->createCommand()->createIndex('yiitest');

		$command = $db->createCommand();
		Customer::setUpMapping($command);
		Item::setUpMapping($command);
		Order::setUpMapping($command);
		OrderItem::setUpMapping($command);

		$db->createCommand()->flushIndex('yiitest');

		$customer = new Customer();
		$customer->id = 1;
		$customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1], false);
		$customer->save(false);
		$customer = new Customer();
		$customer->id = 2;
		$customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1], false);
		$customer->save(false);
		$customer = new Customer();
		$customer->id = 3;
		$customer->setAttributes(['email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2], false);
		$customer->save(false);

//		INSERT INTO tbl_category (name) VALUES ('Books');
//		INSERT INTO tbl_category (name) VALUES ('Movies');

		$item = new Item();
		$item->id = 1;
		$item->setAttributes(['name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1], false);
		$item->save(false);
		$item = new Item();
		$item->id = 2;
		$item->setAttributes(['name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1], false);
		$item->save(false);
		$item = new Item();
		$item->id = 3;
		$item->setAttributes(['name' => 'Ice Age', 'category_id' => 2], false);
		$item->save(false);
		$item = new Item();
		$item->id = 4;
		$item->setAttributes(['name' => 'Toy Story', 'category_id' => 2], false);
		$item->save(false);
		$item = new Item();
		$item->id = 5;
		$item->setAttributes(['name' => 'Cars', 'category_id' => 2], false);
		$item->save(false);

		$order = new Order();
		$order->id = 1;
		$order->setAttributes(['customer_id' => 1, 'create_time' => 1325282384, 'total' => 110.0], false);
		$order->save(false);
		$order = new Order();
		$order->id = 2;
		$order->setAttributes(['customer_id' => 2, 'create_time' => 1325334482, 'total' => 33.0], false);
		$order->save(false);
		$order = new Order();
		$order->id = 3;
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

		$db->createCommand()->flushIndex('yiitest');
	}

	public function testFindAsArray()
	{
		// asArray
		$customer = $this->callCustomerFind()->where(['id' => 2])->asArray()->one();
		$this->assertEquals([
			'id' => 2,
			'email' => 'user2@example.com',
			'name' => 'user2',
			'address' => 'address2',
			'status' => 1,
			'_score' => 1.0
		], $customer);
	}

	public function testSearch()
	{
		$customers = $this->callCustomerFind()->search()['hits'];
		$this->assertEquals(3, $customers['total']);
		$this->assertEquals(3, count($customers['hits']));
		$this->assertTrue($customers['hits'][0] instanceof Customer);
		$this->assertTrue($customers['hits'][1] instanceof Customer);
		$this->assertTrue($customers['hits'][2] instanceof Customer);

		// limit vs. totalcount
		$customers = $this->callCustomerFind()->limit(2)->search()['hits'];
		$this->assertEquals(3, $customers['total']);
		$this->assertEquals(2, count($customers['hits']));

		// asArray
		$result = $this->callCustomerFind()->asArray()->search()['hits'];
		$this->assertEquals(3, $result['total']);
		$customers = $result['hits'];
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers[0]);
		$this->assertArrayHasKey('name', $customers[0]);
		$this->assertArrayHasKey('email', $customers[0]);
		$this->assertArrayHasKey('address', $customers[0]);
		$this->assertArrayHasKey('status', $customers[0]);
		$this->assertArrayHasKey('id', $customers[1]);
		$this->assertArrayHasKey('name', $customers[1]);
		$this->assertArrayHasKey('email', $customers[1]);
		$this->assertArrayHasKey('address', $customers[1]);
		$this->assertArrayHasKey('status', $customers[1]);
		$this->assertArrayHasKey('id', $customers[2]);
		$this->assertArrayHasKey('name', $customers[2]);
		$this->assertArrayHasKey('email', $customers[2]);
		$this->assertArrayHasKey('address', $customers[2]);
		$this->assertArrayHasKey('status', $customers[2]);

		// TODO test asArray() + fields() + indexBy()

		// find by attributes
		$result = $this->callCustomerFind()->where(['name' => 'user2'])->search()['hits'];
		$customer = reset($result['hits']);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(2, $customer->id);

		// TODO test query() and filter()
	}

	public function testSearchFacets()
	{
		$result = $this->callCustomerFind()->addStatisticalFacet('status_stats', ['field' => 'status'])->search();
		$this->assertArrayHasKey('facets', $result);
		$this->assertEquals(3, $result['facets']['status_stats']['count']);
		$this->assertEquals(4, $result['facets']['status_stats']['total']); // sum of values
		$this->assertEquals(1, $result['facets']['status_stats']['min']);
		$this->assertEquals(2, $result['facets']['status_stats']['max']);
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
		$this->assertEquals(2, $orders[0]->id);
	}

	public function testFindEagerViaRelation()
	{
		$orders = Order::find()->with('items')->orderBy('create_time')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertTrue($order->isRelationPopulated('items'));
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testInsertNoPk()
	{
		$this->assertEquals(['id'], Customer::primaryKey());
		$pkName = 'id';

		$customer = new Customer;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->primaryKey);
		$this->assertNull($customer->oldPrimaryKey);
		$this->assertNull($customer->$pkName);
		$this->assertTrue($customer->isNewRecord);

		$customer->save();
		$this->afterSave();

		$this->assertNotNull($customer->primaryKey);
		$this->assertNotNull($customer->oldPrimaryKey);
		$this->assertNotNull($customer->$pkName);
		$this->assertEquals($customer->primaryKey, $customer->oldPrimaryKey);
		$this->assertEquals($customer->primaryKey, $customer->$pkName);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testInsertPk()
	{
		$pkName = 'id';

		$customer = new Customer;
		$customer->$pkName = 5;
		$customer->email = 'user5@example.com';
		$customer->name = 'user5';
		$customer->address = 'address5';

		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertEquals(5, $customer->primaryKey);
		$this->assertEquals(5, $customer->oldPrimaryKey);
		$this->assertEquals(5, $customer->$pkName);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testUpdatePk()
	{
		$pkName = 'id';

		$orderItem = Order::find([$pkName => 2]);
		$this->assertEquals(2, $orderItem->primaryKey);
		$this->assertEquals(2, $orderItem->oldPrimaryKey);
		$this->assertEquals(2, $orderItem->$pkName);

//		$this->setExpectedException('yii\base\InvalidCallException');
		$orderItem->$pkName = 13;
		$this->assertEquals(13, $orderItem->primaryKey);
		$this->assertEquals(2, $orderItem->oldPrimaryKey);
		$this->assertEquals(13, $orderItem->$pkName);
		$orderItem->save();
		$this->afterSave();
		$this->assertEquals(13, $orderItem->primaryKey);
		$this->assertEquals(13, $orderItem->oldPrimaryKey);
		$this->assertEquals(13, $orderItem->$pkName);

		$this->assertNull(Order::find([$pkName => 2]));
		$this->assertNotNull(Order::find([$pkName => 13]));
	}

	public function testFindLazyVia2()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		/** @var Order $order */
		$orderClass = $this->getOrderClass();
		$pkName = 'id';

		$order = new $orderClass();
		$order->$pkName = 100;
		$this->assertEquals([], $order->items);
	}

	/**
	 * Some PDO implementations(e.g. cubrid) do not support boolean values.
	 * Make sure this does not affect AR layer.
	 */
	public function testBooleanAttribute()
	{
		$db = $this->getConnection();
		Customer::setUpMapping($db->createCommand(), true);
		Customer::deleteAll();

		$customerClass = $this->getCustomerClass();
		$customer = new $customerClass();
		$customer->name = 'boolean customer';
		$customer->email = 'mail@example.com';
		$customer->status = true;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(true, $customer->status);

		$customer->status = false;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(false, $customer->status);

		$customer = new Customer();
		$customer->setAttributes(['email' => 'user2b@example.com', 'name' => 'user2b', 'status' => true], false);
		$customer->save(false);
		$customer = new Customer();
		$customer->setAttributes(['email' => 'user3b@example.com', 'name' => 'user3b', 'status' => false], false);
		$customer->save(false);
		$this->afterSave();

		$customers = $this->callCustomerFind()->where(['status' => true])->all();
		$this->assertEquals(1, count($customers));

		$customers = $this->callCustomerFind()->where(['status' => false])->all();
		$this->assertEquals(2, count($customers));
	}

	public function testfindAsArrayFields()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// indexBy + asArray
		$customers = $this->callCustomerFind()->asArray()->fields(['id', 'name'])->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers[0]);
		$this->assertArrayHasKey('name', $customers[0]);
		$this->assertArrayNotHasKey('email', $customers[0]);
		$this->assertArrayNotHasKey('address', $customers[0]);
		$this->assertArrayNotHasKey('status', $customers[0]);
		$this->assertArrayHasKey('id', $customers[1]);
		$this->assertArrayHasKey('name', $customers[1]);
		$this->assertArrayNotHasKey('email', $customers[1]);
		$this->assertArrayNotHasKey('address', $customers[1]);
		$this->assertArrayNotHasKey('status', $customers[1]);
		$this->assertArrayHasKey('id', $customers[2]);
		$this->assertArrayHasKey('name', $customers[2]);
		$this->assertArrayNotHasKey('email', $customers[2]);
		$this->assertArrayNotHasKey('address', $customers[2]);
		$this->assertArrayNotHasKey('status', $customers[2]);
	}

	public function testfindIndexByFields()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// indexBy + asArray
		$customers = $this->callCustomerFind()->indexBy('name')->fields('id', 'name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['user1'] instanceof $customerClass);
		$this->assertTrue($customers['user2'] instanceof $customerClass);
		$this->assertTrue($customers['user3'] instanceof $customerClass);
		$this->assertNotNull($customers['user1']->id);
		$this->assertNotNull($customers['user1']->name);
		$this->assertNull($customers['user1']->email);
		$this->assertNull($customers['user1']->address);
		$this->assertNull($customers['user1']->status);
		$this->assertNotNull($customers['user2']->id);
		$this->assertNotNull($customers['user2']->name);
		$this->assertNull($customers['user2']->email);
		$this->assertNull($customers['user2']->address);
		$this->assertNull($customers['user2']->status);
		$this->assertNotNull($customers['user3']->id);
		$this->assertNotNull($customers['user3']->name);
		$this->assertNull($customers['user3']->email);
		$this->assertNull($customers['user3']->address);
		$this->assertNull($customers['user3']->status);

		// indexBy callable + asArray
		$customers = $this->callCustomerFind()->indexBy(function ($customer) {
			return $customer->id . '-' . $customer->name;
		})->fields('id', 'name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['1-user1'] instanceof $customerClass);
		$this->assertTrue($customers['2-user2'] instanceof $customerClass);
		$this->assertTrue($customers['3-user3'] instanceof $customerClass);
		$this->assertNotNull($customers['1-user1']->id);
		$this->assertNotNull($customers['1-user1']->name);
		$this->assertNull($customers['1-user1']->email);
		$this->assertNull($customers['1-user1']->address);
		$this->assertNull($customers['1-user1']->status);
		$this->assertNotNull($customers['2-user2']->id);
		$this->assertNotNull($customers['2-user2']->name);
		$this->assertNull($customers['2-user2']->email);
		$this->assertNull($customers['2-user2']->address);
		$this->assertNull($customers['2-user2']->status);
		$this->assertNotNull($customers['3-user3']->id);
		$this->assertNotNull($customers['3-user3']->name);
		$this->assertNull($customers['3-user3']->email);
		$this->assertNull($customers['3-user3']->address);
		$this->assertNull($customers['3-user3']->status);
	}

	public function testfindIndexByAsArrayFields()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// indexBy + asArray
		$customers = $this->callCustomerFind()->indexBy('name')->asArray()->fields('id', 'name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers['user1']);
		$this->assertArrayHasKey('name', $customers['user1']);
		$this->assertArrayNotHasKey('email', $customers['user1']);
		$this->assertArrayNotHasKey('address', $customers['user1']);
		$this->assertArrayNotHasKey('status', $customers['user1']);
		$this->assertArrayHasKey('id', $customers['user2']);
		$this->assertArrayHasKey('name', $customers['user2']);
		$this->assertArrayNotHasKey('email', $customers['user2']);
		$this->assertArrayNotHasKey('address', $customers['user2']);
		$this->assertArrayNotHasKey('status', $customers['user2']);
		$this->assertArrayHasKey('id', $customers['user3']);
		$this->assertArrayHasKey('name', $customers['user3']);
		$this->assertArrayNotHasKey('email', $customers['user3']);
		$this->assertArrayNotHasKey('address', $customers['user3']);
		$this->assertArrayNotHasKey('status', $customers['user3']);

		// indexBy callable + asArray
		$customers = $this->callCustomerFind()->indexBy(function ($customer) {
			return $customer['id'] . '-' . $customer['name'];
		})->asArray()->fields('id', 'name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers['1-user1']);
		$this->assertArrayHasKey('name', $customers['1-user1']);
		$this->assertArrayNotHasKey('email', $customers['1-user1']);
		$this->assertArrayNotHasKey('address', $customers['1-user1']);
		$this->assertArrayNotHasKey('status', $customers['1-user1']);
		$this->assertArrayHasKey('id', $customers['2-user2']);
		$this->assertArrayHasKey('name', $customers['2-user2']);
		$this->assertArrayNotHasKey('email', $customers['2-user2']);
		$this->assertArrayNotHasKey('address', $customers['2-user2']);
		$this->assertArrayNotHasKey('status', $customers['2-user2']);
		$this->assertArrayHasKey('id', $customers['3-user3']);
		$this->assertArrayHasKey('name', $customers['3-user3']);
		$this->assertArrayNotHasKey('email', $customers['3-user3']);
		$this->assertArrayNotHasKey('address', $customers['3-user3']);
		$this->assertArrayNotHasKey('status', $customers['3-user3']);
	}


}
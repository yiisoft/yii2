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

		$db->post(['yiitest'], [], Json::encode([
			'mappings' => [
				"item" => [
		            "_source" => [ "enabled" => true ],
		            "properties" => [
						// allow proper sorting by name
		                "name" => ["type" => "string", "index" => "not_analyzed"],
		            ]
		        ]
			],
		]));

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
		// this test is currently failing randomly because of https://github.com/yiisoft/yii2/issues/1310
		$orders = Order::find()->with('items')->orderBy('create_time')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testInsertNoPk()
	{
		$this->assertEquals([ActiveRecord::PRIMARY_KEY_NAME], Customer::primaryKey());
		$pkName = ActiveRecord::PRIMARY_KEY_NAME;

		$customer = new Customer;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->primaryKey);
		$this->assertNull($customer->oldPrimaryKey);
		$this->assertNull($customer->$pkName);
		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertNotNull($customer->primaryKey);
		$this->assertNotNull($customer->oldPrimaryKey);
		$this->assertNotNull($customer->$pkName);
		$this->assertEquals($customer->primaryKey, $customer->oldPrimaryKey);
		$this->assertEquals($customer->primaryKey, $customer->$pkName);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testInsertPk()
	{
		$pkName = ActiveRecord::PRIMARY_KEY_NAME;

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
		$pkName = ActiveRecord::PRIMARY_KEY_NAME;

		$pk = [$pkName => 2];
		$orderItem = Order::find($pk);
		$this->assertEquals(2, $orderItem->primaryKey);
		$this->assertEquals(2, $orderItem->oldPrimaryKey);
		$this->assertEquals(2, $orderItem->$pkName);

		$this->setExpectedException('yii\base\InvalidCallException');
		$orderItem->$pkName = 13;
		$orderItem->save();
	}

	public function testFindLazyVia2()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		/** @var Order $order */
		$orderClass = $this->getOrderClass();
		$pkName = ActiveRecord::PRIMARY_KEY_NAME;

		$order = new $orderClass();
		$order->$pkName = 100;
		$this->assertEquals([], $order->items);
	}

	public function testUpdateCounters()
	{
		// Update Counters is not supported by elasticsearch
//		$this->setExpectedException('yii\base\NotSupportedException');
//		ActiveRecordTestTrait::testUpdateCounters();
	}

	/**
	 * Some PDO implementations(e.g. cubrid) do not support boolean values.
	 * Make sure this does not affect AR layer.
	 */
	public function testBooleanAttribute()
	{
		$db = $this->getConnection();
		$db->createCommand()->deleteIndex('yiitest');
		$db->post(['yiitest'], [], Json::encode([
			'mappings' => [
				"customer" => [
		            "_source" => [ "enabled" => true ],
		            "properties" => [
						// this is for the boolean test
		                "status" => ["type" => "boolean"],
		            ]
		        ]
			],
		]));

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
}
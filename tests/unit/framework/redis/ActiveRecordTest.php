<?php

namespace yiiunit\framework\redis;

use yii\db\Query;
use yii\redis\ActiveQuery;
use yiiunit\data\ar\redis\ActiveRecord;
use yiiunit\data\ar\redis\Customer;
use yiiunit\data\ar\redis\OrderItem;
use yiiunit\data\ar\redis\Order;
use yiiunit\data\ar\redis\Item;

class ActiveRecordTest extends RedisTestCase
{
	public function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();

		$customer = new Customer();
		$customer->setAttributes(array('email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1), false);
		$customer->save(false);
		$customer = new Customer();
		$customer->setAttributes(array('email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1), false);
		$customer->save(false);
		$customer = new Customer();
		$customer->setAttributes(array('email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2), false);
		$customer->save(false);

//		INSERT INTO tbl_category (name) VALUES ('Books');
//		INSERT INTO tbl_category (name) VALUES ('Movies');

		$item = new Item();
		$item->setAttributes(array('name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1), false);
		$item->save(false);
		$item = new Item();
		$item->setAttributes(array('name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1), false);
		$item->save(false);
		$item = new Item();
		$item->setAttributes(array('name' => 'Ice Age', 'category_id' => 2), false);
		$item->save(false);
		$item = new Item();
		$item->setAttributes(array('name' => 'Toy Story', 'category_id' => 2), false);
		$item->save(false);
		$item = new Item();
		$item->setAttributes(array('name' => 'Cars', 'category_id' => 2), false);
		$item->save(false);

		$order = new Order();
		$order->setAttributes(array('customer_id' => 1, 'create_time' => 1325282384, 'total' => 110.0), false);
		$order->save(false);
		$order = new Order();
		$order->setAttributes(array('customer_id' => 2, 'create_time' => 1325334482, 'total' => 33.0), false);
		$order->save(false);
		$order = new Order();
		$order->setAttributes(array('customer_id' => 2, 'create_time' => 1325502201, 'total' => 40.0), false);
		$order->save(false);

		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0), false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0), false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0), false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0), false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0), false);
		$orderItem->save(false);
		$orderItem = new OrderItem();
		$orderItem->setAttributes(array('order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0), false);
		$orderItem->save(false);
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

		// find by a single primary key
		$customer = Customer::find(2);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(5);
		$this->assertNull($customer);

		// query scalar
		$customerName = Customer::find()->where(array('id' => 2))->scalar('name');
		$this->assertEquals('user2', $customerName);

		// find by column values
		$customer = Customer::find(array('id' => 2, 'name' => 'user2'));
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(array('id' => 2, 'name' => 'user1'));
		$this->assertNull($customer);
		$customer = Customer::find(array('id' => 5));
		$this->assertNull($customer);

		// find by attributes
		$customer = Customer::find()->where(array('name' => 'user2'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(2, $customer->id);

		// find count, sum, average, min, max, scalar
		$this->assertEquals(3, Customer::find()->count());
		$this->assertEquals(6, Customer::find()->sum('id'));
		$this->assertEquals(2, Customer::find()->average('id'));
		$this->assertEquals(1, Customer::find()->min('id'));
		$this->assertEquals(3, Customer::find()->max('id'));

		// scope
		$this->assertEquals(2, Customer::find()->active()->count());

		// asArray
		$customer = Customer::find()->where(array('id' => 2))->asArray()->one();
		$this->assertEquals(array(
			'id' => '2',
			'email' => 'user2@example.com',
			'name' => 'user2',
			'address' => 'address2',
			'status' => '1',
		), $customer);

		// indexBy
		$customers = Customer::find()->indexBy('name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['user1'] instanceof Customer);
		$this->assertTrue($customers['user2'] instanceof Customer);
		$this->assertTrue($customers['user3'] instanceof Customer);

		// indexBy callable
		$customers = Customer::find()->indexBy(function ($customer) {
			return $customer->id . '-' . $customer->name;
//		})->orderBy('id')->all();
		})->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['1-user1'] instanceof Customer);
		$this->assertTrue($customers['2-user2'] instanceof Customer);
		$this->assertTrue($customers['3-user3'] instanceof Customer);
	}

	public function testFindCount()
	{
		$this->assertEquals(3, Customer::find()->count());
		$this->assertEquals(1, Customer::find()->limit(1)->count());
		$this->assertEquals(2, Customer::find()->limit(2)->count());
		$this->assertEquals(1, Customer::find()->offset(2)->limit(2)->count());
	}

	public function testFindLimit()
	{
		// all()
		$customers = Customer::find()->all();
		$this->assertEquals(3, count($customers));

		$customers = Customer::find()->limit(1)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user1', $customers[0]->name);

		$customers = Customer::find()->limit(1)->offset(1)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user2', $customers[0]->name);

		$customers = Customer::find()->limit(1)->offset(2)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user3', $customers[0]->name);

		$customers = Customer::find()->limit(2)->offset(1)->all();
		$this->assertEquals(2, count($customers));
		$this->assertEquals('user2', $customers[0]->name);
		$this->assertEquals('user3', $customers[1]->name);

		$customers = Customer::find()->limit(2)->offset(3)->all();
		$this->assertEquals(0, count($customers));

		// one()
		$customer = Customer::find()->one();
		$this->assertEquals('user1', $customer->name);

		$customer = Customer::find()->offset(0)->one();
		$this->assertEquals('user1', $customer->name);

		$customer = Customer::find()->offset(1)->one();
		$this->assertEquals('user2', $customer->name);

		$customer = Customer::find()->offset(2)->one();
		$this->assertEquals('user3', $customer->name);

		$customer = Customer::find()->offset(3)->one();
		$this->assertNull($customer);

	}

	public function testFindComplexCondition()
	{
		$this->assertEquals(2, Customer::find()->where(array('OR', array('id' => 1), array('id' => 2)))->count());
		$this->assertEquals(2, count(Customer::find()->where(array('OR', array('id' => 1), array('id' => 2)))->all()));

		$this->assertEquals(2, Customer::find()->where(array('id' => array(1,2)))->count());
		$this->assertEquals(2, count(Customer::find()->where(array('id' => array(1,2)))->all()));

		$this->assertEquals(1, Customer::find()->where(array('AND', array('id' => array(2,3)), array('BETWEEN', 'status', 2, 4)))->count());
		$this->assertEquals(1, count(Customer::find()->where(array('AND', array('id' => array(2,3)), array('BETWEEN', 'status', 2, 4)))->all()));
	}

	public function testSum()
	{
		$this->assertEquals(6, OrderItem::find()->count());
		$this->assertEquals(7, OrderItem::find()->sum('quantity'));
	}

	public function testFindColumn()
	{
		$this->assertEquals(array('user1', 'user2', 'user3'), Customer::find()->column('name'));
//		TODO $this->assertEquals(array('user3', 'user2', 'user1'), Customer::find()->orderBy(array('name' => Query::SORT_DESC))->column('name'));
	}

	public function testExists()
	{
		$this->assertTrue(Customer::find()->where(array('id' => 2))->exists());
		$this->assertFalse(Customer::find()->where(array('id' => 5))->exists());
	}

	public function testFindLazy()
	{
		/** @var $customer Customer */
		$customer = Customer::find(2);
		$orders = $customer->orders;
		$this->assertEquals(2, count($orders));

		$orders = $customer->getOrders()->where(array('id' => 3))->all();
		$this->assertEquals(1, count($orders));
		$this->assertEquals(3, $orders[0]->id);
	}

	public function testFindEager()
	{
		$customers = Customer::find()->with('orders')->all();
		$this->assertEquals(3, count($customers));
		$this->assertEquals(1, count($customers[0]->orders));
		$this->assertEquals(2, count($customers[1]->orders));
	}

	public function testFindLazyVia()
	{
		/** @var $order Order */
		$order = Order::find(1);
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);

		$order = Order::find(1);
		$order->id = 100;
		$this->assertEquals(array(), $order->items);
	}

	public function testFindEagerViaRelation()
	{
		$orders = Order::find()->with('items')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testFindNestedRelation()
	{
		$customers = Customer::find()->with('orders', 'orders.items')->all();
		$this->assertEquals(3, count($customers));
		$this->assertEquals(1, count($customers[0]->orders));
		$this->assertEquals(2, count($customers[1]->orders));
		$this->assertEquals(0, count($customers[2]->orders));
		$this->assertEquals(2, count($customers[0]->orders[0]->items));
		$this->assertEquals(3, count($customers[1]->orders[0]->items));
		$this->assertEquals(1, count($customers[1]->orders[1]->items));
	}

	public function testLink()
	{
		$customer = Customer::find(2);
		$this->assertEquals(2, count($customer->orders));

		// has many
		$order = new Order;
		$order->total = 100;
		$this->assertTrue($order->isNewRecord);
		$customer->link('orders', $order);
		$this->assertEquals(3, count($customer->orders));
		$this->assertFalse($order->isNewRecord);
		$this->assertEquals(3, count($customer->getOrders()->all()));
		$this->assertEquals(2, $order->customer_id);

		// belongs to
		$order = new Order;
		$order->total = 100;
		$this->assertTrue($order->isNewRecord);
		$customer = Customer::find(1);
		$this->assertNull($order->customer);
		$order->link('customer', $customer);
		$this->assertFalse($order->isNewRecord);
		$this->assertEquals(1, $order->customer_id);
		$this->assertEquals(1, $order->customer->id);

		// TODO support via
//		// via model
//		$order = Order::find(1);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(2, count($order->orderItems));
//		$orderItem = OrderItem::find(array('order_id' => 1, 'item_id' => 3));
//		$this->assertNull($orderItem);
//		$item = Item::find(3);
//		$order->link('items', $item, array('quantity' => 10, 'subtotal' => 100));
//		$this->assertEquals(3, count($order->items));
//		$this->assertEquals(3, count($order->orderItems));
//		$orderItem = OrderItem::find(array('order_id' => 1, 'item_id' => 3));
//		$this->assertTrue($orderItem instanceof OrderItem);
//		$this->assertEquals(10, $orderItem->quantity);
//		$this->assertEquals(100, $orderItem->subtotal);
	}

	public function testUnlink()
	{
		// has many
		$customer = Customer::find(2);
		$this->assertEquals(2, count($customer->orders));
		$customer->unlink('orders', $customer->orders[1], true);
		$this->assertEquals(1, count($customer->orders));
		$this->assertNull(Order::find(3));

		// TODO support via
//		// via model
//		$order = Order::find(2);
//		$this->assertEquals(3, count($order->items));
//		$this->assertEquals(3, count($order->orderItems));
//		$order->unlink('items', $order->items[2], true);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(2, count($order->orderItems));
	}

	public function testInsert()
	{
		$customer = new Customer;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->id);
		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertEquals(4, $customer->id);
		$this->assertFalse($customer->isNewRecord);
	}

	// TODO test serial column incr

	public function testUpdate()
	{
		// save
		$customer = Customer::find(2);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		$customer->name = 'user2x';
		$customer->save();
		$this->assertEquals('user2x', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		$customer2 = Customer::find(2);
		$this->assertEquals('user2x', $customer2->name);

		// updateAll
		$customer = Customer::find(3);
		$this->assertEquals('user3', $customer->name);
		$ret = Customer::updateAll(array(
			'name' => 'temp',
		), array('id' => 3));
		$this->assertEquals(1, $ret);
		$customer = Customer::find(3);
		$this->assertEquals('temp', $customer->name);
	}

	public function testUpdateCounters()
	{
		// updateCounters
		$pk = array('order_id' => 2, 'item_id' => 4);
		$orderItem = OrderItem::find($pk);
		$this->assertEquals(1, $orderItem->quantity);
		$ret = $orderItem->updateCounters(array('quantity' => -1));
		$this->assertTrue($ret);
		$this->assertEquals(0, $orderItem->quantity);
		$orderItem = OrderItem::find($pk);
		$this->assertEquals(0, $orderItem->quantity);

		// updateAllCounters
		$pk = array('order_id' => 1, 'item_id' => 2);
		$orderItem = OrderItem::find($pk);
		$this->assertEquals(2, $orderItem->quantity);
		$ret = OrderItem::updateAllCounters(array(
			'quantity' => 3,
			'subtotal' => -10,
		), $pk);
		$this->assertEquals(1, $ret);
		$orderItem = OrderItem::find($pk);
		$this->assertEquals(5, $orderItem->quantity);
		$this->assertEquals(30, $orderItem->subtotal);
	}

	public function testUpdatePk()
	{
		// updateCounters
		$pk = array('order_id' => 2, 'item_id' => 4);
		$orderItem = OrderItem::find($pk);
		$this->assertEquals(2, $orderItem->order_id);
		$this->assertEquals(4, $orderItem->item_id);

		$orderItem->order_id = 2;
		$orderItem->item_id = 10;
		$orderItem->save();

		$this->assertNull(OrderItem::find($pk));
		$this->assertNotNull(OrderItem::find(array('order_id' => 2, 'item_id' => 10)));
	}

	public function testDelete()
	{
		// delete
		$customer = Customer::find(2);
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer->delete();
		$customer = Customer::find(2);
		$this->assertNull($customer);

		// deleteAll
		$customers = Customer::find()->all();
		$this->assertEquals(2, count($customers));
		$ret = Customer::deleteAll();
		$this->assertEquals(2, $ret);
		$customers = Customer::find()->all();
		$this->assertEquals(0, count($customers));
	}
}
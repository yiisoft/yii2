<?php

namespace yiiunit\framework\elasticsearch;

use yii\elasticsearch\Connection;
use yii\elasticsearch\ActiveQuery;
use yii\helpers\Json;
use yiiunit\data\ar\elasticsearch\ActiveRecord;
use yiiunit\data\ar\elasticsearch\Customer;
use yiiunit\data\ar\elasticsearch\OrderItem;
use yiiunit\data\ar\elasticsearch\Order;
use yiiunit\data\ar\elasticsearch\Item;

class ActiveRecordTest extends ElasticSearchTestCase
{
	public function setUp()
	{
		parent::setUp();

		/** @var Connection $db */
		$db = ActiveRecord::$db = $this->getConnection();

		// delete all indexes
		$db->http()->delete('_all')->send();

		$customer = new Customer();
		$customer->primaryKey = 1;
		$customer->setAttributes(array('email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1), false);
		$customer->save(false);
		$customer = new Customer();
		$customer->primaryKey = 2;
		$customer->setAttributes(array('email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1), false);
		$customer->save(false);
		$customer = new Customer();
		$customer->primaryKey = 3;
		$customer->setAttributes(array('email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2), false);
		$customer->save(false);

//		INSERT INTO tbl_category (name) VALUES ('Books');
//		INSERT INTO tbl_category (name) VALUES ('Movies');

		$item = new Item();
		$item->primaryKey = 1;
		$item->setAttributes(array('name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1), false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 2;
		$item->setAttributes(array('name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1), false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 3;
		$item->setAttributes(array('name' => 'Ice Age', 'category_id' => 2), false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 4;
		$item->setAttributes(array('name' => 'Toy Story', 'category_id' => 2), false);
		$item->save(false);
		$item = new Item();
		$item->primaryKey = 5;
		$item->setAttributes(array('name' => 'Cars', 'category_id' => 2), false);
		$item->save(false);

		$order = new Order();
		$order->primaryKey = 1;
		$order->setAttributes(array('customer_id' => 1, 'create_time' => 1325282384, 'total' => 110.0), false);
		$order->save(false);
		$order = new Order();
		$order->primaryKey = 2;
		$order->setAttributes(array('customer_id' => 2, 'create_time' => 1325334482, 'total' => 33.0), false);
		$order->save(false);
		$order = new Order();
		$order->primaryKey = 3;
		$order->setAttributes(array('customer_id' => 2, 'create_time' => 1325502201, 'total' => 40.0), false);
		$order->save(false);

//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0), false);
//		$orderItem->save(false);
//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0), false);
//		$orderItem->save(false);
//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0), false);
//		$orderItem->save(false);
//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0), false);
//		$orderItem->save(false);
//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0), false);
//		$orderItem->save(false);
//		$orderItem = new OrderItem();
//		$orderItem->setAttributes(array('order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0), false);
//		$orderItem->save(false);

		Customer::getDb()->createCommand()->flushIndex();

//		for($n = 0; $n < 20; $n++) {
//			$r = $db->http()->post('_count')->send();
//			$c = Json::decode($r->getBody(true));
//			if ($c['count'] != 11) {
//				usleep(100000);
//			} else {
//				return;
//			}
//		}
//		throw new \Exception('Unable to initialize elasticsearch data.');
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
		$customerName = Customer::find()->where(array('status' => 2))->scalar('name');
		$this->assertEquals('user3', $customerName);

		// find by column values
		$customer = Customer::find(array('name' => 'user2'));
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(array('name' => 'user1', 'id' => 2));
		$this->assertNull($customer);
		$customer = Customer::find(array('primaryKey' => 5));
		$this->assertNull($customer);
		$customer = Customer::find(array('name' => 'user5'));
		$this->assertNull($customer);

		// find by attributes
		$customer = Customer::find()->where(array('name' => 'user2'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);

		// find count, sum, average, min, max, scalar
		$this->assertEquals(3, Customer::find()->count());
//		$this->assertEquals(6, Customer::find()->sum('id'));
//		$this->assertEquals(2, Customer::find()->average('id'));
//		$this->assertEquals(1, Customer::find()->min('id'));
//		$this->assertEquals(3, Customer::find()->max('id'));

		// scope
		$this->assertEquals(2, count(Customer::find()->active()->all()));
//		$this->assertEquals(2, Customer::find()->active()->count());

		// asArray
		$customer = Customer::find()->where(array('name' => 'user2'))->asArray()->one();
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
//		})->orderBy('id')->all();
		})->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['1-user1'] instanceof Customer);
		$this->assertTrue($customers['1-user2'] instanceof Customer);
		$this->assertTrue($customers['2-user3'] instanceof Customer);
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

//	public function testFindLazy()
//	{
//		/** @var $customer Customer */
//		$customer = Customer::find(2);
//		$orders = $customer->orders;
//		$this->assertEquals(2, count($orders));
//
//		$orders = $customer->getOrders()->where(array('id' => 3))->all();
//		$this->assertEquals(1, count($orders));
//		$this->assertEquals(3, $orders[0]->id);
//	}
//
//	public function testFindEager()
//	{
//		$customers = Customer::find()->with('orders')->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertEquals(1, count($customers[0]->orders));
//		$this->assertEquals(2, count($customers[1]->orders));
//	}
//
//	public function testFindLazyVia()
//	{
//		/** @var $order Order */
//		$order = Order::find(1);
//		$this->assertEquals(1, $order->id);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(1, $order->items[0]->id);
//		$this->assertEquals(2, $order->items[1]->id);
//
//		$order = Order::find(1);
//		$order->id = 100;
//		$this->assertEquals(array(), $order->items);
//	}
//
//	public function testFindEagerViaRelation()
//	{
//		$orders = Order::find()->with('items')->all();
//		$this->assertEquals(3, count($orders));
//		$order = $orders[0];
//		$this->assertEquals(1, $order->id);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(1, $order->items[0]->id);
//		$this->assertEquals(2, $order->items[1]->id);
//	}
//
//	public function testFindNestedRelation()
//	{
//		$customers = Customer::find()->with('orders', 'orders.items')->all();
//		$this->assertEquals(3, count($customers));
//		$this->assertEquals(1, count($customers[0]->orders));
//		$this->assertEquals(2, count($customers[1]->orders));
//		$this->assertEquals(0, count($customers[2]->orders));
//		$this->assertEquals(2, count($customers[0]->orders[0]->items));
//		$this->assertEquals(3, count($customers[1]->orders[0]->items));
//		$this->assertEquals(1, count($customers[1]->orders[1]->items));
//	}
//
//	public function testLink()
//	{
//		$customer = Customer::find(2);
//		$this->assertEquals(2, count($customer->orders));
//
//		// has many
//		$order = new Order;
//		$order->total = 100;
//		$this->assertTrue($order->isNewRecord);
//		$customer->link('orders', $order);
//		$this->assertEquals(3, count($customer->orders));
//		$this->assertFalse($order->isNewRecord);
//		$this->assertEquals(3, count($customer->getOrders()->all()));
//		$this->assertEquals(2, $order->customer_id);
//
//		// belongs to
//		$order = new Order;
//		$order->total = 100;
//		$this->assertTrue($order->isNewRecord);
//		$customer = Customer::find(1);
//		$this->assertNull($order->customer);
//		$order->link('customer', $customer);
//		$this->assertFalse($order->isNewRecord);
//		$this->assertEquals(1, $order->customer_id);
//		$this->assertEquals(1, $order->customer->id);
//
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
//	}
//
//	public function testUnlink()
//	{
//		// has many
//		$customer = Customer::find(2);
//		$this->assertEquals(2, count($customer->orders));
//		$customer->unlink('orders', $customer->orders[1], true);
//		$this->assertEquals(1, count($customer->orders));
//		$this->assertNull(Order::find(3));
//
//		// via model
//		$order = Order::find(2);
//		$this->assertEquals(3, count($order->items));
//		$this->assertEquals(3, count($order->orderItems));
//		$order->unlink('items', $order->items[2], true);
//		$this->assertEquals(2, count($order->items));
//		$this->assertEquals(2, count($order->orderItems));
//	}

	public function testInsertNoPk()
	{
		$customer = new Customer;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->id);
		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertNotNull($customer->id);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testInsertPk()
	{
		$customer = new Customer;
		$customer->id = 5;
		$customer->email = 'user5@example.com';
		$customer->name = 'user5';
		$customer->address = 'address5';

		$this->assertTrue($customer->isNewRecord);

		$customer->save();

		$this->assertEquals(5, $customer->id);
		$this->assertFalse($customer->isNewRecord);
	}

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

	public function testUpdatePk()
	{
		$this->setExpectedException('yii\base\NotSupportedException');

		$pk = array('id' => 2);
		$orderItem = Order::find($pk);
		$this->assertEquals(2, $orderItem->id);

		$orderItem->id = 13;
		$orderItem->save();

		$this->assertNull(OrderItem::find($pk));
		$this->assertNotNull(OrderItem::find(array('id' => 13)));
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
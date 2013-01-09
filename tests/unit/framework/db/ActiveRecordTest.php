<?php

namespace yiiunit\framework\db;

use yii\db\Query;
use yii\db\ActiveQuery;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Order;

class ActiveRecordTest extends \yiiunit\MysqlTestCase
{
	public function setUp()
	{
		ActiveRecord::$db = $this->getConnection();
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

		// find by column values
		$customer = Customer::find(array('id' => 2, 'name' => 'user2'));
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer = Customer::find(array('id' => 2, 'name' => 'user1'));
		$this->assertNull($customer);

		// find by attributes
		$customer = Customer::find()->where(array('name' => 'user2'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(2, $customer->id);


		// find count
		$this->assertEquals(3, Customer::count()->value());
		$this->assertEquals(2, Customer::find()->select('COUNT(*)')->where('id=1 OR id=2')->value());
		$this->assertEquals(6, Customer::count('SUM(id)')->value());

		// asArray
		$customer = Customer::find()->where('id=2')->asArray()->one();
		$this->assertEquals(array(
			'id' => '2',
			'email' => 'user2@example.com',
			'name' => 'user2',
			'address' => 'address2',
			'status' => '1',
		), $customer);

		// indexBy
		$customers = Customer::find()->indexBy('name')->orderBy('id')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['user1'] instanceof Customer);
		$this->assertTrue($customers['user2'] instanceof Customer);
		$this->assertTrue($customers['user3'] instanceof Customer);
	}

	public function testFindBySql()
	{
		// find one
		$customer = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC')->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user3', $customer->name);

		// find all
		$customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
		$this->assertEquals(3, count($customers));

		// find with parameter binding
		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', array(':id' => 2))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
	}

	public function testScope()
	{
		$customers = Customer::find()->scopes('active')->all();
		$this->assertEquals(2, count($customers));

		$customers = Customer::find()->active()->all();
		$this->assertEquals(2, count($customers));
	}

	public function testFindLazy()
	{
		/** @var $customer Customer */
		$customer = Customer::find(2);
		$orders = $customer->orders;
		$this->assertEquals(2, count($orders));

		$orders = $customer->orders()->where('id=3')->all();
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
		$orders = Order::find()->with('items')->orderBy('id')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testFindLazyViaTable()
	{
		/** @var $order Order */
		$order = Order::find(1);
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->books));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);

		$order = Order::find(2);
		$this->assertEquals(2, $order->id);
		$this->assertEquals(0, count($order->books));
	}

	public function testFindEagerViaTable()
	{
		$orders = Order::find()->with('books')->orderBy('id')->all();
		$this->assertEquals(3, count($orders));

		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->books));
		$this->assertEquals(1, $order->books[0]->id);
		$this->assertEquals(2, $order->books[1]->id);

		$order = $orders[1];
		$this->assertEquals(2, $order->id);
		$this->assertEquals(0, count($order->books));

		$order = $orders[2];
		$this->assertEquals(3, $order->id);
		$this->assertEquals(1, count($order->books));
		$this->assertEquals(2, $order->books[0]->id);
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

//	public function testInsert()
//	{
//		$customer = new Customer;
//		$customer->email = 'user4@example.com';
//		$customer->name = 'user4';
//		$customer->address = 'address4';
//
//		$this->assertNull($customer->id);
//		$this->assertTrue($customer->isNewRecord);
//
//		$customer->save();
//
//		$this->assertEquals(4, $customer->id);
//		$this->assertFalse($customer->isNewRecord);
//	}
//
//	public function testUpdate()
//	{
//		// save
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//		$this->assertFalse($customer->isNewRecord);
//		$customer->name = 'user2x';
//		$customer->save();
//		$this->assertEquals('user2x', $customer->name);
//		$this->assertFalse($customer->isNewRecord);
//		$customer2 = Customer::find(2);
//		$this->assertEquals('user2x', $customer2->name);
//
//		// updateCounters
//		$pk = array('order_id' => 2, 'item_id' => 4);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(1, $orderItem->quantity);
//		$ret = $orderItem->updateCounters(array('quantity' => -1));
//		$this->assertTrue($ret);
//		$this->assertEquals(0, $orderItem->quantity);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(0, $orderItem->quantity);
//
//		// updateAll
//		$customer = Customer::find(3);
//		$this->assertEquals('user3', $customer->name);
//		$ret = Customer::updateAll(array(
//			'name' => 'temp',
//		), array('id' => 3));
//		$this->assertEquals(1, $ret);
//		$customer = Customer::find(3);
//		$this->assertEquals('temp', $customer->name);
//
//		// updateCounters
//		$pk = array('order_id' => 1, 'item_id' => 2);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(2, $orderItem->quantity);
//		$ret = OrderItem::updateAllCounters(array(
//			'quantity' => 3,
//		), $pk);
//		$this->assertEquals(1, $ret);
//		$orderItem = OrderItem::find()->where($pk)->one();
//		$this->assertEquals(5, $orderItem->quantity);
//	}
//
//	public function testDelete()
//	{
//		// delete
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//		$customer->delete();
//		$customer = Customer::find(2);
//		$this->assertNull($customer);
//
//		// deleteAll
//		$customers = Customer::find()->all();
//		$this->assertEquals(2, count($customers));
//		$ret = Customer::deleteAll();
//		$this->assertEquals(2, $ret);
//		$customers = Customer::find()->all();
//		$this->assertEquals(0, count($customers));
//	}
//
//	public function testFind()
//	{
//		// find one
//		$result = Customer::find();
//		$this->assertTrue($result instanceof ActiveQuery);
//		$customer = $result->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(1, $result->count);
//
//		// find all
//		$result = Customer::find();
//		$customers = $result->all();
//		$this->assertTrue(is_array($customers));
//		$this->assertEquals(3, count($customers));
//		$this->assertTrue($customers[0] instanceof Customer);
//		$this->assertTrue($customers[1] instanceof Customer);
//		$this->assertTrue($customers[2] instanceof Customer);
//		$this->assertEquals(3, $result->count);
//		$this->assertEquals(3, count($result));
//
//		// check count first
//		$result = Customer::find();
//		$this->assertEquals(3, $result->count);
//		$customer = $result->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(3, $result->count);
//
//		// iterator
//		$result = Customer::find();
//		$count = 0;
//		foreach ($result as $customer) {
//			$this->assertTrue($customer instanceof Customer);
//			$count++;
//		}
//		$this->assertEquals($count, $result->count);
//
//		// array access
//		$result = Customer::find();
//		$this->assertTrue($result[0] instanceof Customer);
//		$this->assertTrue($result[1] instanceof Customer);
//		$this->assertTrue($result[2] instanceof Customer);
//
//		// find by a single primary key
//		$customer = Customer::find(2);
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find by attributes
//		$customer = Customer::find()->where(array('name' => 'user2'))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(2, $customer->id);
//
//		// find by Query
//		$query = array(
//			'where' => 'id=:id',
//			'params' => array(':id' => 2),
//		);
//		$customer = Customer::find($query)->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// find count
//		$this->assertEquals(3, Customer::find()->count());
//		$this->assertEquals(3, Customer::count());
//		$this->assertEquals(2, Customer::count(array(
//			'where' => 'id=1 OR id=2',
//		)));
//	}
//
//	public function testFindBySql()
//	{
//		// find one
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC')->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user3', $customer->name);
//
//		// find all
//		$customers = Customer::findBySql('SELECT * FROM tbl_customer')->all();
//		$this->assertEquals(3, count($customers));
//
//		// find with parameter binding
//		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', array(':id' => 2))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		// count
//		$query = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
//		$query->one();
//		$this->assertEquals(3, $query->count);
//		$query = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
//		$this->assertEquals(3, $query->count);
//	}
//
//	public function testQueryMethods()
//	{
//		$customer = Customer::find()->where('id=:id', array(':id' => 2))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user2', $customer->name);
//
//		$customer = Customer::find()->where(array('name' => 'user3'))->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals('user3', $customer->name);
//
//		$customer = Customer::find()->select('id')->orderBy('id DESC')->one();
//		$this->assertTrue($customer instanceof Customer);
//		$this->assertEquals(3, $customer->id);
//		$this->assertEquals(null, $customer->name);
//
//		// scopes
//		$customers = Customer::find()->active()->all();
//		$this->assertEquals(2, count($customers));
//		$customers = Customer::find(array(
//			'scopes' => array('active'),
//		))->all();
//		$this->assertEquals(2, count($customers));
//
//		// asArray
//		$customers = Customer::find()->orderBy('id')->asArray()->all();
//		$this->assertEquals('user2', $customers[1]['name']);
//
//		// index
//		$customers = Customer::find()->orderBy('id')->index('name')->all();
//		$this->assertEquals(2, $customers['user2']['id']);
//	}

}
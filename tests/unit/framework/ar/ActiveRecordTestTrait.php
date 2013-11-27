<?php
/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\ar;

use yii\db\ActiveQueryInterface;
use yiiunit\TestCase;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;

/**
 * This trait provides unit tests shared by the differen AR implementations
 *
 * @var TestCase $this
 */
trait ActiveRecordTestTrait
{
	/**
	 * This method should call Customer::find($q)
	 * @param $q
	 * @return mixed
	 */
	public abstract function callCustomerFind($q = null);

	/**
	 * This method should call Order::find($q)
	 * @param $q
	 * @return mixed
	 */
	public abstract function callOrderFind($q = null);

	/**
	 * This method should call OrderItem::find($q)
	 * @param $q
	 * @return mixed
	 */
	public abstract function callOrderItemFind($q = null);

	/**
	 * This method should call Item::find($q)
	 * @param $q
	 * @return mixed
	 */
	public abstract function callItemFind($q = null);

	/**
	 * This method should return the classname of Customer class
	 * @return string
	 */
	public abstract function getCustomerClass();

	/**
	 * This method should return the classname of Order class
	 * @return string
	 */
	public abstract function getOrderClass();

	/**
	 * This method should return the classname of OrderItem class
	 * @return string
	 */
	public abstract function getOrderItemClass();

	/**
	 * This method should return the classname of Item class
	 * @return string
	 */
	public abstract function getItemClass();

	/**
	 * can be overridden to do things after save()
	 */
	public function afterSave()
	{
	}


	public function testFind()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// find one
		$result = $this->callCustomerFind();
		$this->assertTrue($result instanceof ActiveQueryInterface);
		$customer = $result->one();
		$this->assertTrue($customer instanceof $customerClass);

		// find all
		$customers = $this->callCustomerFind()->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers[0] instanceof $customerClass);
		$this->assertTrue($customers[1] instanceof $customerClass);
		$this->assertTrue($customers[2] instanceof $customerClass);

		// find all asArray
		$customers = $this->callCustomerFind()->asArray()->all();
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

		// find by a single primary key
		$customer = $this->callCustomerFind(2);
		$this->assertTrue($customer instanceof $customerClass);
		$this->assertEquals('user2', $customer->name);
		$customer = $this->callCustomerFind(5);
		$this->assertNull($customer);
		$customer = $this->callCustomerFind(['id' => [5, 6, 1]]);
		$this->assertEquals(1, count($customer));
		$customer = $this->callCustomerFind()->where(['id' => [5, 6, 1]])->one();
		$this->assertNotNull($customer);

		// find by column values
		$customer = $this->callCustomerFind(['id' => 2, 'name' => 'user2']);
		$this->assertTrue($customer instanceof $customerClass);
		$this->assertEquals('user2', $customer->name);
		$customer = $this->callCustomerFind(['id' => 2, 'name' => 'user1']);
		$this->assertNull($customer);
		$customer = $this->callCustomerFind(['id' => 5]);
		$this->assertNull($customer);
		$customer = $this->callCustomerFind(['name' => 'user5']);
		$this->assertNull($customer);

		// find by attributes
		$customer = $this->callCustomerFind()->where(['name' => 'user2'])->one();
		$this->assertTrue($customer instanceof $customerClass);
		$this->assertEquals(2, $customer->id);

		// scope
		$this->assertEquals(2, count($this->callCustomerFind()->active()->all()));
		$this->assertEquals(2, $this->callCustomerFind()->active()->count());

		// asArray
		$customer = $this->callCustomerFind()->where(['id' => 2])->asArray()->one();
		$this->assertEquals([
			'id' => '2',
			'email' => 'user2@example.com',
			'name' => 'user2',
			'address' => 'address2',
			'status' => '1',
		], $customer);
	}

	public function testFindScalar()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		// query scalar
		$customerName = $this->callCustomerFind()->where(['id' => 2])->scalar('name');
		$this->assertEquals('user2', $customerName);
		$customerName = $this->callCustomerFind()->where(['status' => 2])->scalar('name');
		$this->assertEquals('user3', $customerName);
		$customerName = $this->callCustomerFind()->where(['status' => 2])->scalar('noname');
		$this->assertNull($customerName);
		$customerId = $this->callCustomerFind()->where(['status' => 2])->scalar('id');
		$this->assertEquals(3, $customerId);
	}

	public function testFindColumn()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$this->assertEquals(['user1', 'user2', 'user3'], $this->callCustomerFind()->orderBy(['name' => SORT_ASC])->column('name'));
		$this->assertEquals(['user3', 'user2', 'user1'], $this->callCustomerFind()->orderBy(['name' => SORT_DESC])->column('name'));
	}

	public function testfindIndexBy()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// indexBy
		$customers = $this->callCustomerFind()->indexBy('name')->orderBy('id')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['user1'] instanceof $customerClass);
		$this->assertTrue($customers['user2'] instanceof $customerClass);
		$this->assertTrue($customers['user3'] instanceof $customerClass);

		// indexBy callable
		$customers = $this->callCustomerFind()->indexBy(function ($customer) {
			return $customer->id . '-' . $customer->name;
		})->orderBy('id')->all();
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers['1-user1'] instanceof $customerClass);
		$this->assertTrue($customers['2-user2'] instanceof $customerClass);
		$this->assertTrue($customers['3-user3'] instanceof $customerClass);
	}

	public function testfindIndexByAsArray()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// indexBy + asArray
		$customers = $this->callCustomerFind()->asArray()->indexBy('name')->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers['user1']);
		$this->assertArrayHasKey('name', $customers['user1']);
		$this->assertArrayHasKey('email', $customers['user1']);
		$this->assertArrayHasKey('address', $customers['user1']);
		$this->assertArrayHasKey('status', $customers['user1']);
		$this->assertArrayHasKey('id', $customers['user2']);
		$this->assertArrayHasKey('name', $customers['user2']);
		$this->assertArrayHasKey('email', $customers['user2']);
		$this->assertArrayHasKey('address', $customers['user2']);
		$this->assertArrayHasKey('status', $customers['user2']);
		$this->assertArrayHasKey('id', $customers['user3']);
		$this->assertArrayHasKey('name', $customers['user3']);
		$this->assertArrayHasKey('email', $customers['user3']);
		$this->assertArrayHasKey('address', $customers['user3']);
		$this->assertArrayHasKey('status', $customers['user3']);

		// indexBy callable + asArray
		$customers = $this->callCustomerFind()->indexBy(function ($customer) {
			return $customer['id'] . '-' . $customer['name'];
		})->asArray()->all();
		$this->assertEquals(3, count($customers));
		$this->assertArrayHasKey('id', $customers['1-user1']);
		$this->assertArrayHasKey('name', $customers['1-user1']);
		$this->assertArrayHasKey('email', $customers['1-user1']);
		$this->assertArrayHasKey('address', $customers['1-user1']);
		$this->assertArrayHasKey('status', $customers['1-user1']);
		$this->assertArrayHasKey('id', $customers['2-user2']);
		$this->assertArrayHasKey('name', $customers['2-user2']);
		$this->assertArrayHasKey('email', $customers['2-user2']);
		$this->assertArrayHasKey('address', $customers['2-user2']);
		$this->assertArrayHasKey('status', $customers['2-user2']);
		$this->assertArrayHasKey('id', $customers['3-user3']);
		$this->assertArrayHasKey('name', $customers['3-user3']);
		$this->assertArrayHasKey('email', $customers['3-user3']);
		$this->assertArrayHasKey('address', $customers['3-user3']);
		$this->assertArrayHasKey('status', $customers['3-user3']);
	}

	public function testRefresh()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = new $customerClass();
		$this->assertFalse($customer->refresh());

		$customer = $this->callCustomerFind(1);
		$customer->name = 'to be refreshed';
		$this->assertTrue($customer->refresh());
		$this->assertEquals('user1', $customer->name);
	}

	public function testEquals()
	{
		$customerClass = $this->getCustomerClass();
		$itemClass = $this->getItemClass();

		/** @var TestCase|ActiveRecordTestTrait $this */
		$customerA = new $customerClass();
		$customerB = new $customerClass();
		$this->assertFalse($customerA->equals($customerB));

		$customerA = new $customerClass();
		$customerB = new $itemClass();
		$this->assertFalse($customerA->equals($customerB));

		$customerA = $this->callCustomerFind(1);
		$customerB = $this->callCustomerFind(2);
		$this->assertFalse($customerA->equals($customerB));

		$customerB = $this->callCustomerFind(1);
		$this->assertTrue($customerA->equals($customerB));

		$customerA = $this->callCustomerFind(1);
		$customerB = $this->callItemFind(1);
		$this->assertFalse($customerA->equals($customerB));
	}

	public function testFindCount()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$this->assertEquals(3, $this->callCustomerFind()->count());
		// TODO should limit have effect on count()
//		$this->assertEquals(1, $this->callCustomerFind()->limit(1)->count());
//		$this->assertEquals(2, $this->callCustomerFind()->limit(2)->count());
//		$this->assertEquals(1, $this->callCustomerFind()->offset(2)->limit(2)->count());
	}

	public function testFindLimit()
	{
		if (getenv('TRAVIS') == 'true' && $this instanceof \yiiunit\extensions\elasticsearch\ActiveRecordTest) {
			// https://github.com/yiisoft/yii2/issues/1317
			$this->markTestSkipped('This test is unreproduceable failing on travis-ci, locally it is passing.');
		}

		/** @var TestCase|ActiveRecordTestTrait $this */
		// all()
		$customers = $this->callCustomerFind()->all();
		$this->assertEquals(3, count($customers));

		$customers = $this->callCustomerFind()->orderBy('id')->limit(1)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user1', $customers[0]->name);

		$customers = $this->callCustomerFind()->orderBy('id')->limit(1)->offset(1)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user2', $customers[0]->name);

		$customers = $this->callCustomerFind()->orderBy('id')->limit(1)->offset(2)->all();
		$this->assertEquals(1, count($customers));
		$this->assertEquals('user3', $customers[0]->name);

		$customers = $this->callCustomerFind()->orderBy('id')->limit(2)->offset(1)->all();
		$this->assertEquals(2, count($customers));
		$this->assertEquals('user2', $customers[0]->name);
		$this->assertEquals('user3', $customers[1]->name);

		$customers = $this->callCustomerFind()->limit(2)->offset(3)->all();
		$this->assertEquals(0, count($customers));

		// one()
		$customer = $this->callCustomerFind()->orderBy('id')->one();
		$this->assertEquals('user1', $customer->name);

		$customer = $this->callCustomerFind()->orderBy('id')->offset(0)->one();
		$this->assertEquals('user1', $customer->name);

		$customer = $this->callCustomerFind()->orderBy('id')->offset(1)->one();
		$this->assertEquals('user2', $customer->name);

		$customer = $this->callCustomerFind()->orderBy('id')->offset(2)->one();
		$this->assertEquals('user3', $customer->name);

		$customer = $this->callCustomerFind()->offset(3)->one();
		$this->assertNull($customer);

	}

	public function testFindComplexCondition()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$this->assertEquals(2, $this->callCustomerFind()->where(['OR', ['name' => 'user1'], ['name' => 'user2']])->count());
		$this->assertEquals(2, count($this->callCustomerFind()->where(['OR', ['name' => 'user1'], ['name' => 'user2']])->all()));

		$this->assertEquals(2, $this->callCustomerFind()->where(['name' => ['user1','user2']])->count());
		$this->assertEquals(2, count($this->callCustomerFind()->where(['name' => ['user1','user2']])->all()));

		$this->assertEquals(1, $this->callCustomerFind()->where(['AND', ['name' => ['user2','user3']], ['BETWEEN', 'status', 2, 4]])->count());
		$this->assertEquals(1, count($this->callCustomerFind()->where(['AND', ['name' => ['user2','user3']], ['BETWEEN', 'status', 2, 4]])->all()));
	}

	public function testFindNullValues()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = $this->callCustomerFind(2);
		$customer->name = null;
		$customer->save(false);
		$this->afterSave();

		$result = $this->callCustomerFind()->where(['name' => null])->all();
		$this->assertEquals(1, count($result));
		$this->assertEquals(2, reset($result)->primaryKey);
	}

	public function testExists()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$this->assertTrue($this->callCustomerFind()->where(['id' => 2])->exists());
		$this->assertFalse($this->callCustomerFind()->where(['id' => 5])->exists());
		$this->assertTrue($this->callCustomerFind()->where(['name' => 'user1'])->exists());
		$this->assertFalse($this->callCustomerFind()->where(['name' => 'user5'])->exists());
	}

	public function testFindLazy()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = $this->callCustomerFind(2);
		$this->assertFalse($customer->isRelationPopulated('orders'));
		$orders = $customer->orders;
		$this->assertTrue($customer->isRelationPopulated('orders'));
		$this->assertEquals(2, count($orders));
		$this->assertEquals(1, count($customer->populatedRelations));

		/** @var Customer $customer */
		$customer = $this->callCustomerFind(2);
		$this->assertFalse($customer->isRelationPopulated('orders'));
		$orders = $customer->getOrders()->where(['id' => 3])->all();
		$this->assertFalse($customer->isRelationPopulated('orders'));
		$this->assertEquals(0, count($customer->populatedRelations));

		$this->assertEquals(1, count($orders));
		$this->assertEquals(3, $orders[0]->id);
	}

	public function testFindEager()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customers = $this->callCustomerFind()->with('orders')->indexBy('id')->all();
		ksort($customers);
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers[1]->isRelationPopulated('orders'));
		$this->assertTrue($customers[2]->isRelationPopulated('orders'));
		$this->assertTrue($customers[3]->isRelationPopulated('orders'));
		$this->assertEquals(1, count($customers[1]->orders));
		$this->assertEquals(2, count($customers[2]->orders));
		$this->assertEquals(0, count($customers[3]->orders));

		$customer = $this->callCustomerFind()->where(['id' => 1])->with('orders')->one();
		$this->assertTrue($customer->isRelationPopulated('orders'));
		$this->assertEquals(1, count($customer->orders));
		$this->assertEquals(1, count($customer->populatedRelations));
	}

	public function testFindLazyVia()
	{
		if (getenv('TRAVIS') == 'true' && $this instanceof \yiiunit\extensions\elasticsearch\ActiveRecordTest) {
			// https://github.com/yiisoft/yii2/issues/1317
			$this->markTestSkipped('This test is unreproduceable failing on travis-ci, locally it is passing.');
		}

		/** @var TestCase|ActiveRecordTestTrait $this */
		/** @var Order $order */
		$order = $this->callOrderFind(1);
		$this->assertEquals(1, $order->id);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testFindLazyVia2()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		/** @var Order $order */
		$order = $this->callOrderFind(1);
		$order->id = 100;
		$this->assertEquals([], $order->items);
	}

	public function testFindEagerViaRelation()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$orders = $this->callOrderFind()->with('items')->orderBy('id')->all();
		$this->assertEquals(3, count($orders));
		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertTrue($order->isRelationPopulated('items'));
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(1, $order->items[0]->id);
		$this->assertEquals(2, $order->items[1]->id);
	}

	public function testFindNestedRelation()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customers = $this->callCustomerFind()->with('orders', 'orders.items')->indexBy('id')->all();
		ksort($customers);
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers[1]->isRelationPopulated('orders'));
		$this->assertTrue($customers[2]->isRelationPopulated('orders'));
		$this->assertTrue($customers[3]->isRelationPopulated('orders'));
		$this->assertEquals(1, count($customers[1]->orders));
		$this->assertEquals(2, count($customers[2]->orders));
		$this->assertEquals(0, count($customers[3]->orders));
		$this->assertTrue($customers[1]->orders[0]->isRelationPopulated('items'));
		$this->assertTrue($customers[2]->orders[0]->isRelationPopulated('items'));
		$this->assertTrue($customers[2]->orders[1]->isRelationPopulated('items'));
		$this->assertEquals(2, count($customers[1]->orders[0]->items));
		$this->assertEquals(3, count($customers[2]->orders[0]->items));
		$this->assertEquals(1, count($customers[2]->orders[1]->items));
	}

	/**
	 * Ensure ActiveRelation does preserve order of items on find via()
	 * https://github.com/yiisoft/yii2/issues/1310
	 */
	public function testFindEagerViaRelationPreserveOrder()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		$orders = $this->callOrderFind()->with('itemsInOrder1')->orderBy('create_time')->all();
		$this->assertEquals(3, count($orders));

		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
		$this->assertEquals(2, count($order->itemsInOrder1));
		$this->assertEquals(1, $order->itemsInOrder1[0]->id);
		$this->assertEquals(2, $order->itemsInOrder1[1]->id);

		$order = $orders[1];
		$this->assertEquals(2, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
		$this->assertEquals(3, count($order->itemsInOrder1));
		$this->assertEquals(5, $order->itemsInOrder1[0]->id);
		$this->assertEquals(3, $order->itemsInOrder1[1]->id);
		$this->assertEquals(4, $order->itemsInOrder1[2]->id);

		$order = $orders[2];
		$this->assertEquals(3, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder1'));
		$this->assertEquals(1, count($order->itemsInOrder1));
		$this->assertEquals(2, $order->itemsInOrder1[0]->id);
	}

	// different order in via table
	public function testFindEagerViaRelationPreserveOrderB()
	{
		$orders = $this->callOrderFind()->with('itemsInOrder2')->orderBy('create_time')->all();
		$this->assertEquals(3, count($orders));

		$order = $orders[0];
		$this->assertEquals(1, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
		$this->assertEquals(2, count($order->itemsInOrder2));
		$this->assertEquals(1, $order->itemsInOrder2[0]->id);
		$this->assertEquals(2, $order->itemsInOrder2[1]->id);

		$order = $orders[1];
		$this->assertEquals(2, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
		$this->assertEquals(3, count($order->itemsInOrder2));
		$this->assertEquals(5, $order->itemsInOrder2[0]->id);
		$this->assertEquals(3, $order->itemsInOrder2[1]->id);
		$this->assertEquals(4, $order->itemsInOrder2[2]->id);

		$order = $orders[2];
		$this->assertEquals(3, $order->id);
		$this->assertTrue($order->isRelationPopulated('itemsInOrder2'));
		$this->assertEquals(1, count($order->itemsInOrder2));
		$this->assertEquals(2, $order->itemsInOrder2[0]->id);
	}

	public function testLink()
	{
		$orderClass = $this->getOrderClass();
		$orderItemClass = $this->getOrderItemClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = $this->callCustomerFind(2);
		$this->assertEquals(2, count($customer->orders));

		// has many
		$order = new $orderClass;
		$order->total = 100;
		$this->assertTrue($order->isNewRecord);
		$customer->link('orders', $order);
		$this->afterSave();
		$this->assertEquals(3, count($customer->orders));
		$this->assertFalse($order->isNewRecord);
		$this->assertEquals(3, count($customer->getOrders()->all()));
		$this->assertEquals(2, $order->customer_id);

		// belongs to
		$order = new $orderClass;
		$order->total = 100;
		$this->assertTrue($order->isNewRecord);
		$customer = $this->callCustomerFind(1);
		$this->assertNull($order->customer);
		$order->link('customer', $customer);
		$this->assertFalse($order->isNewRecord);
		$this->assertEquals(1, $order->customer_id);
		$this->assertEquals(1, $order->customer->primaryKey);

		// via model
		$order = $this->callOrderFind(1);
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(2, count($order->orderItems));
		$orderItem = $this->callOrderItemFind(['order_id' => 1, 'item_id' => 3]);
		$this->assertNull($orderItem);
		$item = $this->callItemFind(3);
		$order->link('items', $item, ['quantity' => 10, 'subtotal' => 100]);
		$this->afterSave();
		$this->assertEquals(3, count($order->items));
		$this->assertEquals(3, count($order->orderItems));
		$orderItem = $this->callOrderItemFind(['order_id' => 1, 'item_id' => 3]);
		$this->assertTrue($orderItem instanceof $orderItemClass);
		$this->assertEquals(10, $orderItem->quantity);
		$this->assertEquals(100, $orderItem->subtotal);
	}

	public function testUnlink()
	{
		/** @var TestCase|ActiveRecordTestTrait $this */
		// has many
		$customer = $this->callCustomerFind(2);
		$this->assertEquals(2, count($customer->orders));
		$customer->unlink('orders', $customer->orders[1], true);
		$this->afterSave();
		$this->assertEquals(1, count($customer->orders));
		$this->assertNull($this->callOrderFind(3));

		// via model
		$order = $this->callOrderFind(2);
		$this->assertEquals(3, count($order->items));
		$this->assertEquals(3, count($order->orderItems));
		$order->unlink('items', $order->items[2], true);
		$this->afterSave();
		$this->assertEquals(2, count($order->items));
		$this->assertEquals(2, count($order->orderItems));
	}

	public static $afterSaveNewRecord;
	public static $afterSaveInsert;

	public function testInsert()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = new $customerClass;
		$customer->email = 'user4@example.com';
		$customer->name = 'user4';
		$customer->address = 'address4';

		$this->assertNull($customer->id);
		$this->assertTrue($customer->isNewRecord);
		static::$afterSaveNewRecord = null;
		static::$afterSaveInsert = null;

		$customer->save();
		$this->afterSave();

		$this->assertNotNull($customer->id);
		$this->assertFalse(static::$afterSaveNewRecord);
		$this->assertTrue(static::$afterSaveInsert);
		$this->assertFalse($customer->isNewRecord);
	}

	public function testUpdate()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// save
		$customer = $this->callCustomerFind(2);
		$this->assertTrue($customer instanceof $customerClass);
		$this->assertEquals('user2', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		static::$afterSaveNewRecord = null;
		static::$afterSaveInsert = null;

		$customer->name = 'user2x';
		$customer->save();
		$this->afterSave();
		$this->assertEquals('user2x', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		$this->assertFalse(static::$afterSaveNewRecord);
		$this->assertFalse(static::$afterSaveInsert);
		$customer2 = $this->callCustomerFind(2);
		$this->assertEquals('user2x', $customer2->name);

		// updateAll
		$customer = $this->callCustomerFind(3);
		$this->assertEquals('user3', $customer->name);
		$ret = $customerClass::updateAll(['name' => 'temp'], ['id' => 3]);
		$this->afterSave();
		$this->assertEquals(1, $ret);
		$customer = $this->callCustomerFind(3);
		$this->assertEquals('temp', $customer->name);

		$ret = $customerClass::updateAll(['name' => 'tempX']);
		$this->afterSave();
		$this->assertEquals(3, $ret);

		$ret = $customerClass::updateAll(['name' => 'temp'], ['name' => 'user6']);
		$this->afterSave();
		$this->assertEquals(0, $ret);
	}

	public function testUpdateCounters()
	{
		$orderItemClass = $this->getOrderItemClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// updateCounters
		$pk = ['order_id' => 2, 'item_id' => 4];
		$orderItem = $this->callOrderItemFind($pk);
		$this->assertEquals(1, $orderItem->quantity);
		$ret = $orderItem->updateCounters(['quantity' => -1]);
		$this->afterSave();
		$this->assertTrue($ret);
		$this->assertEquals(0, $orderItem->quantity);
		$orderItem = $this->callOrderItemFind($pk);
		$this->assertEquals(0, $orderItem->quantity);

		// updateAllCounters
		$pk = ['order_id' => 1, 'item_id' => 2];
		$orderItem = $this->callOrderItemFind($pk);
		$this->assertEquals(2, $orderItem->quantity);
		$ret = $orderItemClass::updateAllCounters([
			'quantity' => 3,
			'subtotal' => -10,
		], $pk);
		$this->afterSave();
		$this->assertEquals(1, $ret);
		$orderItem = $this->callOrderItemFind($pk);
		$this->assertEquals(5, $orderItem->quantity);
		$this->assertEquals(30, $orderItem->subtotal);
	}

	public function testDelete()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		// delete
		$customer = $this->callCustomerFind(2);
		$this->assertTrue($customer instanceof $customerClass);
		$this->assertEquals('user2', $customer->name);
		$customer->delete();
		$this->afterSave();
		$customer = $this->callCustomerFind(2);
		$this->assertNull($customer);

		// deleteAll
		$customers = $this->callCustomerFind()->all();
		$this->assertEquals(2, count($customers));
		$ret = $customerClass::deleteAll();
		$this->afterSave();
		$this->assertEquals(2, $ret);
		$customers = $this->callCustomerFind()->all();
		$this->assertEquals(0, count($customers));

		$ret = $customerClass::deleteAll();
		$this->afterSave();
		$this->assertEquals(0, $ret);
	}

	/**
	 * Some PDO implementations(e.g. cubrid) do not support boolean values.
	 * Make sure this does not affect AR layer.
	 */
	public function testBooleanAttribute()
	{
		$customerClass = $this->getCustomerClass();
		/** @var TestCase|ActiveRecordTestTrait $this */
		$customer = new $customerClass();
		$customer->name = 'boolean customer';
		$customer->email = 'mail@example.com';
		$customer->status = true;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(1, $customer->status);

		$customer->status = false;
		$customer->save(false);

		$customer->refresh();
		$this->assertEquals(0, $customer->status);

		$customers = $this->callCustomerFind()->where(['status' => true])->all();
		$this->assertEquals(2, count($customers));

		$customers = $this->callCustomerFind()->where(['status' => false])->all();
		$this->assertEquals(1, count($customers));
	}
}
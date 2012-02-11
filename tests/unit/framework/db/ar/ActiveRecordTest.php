<?php

namespace yiiunit\framework\db\ar;

use yii\db\dao\Query;
use yii\db\ar\ActiveFinder;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\OrderItem;

class ActiveRecordTest extends \yiiunit\MysqlTestCase
{
	public function setUp()
	{
		ActiveRecord::$db = $this->getConnection();
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

	public function testUpdate()
	{
		// save
		$customer = Customer::find(2)->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		$customer->name = 'user2x';
		$customer->save();
		$this->assertEquals('user2x', $customer->name);
		$this->assertFalse($customer->isNewRecord);
		$customer2 = Customer::find(2)->one();
		$this->assertEquals('user2x', $customer2->name);

		// saveAttributes
		$customer = Customer::find(1)->one();
		$this->assertEquals('user1', $customer->name);
		$this->assertEquals('address1', $customer->address);
		$customer->saveAttributes(array(
			'name' => 'user1x',
			'address' => 'address1x',
		));
		$this->assertEquals('user1x', $customer->name);
		$this->assertEquals('address1x', $customer->address);
		$customer = Customer::find(1)->one();
		$this->assertEquals('user1x', $customer->name);
		$this->assertEquals('address1x', $customer->address);

		// saveCounters
		$pk = array('order_id' => 2, 'item_id' => 4);
		$orderItem = OrderItem::find($pk)->one();
		$this->assertEquals(1, $orderItem->quantity);
		$ret = $orderItem->saveCounters(array('quantity' => -1));
		$this->assertTrue($ret);
		$this->assertEquals(0, $orderItem->quantity);
		$orderItem = OrderItem::find($pk)->one();
		$this->assertEquals(0, $orderItem->quantity);

		// updateAll
		$customer = Customer::find(3)->one();
		$this->assertEquals('user3', $customer->name);
		$ret = Customer::updateAll(array(
			'name' => 'temp',
		), array('id' => 3));
		$this->assertEquals(1, $ret);
		$customer = Customer::find(3)->one();
		$this->assertEquals('temp', $customer->name);

		// updateCounters
		$pk = array('order_id' => 1, 'item_id' => 2);
		$orderItem = OrderItem::find($pk)->one();
		$this->assertEquals(2, $orderItem->quantity);
		$ret = OrderItem::updateCounters(array(
			'quantity' => 3,
		), $pk);
		$this->assertEquals(1, $ret);
		$orderItem = OrderItem::find($pk)->one();
		$this->assertEquals(5, $orderItem->quantity);
	}

	public function testDelete()
	{
		// delete
		$customer = Customer::find(2)->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);
		$customer->delete();
		$customer = Customer::find(2)->one();
		$this->assertNull($customer);

		// deleteAll
		$customers = Customer::find()->all();
		$this->assertEquals(2, count($customers));
		$ret = Customer::deleteAll();
		$this->assertEquals(2, $ret);
		$customers = Customer::find()->all();
		$this->assertEquals(0, count($customers));
	}

	public function testFind()
	{
		// find one
		$result = Customer::find();
		$this->assertTrue($result instanceof ActiveFinder);
		$customer = $result->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(1, $result->count);

		// find all
		$result = Customer::find();
		$customers = $result->all();
		$this->assertTrue(is_array($customers));
		$this->assertEquals(3, count($customers));
		$this->assertTrue($customers[0] instanceof Customer);
		$this->assertTrue($customers[1] instanceof Customer);
		$this->assertTrue($customers[2] instanceof Customer);
		$this->assertEquals(3, $result->count);
		$this->assertEquals(3, count($result));

		// check count first
		$result = Customer::find();
		$this->assertEquals(3, $result->count);
		$customer = $result->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(3, $result->count);

		// iterator
		$result = Customer::find();
		$count = 0;
		foreach ($result as $customer) {
			$this->assertTrue($customer instanceof Customer);
			$count++;
		}
		$this->assertEquals($count, $result->count);

		// array access
		$result = Customer::find();
		$this->assertTrue($result[0] instanceof Customer);
		$this->assertTrue($result[1] instanceof Customer);
		$this->assertTrue($result[2] instanceof Customer);

		// find by a single primary key
		$customer = Customer::find(2)->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);

		// find by attributes
		$customer = Customer::find(array('name' => 'user2'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(2, $customer->id);

		// find by Query
		$query = new Query;
		$query->where('id=:id', array(':id' => 2));
		$customer = Customer::find($query)->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);

		// find count
		$this->assertEquals(3, Customer::find()->count(true));
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

		// count
		$finder = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
		$finder->one();
		$this->assertEquals(3, $finder->count);
		$finder = Customer::findBySql('SELECT * FROM tbl_customer ORDER BY id DESC');
		$this->assertEquals(3, $finder->count);
	}

	public function testQueryMethods()
	{
		$customer = Customer::find()->where('id=?', 2)->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user2', $customer->name);

		$customer = Customer::find()->where(array('name' => 'user3'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals('user3', $customer->name);

		$customer = Customer::find()->select('id')->orderBy('id DESC')->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(3, $customer->id);
		$this->assertEquals(null, $customer->name);
	}

	public function testEagerLoading()
	{
		$customers = Customer::find()->with('orders')->orderBy('t0.id')->all();
		$this->assertEquals(3, count($customers));
		$this->assertEquals(1, count($customers[0]->orders));
		$this->assertEquals(2, count($customers[1]->orders));
		$this->assertEquals(0, count($customers[2]->orders));

		$customers = Customer::find()->with('orders.customer')->orderBy('t0.id')->all();
	}

	/*
	 public function testGetSql()
	 {
		 // sql for all
		 $sql = Customer::find()->sql;
		 $this->assertEquals('SELECT * FROM `tbl_customer`', $sql);

		 // sql for one row
		 $sql = Customer::find()->oneSql;
		 $this->assertEquals('SELECT * FROM tbl_customer LIMIT 1', $sql);

		 // sql for count
		 $sql = Customer::find()->countSql;
		 $this->assertEquals('SELECT COUNT(*) FROM tbl_customer', $sql);
	 }

	 public function testArrayResult()
	 {
		 Customer::find()->asArray()->one();
		 Customer::find()->asArray()->all();
	 }

	 public function testMisc()
	 {
 //		 Customer::exists()
 //		 Customer::updateAll()
 //		 Customer::updateCounters()
 //		 Customer::deleteAll()
	 }


	 public function testLazyLoading()
	 {

	 }

	 public function testEagerLoading()
	 {

	 }
 */
}
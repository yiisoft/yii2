<?php

namespace yiiunit\framework\db\ar;

use yii\db\dao\Query;
use yii\db\ar\ActiveQuery;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;

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
		$customer = Customer::find(array('name'=>'user2'))->one();
		$this->assertTrue($customer instanceof Customer);
		$this->assertEquals(2, $customer->id);

		// find by Query
		$query = new Query;
		$query->where('id=:id', array(':id'=>2));
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
		$customer = Customer::findBySql('SELECT * FROM tbl_customer WHERE id=:id', array(':id'=>2))->one();
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

	public function testInsert()
	{

	}

	public function testUpdate()
	{


	}

	public function testDelete()
	{

	}

	public function testLazyLoading()
	{

	}

	public function testEagerLoading()
	{

	}
*/
}
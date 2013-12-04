<?php

namespace yiiunit\extensions\mongo;

use yii\mongo\Query;

/**
 * @group mongo
 */
class QueryRunTest extends MongoTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setUpTestRows();
	}

	protected function tearDown()
	{
		$this->dropCollection('customer');
		parent::tearDown();
	}

	/**
	 * Sets up test rows.
	 */
	protected function setUpTestRows()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$rows = [];
		for ($i = 1; $i <= 10; $i++) {
			$rows[] = [
				'name' => 'name' . $i,
				'address' => 'address' . $i,
				'avatar' => [
					'width' => 50 + $i,
					'height' => 100 + $i,
					'url' => 'http://some.url/' . $i,
				],
			];
		}
		$collection->batchInsert($rows);
	}

	// Tests :

	public function testAll()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$rows = $query->from('customer')->all($connection);
		$this->assertEquals(10, count($rows));
	}

	public function testDirectMatch()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$rows = $query->from('customer')
			->where(['name' => 'name1'])
			->all($connection);
		$this->assertEquals(1, count($rows));
		$this->assertEquals('name1', $rows[0]['name']);
	}

	public function testIndexBy()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$rows = $query->from('customer')
			->indexBy('name')
			->all($connection);
		$this->assertEquals(10, count($rows));
		$this->assertNotEmpty($rows['name1']);
	}

	public function testInCondition()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$rows = $query->from('customer')
			->where([
				'name' => ['name1', 'name5']
			])
			->all($connection);
		$this->assertEquals(2, count($rows));
		$this->assertEquals('name1', $rows[0]['name']);
		$this->assertEquals('name5', $rows[1]['name']);
	}

	public function testOrCondition()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$rows = $query->from('customer')
			->where(['name' => 'name1'])
			->orWhere(['address' => 'address5'])
			->all($connection);
		$this->assertEquals(2, count($rows));
		$this->assertEquals('name1', $rows[0]['name']);
		$this->assertEquals('address5', $rows[1]['address']);
	}

	public function testOrder()
	{
		$connection = $this->getConnection();

		$query = new Query;
		$rows = $query->from('customer')
			->orderBy(['name' => SORT_DESC])
			->all($connection);
		$this->assertEquals('name9', $rows[0]['name']);

		$query = new Query;
		$rows = $query->from('customer')
			->orderBy(['avatar.height' => SORT_DESC])
			->all($connection);
		$this->assertEquals('name10', $rows[0]['name']);
	}

	public function testMatchPlainId()
	{
		$connection = $this->getConnection();
		$query = new Query;
		$row = $query->from('customer')->one($connection);
		$query = new Query;
		$rows = $query->from('customer')
			->where(['_id' => $row['_id']->__toString()])
			->all($connection);
		$this->assertEquals(1, count($rows));
	}
}
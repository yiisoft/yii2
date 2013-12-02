<?php

namespace yiiunit\extensions\mongo;

/**
 * @group mongo
 */
class CollectionTest extends MongoTestCase
{
	protected function tearDown()
	{
		$this->dropCollection('customer');
		parent::tearDown();
	}

	// Tests :

	public function testFind()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$cursor = $collection->find();
		$this->assertTrue($cursor instanceof \MongoCursor);
	}

	public function testInsert()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $collection->insert($data);
		$this->assertTrue($id instanceof \MongoId);
		$this->assertNotEmpty($id->__toString());
	}

	/**
	 * @depends testInsert
	 */
	public function testFindAll()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $collection->insert($data);

		$rows = $collection->findAll();
		$this->assertEquals(1, count($rows));
		$this->assertEquals($id, $rows[0]['_id']);
	}

	/**
	 * @depends testFind
	 */
	public function testBatchInsert()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$rows = [
			[
				'name' => 'customer 1',
				'address' => 'customer 1 address',
			],
			[
				'name' => 'customer 2',
				'address' => 'customer 2 address',
			],
		];
		$insertedRows = $collection->batchInsert($rows);
		$this->assertTrue($insertedRows[0]['_id'] instanceof \MongoId);
		$this->assertTrue($insertedRows[1]['_id'] instanceof \MongoId);
		$this->assertEquals(count($rows), $collection->find()->count());
	}

	public function testSave()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $collection->save($data);
		$this->assertTrue($id instanceof \MongoId);
		$this->assertNotEmpty($id->__toString());
	}

	/**
	 * @depends testSave
	 */
	public function testUpdateBySave()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$newId = $collection->save($data);

		$updatedId = $collection->save($data);
		$this->assertEquals($newId, $updatedId, 'Unable to update data!');

		$data['_id'] = $newId->__toString();
		$updatedId = $collection->save($data);
		$this->assertEquals($newId, $updatedId, 'Unable to updated data by string id!');
	}

	/**
	 * @depends testFindAll
	 */
	public function testRemove()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $collection->insert($data);

		$count = $collection->remove(['_id' => $id]);
		$this->assertEquals(1, $count);

		$rows = $collection->findAll();
		$this->assertEquals(0, count($rows));
	}

	/**
	 * @depends testFindAll
	 */
	public function testUpdate()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $collection->insert($data);

		$newData = [
			'name' => 'new name'
		];
		$count = $collection->update(['_id' => $id], $newData);
		$this->assertEquals(1, $count);

		list($row) = $collection->findAll();
		$this->assertEquals($newData['name'], $row['name']);
	}

	/**
	 * @depends testBatchInsert
	 */
	public function testMapReduce()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$rows = [
			[
				'name' => 'customer 1',
				'address' => 'customer 1 address',
			],
			[
				'name' => 'customer 2',
				'address' => 'customer 2 address',
			],
		];
		$collection->batchInsert($rows);

		$keys = ['address' => 1];
		$initial = ['items' => []];
		$reduce = "function (obj, prev) { prev.items.push(obj.name); }";
		$result = $collection->mapReduce($keys, $initial, $reduce);
		$this->assertEquals(2, count($result));
		$this->assertNotEmpty($result[0]['address']);
		$this->assertNotEmpty($result[0]['items']);
	}

	public function testCreateIndex()
	{
		$collection = $this->getConnection()->getCollection('customer');
		$columns = [
			'name',
			'status' => \MongoCollection::DESCENDING,
		];
		$this->assertTrue($collection->createIndex($columns));
	}
}
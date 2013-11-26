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
	public function testUpdate()
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

		$collection->remove(['_id' => $id]);

		$rows = $collection->findAll();
		$this->assertEquals(0, count($rows));
	}
}
<?php

namespace yiiunit\extensions\mongo;

/**
 * @group mongo
 */
class CommandTest extends MongoTestCase
{
	protected function tearDown()
	{
		$this->dropCollection('customer');
		parent::tearDown();
	}

	// Tests :

	public function testInsert()
	{
		$command = $this->getConnection()->createCommand();
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $command->insert('customer', $data);
		$this->assertTrue($id instanceof \MongoId);
		$this->assertNotEmpty($id->__toString());
	}

	/**
	 * @depends testInsert
	 */
	public function testFindAll()
	{
		$command = $this->getConnection()->createCommand();
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $command->insert('customer', $data);

		$rows = $command->findAll('customer');
		$this->assertEquals(1, count($rows));
		$this->assertEquals($id, $rows[0]['_id']);
	}

	public function testSave()
	{
		$command = $this->getConnection()->createCommand();
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $command->save('customer', $data);
		$this->assertTrue($id instanceof \MongoId);
		$this->assertNotEmpty($id->__toString());
	}

	/**
	 * @depends testSave
	 */
	public function testUpdate()
	{
		$command = $this->getConnection()->createCommand();
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$newId = $command->save('customer', $data);

		$updatedId = $command->save('customer', $data);
		$this->assertEquals($newId, $updatedId, 'Unable to update data!');

		$data['_id'] = $newId->__toString();
		$updatedId = $command->save('customer', $data);
		$this->assertEquals($newId, $updatedId, 'Unable to updated data by string id!');
	}

	/**
	 * @depends testFindAll
	 */
	public function testRemove()
	{
		$command = $this->getConnection()->createCommand();
		$data = [
			'name' => 'customer 1',
			'address' => 'customer 1 address',
		];
		$id = $command->insert('customer', $data);

		$command->remove('customer', ['_id' => $id]);

		$rows = $command->findAll('customer');
		$this->assertEquals(0, count($rows));
	}
}
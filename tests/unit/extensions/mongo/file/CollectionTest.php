<?php

namespace yiiunit\extensions\mongo\file;

use yiiunit\extensions\mongo\MongoTestCase;

class CollectionTest extends MongoTestCase
{
	protected function tearDown()
	{
		$this->dropFileCollection('fs');
		parent::tearDown();
	}

	// Tests :

	public function testFind()
	{
		$collection = $this->getConnection()->getFileCollection();
		$cursor = $collection->find();
		$this->assertTrue($cursor instanceof \MongoGridFSCursor);
	}

	public function testStoreFile()
	{
		$collection = $this->getConnection()->getFileCollection();

		$filename = __FILE__;
		$id = $collection->storeFile($filename);
		$this->assertTrue($id instanceof \MongoId);

		$files = $this->findAll($collection);
		$this->assertEquals(1, count($files));

		/** @var $file \MongoGridFSFile */
		$file = $files[0];
		$this->assertEquals($filename, $file->getFilename());
		$this->assertEquals(file_get_contents($filename), $file->getBytes());
	}

	public function testStoreBytes()
	{
		$collection = $this->getConnection()->getFileCollection();

		$bytes = 'Test file content';
		$id = $collection->storeBytes($bytes);
		$this->assertTrue($id instanceof \MongoId);

		$files = $this->findAll($collection);
		$this->assertEquals(1, count($files));

		/** @var $file \MongoGridFSFile */
		$file = $files[0];
		$this->assertEquals($bytes, $file->getBytes());
	}

	/**
	 * @depends testStoreBytes
	 */
	public function testGet()
	{
		$collection = $this->getConnection()->getFileCollection();

		$bytes = 'Test file content';
		$id = $collection->storeBytes($bytes);

		$file = $collection->get($id);
		$this->assertTrue($file instanceof \MongoGridFSFile);
		$this->assertEquals($bytes, $file->getBytes());
	}

	/**
	 * @depends testGet
	 */
	public function testDelete()
	{
		$collection = $this->getConnection()->getFileCollection();

		$bytes = 'Test file content';
		$id = $collection->storeBytes($bytes);

		$this->assertTrue($collection->delete($id));

		$file = $collection->get($id);
		$this->assertNull($file);
	}
}
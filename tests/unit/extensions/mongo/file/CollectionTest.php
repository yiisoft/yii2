<?php

namespace yiiunit\extensions\mongo\file;

use yiiunit\extensions\mongo\MongoTestCase;

/**
 * @group mongo
 */
class CollectionTest extends MongoTestCase
{
	protected function tearDown()
	{
		$this->dropFileCollection('fs');
		parent::tearDown();
	}

	// Tests :

	public function testGetChunkCollection()
	{
		$collection = $this->getConnection()->getFileCollection();
		$chunkCollection = $collection->getChunkCollection();
		$this->assertTrue($chunkCollection instanceof \yii\mongo\Collection);
		$this->assertTrue($chunkCollection->mongoCollection instanceof \MongoCollection);
	}

	public function testFind()
	{
		$collection = $this->getConnection()->getFileCollection();
		$cursor = $collection->find();
		$this->assertTrue($cursor instanceof \MongoGridFSCursor);
	}

	public function testInsertFile()
	{
		$collection = $this->getConnection()->getFileCollection();

		$filename = __FILE__;
		$id = $collection->insertFile($filename);
		$this->assertTrue($id instanceof \MongoId);

		$files = $this->findAll($collection);
		$this->assertEquals(1, count($files));

		/** @var $file \MongoGridFSFile */
		$file = $files[0];
		$this->assertEquals($filename, $file->getFilename());
		$this->assertEquals(file_get_contents($filename), $file->getBytes());
	}

	public function testInsertFileContent()
	{
		$collection = $this->getConnection()->getFileCollection();

		$bytes = 'Test file content';
		$id = $collection->insertFileContent($bytes);
		$this->assertTrue($id instanceof \MongoId);

		$files = $this->findAll($collection);
		$this->assertEquals(1, count($files));

		/** @var $file \MongoGridFSFile */
		$file = $files[0];
		$this->assertEquals($bytes, $file->getBytes());
	}

	/**
	 * @depends testInsertFileContent
	 */
	public function testGet()
	{
		$collection = $this->getConnection()->getFileCollection();

		$bytes = 'Test file content';
		$id = $collection->insertFileContent($bytes);

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
		$id = $collection->insertFileContent($bytes);

		$this->assertTrue($collection->delete($id));

		$file = $collection->get($id);
		$this->assertNull($file);
	}
}
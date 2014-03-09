<?php

namespace yiiunit\extensions\mongodb\file;

use Yii;
use yii\helpers\FileHelper;
use yiiunit\extensions\mongodb\MongoDbTestCase;
use yii\mongodb\file\ActiveQuery;
use yiiunit\data\ar\mongodb\file\ActiveRecord;
use yiiunit\data\ar\mongodb\file\CustomerFile;

/**
 * @group mongodb
 */
class ActiveRecordTest extends MongoDbTestCase
{
	/**
	 * @var array[] list of test rows.
	 */
	protected $testRows = [];

	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
		$this->setUpTestRows();
		$filePath = $this->getTestFilePath();
		if (!file_exists($filePath)) {
			FileHelper::createDirectory($filePath);
		}
	}

	protected function tearDown()
	{
		$filePath = $this->getTestFilePath();
		if (file_exists($filePath)) {
			FileHelper::removeDirectory($filePath);
		}
		$this->dropFileCollection(CustomerFile::collectionName());
		parent::tearDown();
	}

	/**
	 * @return string test file path.
	 */
	protected function getTestFilePath()
	{
		return Yii::getAlias('@yiiunit/runtime') . DIRECTORY_SEPARATOR . basename(get_class($this)) . '_' . getmypid();
	}

	/**
	 * Sets up test rows.
	 */
	protected function setUpTestRows()
	{
		$collection = $this->getConnection()->getFileCollection(CustomerFile::collectionName());
		$rows = [];
		for ($i = 1; $i <= 10; $i++) {
			$record = [
				'tag' => 'tag' . $i,
				'status' => $i,
			];
			$content = 'content' . $i;
			$record['_id'] = $collection->insertFileContent($content, $record);
			$record['content'] = $content;
			$rows[] = $record;
		}
		$this->testRows = $rows;
	}

	// Tests :

	public function testFind()
	{
		// find one
		$result = CustomerFile::find();
		$this->assertTrue($result instanceof ActiveQuery);
		$customer = $result->one();
		$this->assertTrue($customer instanceof CustomerFile);

		// find all
		$customers = CustomerFile::find()->all();
		$this->assertEquals(10, count($customers));
		$this->assertTrue($customers[0] instanceof CustomerFile);
		$this->assertTrue($customers[1] instanceof CustomerFile);

		// find by _id
		$testId = $this->testRows[0]['_id'];
		$customer = CustomerFile::find($testId);
		$this->assertTrue($customer instanceof CustomerFile);
		$this->assertEquals($testId, $customer->_id);

		// find by column values
		$customer = CustomerFile::find(['tag' => 'tag5']);
		$this->assertTrue($customer instanceof CustomerFile);
		$this->assertEquals($this->testRows[4]['_id'], $customer->_id);
		$this->assertEquals('tag5', $customer->tag);
		$customer = CustomerFile::find(['tag' => 'unexisting tag']);
		$this->assertNull($customer);

		// find by attributes
		$customer = CustomerFile::find()->where(['status' => 4])->one();
		$this->assertTrue($customer instanceof CustomerFile);
		$this->assertEquals(4, $customer->status);

		// find count, sum, average, min, max, distinct
		$this->assertEquals(10, CustomerFile::find()->count());
		$this->assertEquals(1, CustomerFile::find()->where(['status' => 2])->count());
		$this->assertEquals((1+10)/2*10, CustomerFile::find()->sum('status'));
		$this->assertEquals((1+10)/2, CustomerFile::find()->average('status'));
		$this->assertEquals(1, CustomerFile::find()->min('status'));
		$this->assertEquals(10, CustomerFile::find()->max('status'));
		$this->assertEquals(range(1, 10), CustomerFile::find()->distinct('status'));

		// scope
		$this->assertEquals(1, CustomerFile::find()->activeOnly()->count());

		// asArray
		$testRow = $this->testRows[2];
		$customer = CustomerFile::find()->where(['_id' => $testRow['_id']])->asArray()->one();
		$this->assertEquals($testRow['_id'], $customer['_id']);
		$this->assertEquals($testRow['tag'], $customer['tag']);
		$this->assertEquals($testRow['status'], $customer['status']);

		// indexBy
		$customers = CustomerFile::find()->indexBy('tag')->all();
		$this->assertTrue($customers['tag1'] instanceof CustomerFile);
		$this->assertTrue($customers['tag2'] instanceof CustomerFile);

		// indexBy callable
		$customers = CustomerFile::find()->indexBy(function ($customer) {
			return $customer->status . '-' . $customer->status;
		})->all();
		$this->assertTrue($customers['1-1'] instanceof CustomerFile);
		$this->assertTrue($customers['2-2'] instanceof CustomerFile);
	}

	public function testInsert()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;

		$this->assertTrue($record->isNewRecord);

		$record->save();

		$this->assertTrue($record->_id instanceof \MongoId);
		$this->assertFalse($record->isNewRecord);

		$fileContent = $record->getFileContent();
		$this->assertEmpty($fileContent);
	}

	/**
	 * @depends testInsert
	 */
	public function testInsertFile()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;

		$fileName = __FILE__;
		$record->setAttribute('file', $fileName);

		$record->save();

		$this->assertTrue($record->_id instanceof \MongoId);
		$this->assertFalse($record->isNewRecord);

		$fileContent = $record->getFileContent();
		$this->assertEquals(file_get_contents($fileName), $fileContent);
	}

	/**
	 * @depends testInsert
	 */
	public function testInsertFileContent()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;

		$newFileContent = 'Test new file content';
		$record->setAttribute('newFileContent', $newFileContent);

		$record->save();

		$this->assertTrue($record->_id instanceof \MongoId);
		$this->assertFalse($record->isNewRecord);

		$fileContent = $record->getFileContent();
		$this->assertEquals($newFileContent, $fileContent);
	}

	/**
	 * @depends testInsert
	 */
	public function testUpdate()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;
		$record->save();

		// save
		$record = CustomerFile::find($record->_id);
		$this->assertTrue($record instanceof CustomerFile);
		$this->assertEquals(7, $record->status);
		$this->assertFalse($record->isNewRecord);

		$record->status = 9;
		$record->save();
		$this->assertEquals(9, $record->status);
		$this->assertFalse($record->isNewRecord);
		$record2 = CustomerFile::find($record->_id);
		$this->assertEquals(9, $record2->status);

		// updateAll
		$pk = ['_id' => $record->_id];
		$ret = CustomerFile::updateAll(['status' => 55], $pk);
		$this->assertEquals(1, $ret);
		$record = CustomerFile::find($pk);
		$this->assertEquals(55, $record->status);
	}

	/**
	 * @depends testUpdate
	 * @depends testInsertFileContent
	 */
	public function testUpdateFile()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;
		$newFileContent = 'Test new file content';
		$record->setAttribute('newFileContent', $newFileContent);
		$record->save();

		$updateFileName = __FILE__;
		$record = CustomerFile::find($record->_id);
		$record->setAttribute('file', $updateFileName);
		$record->status = 55;
		$record->save();
		$this->assertEquals(file_get_contents($updateFileName), $record->getFileContent());

		$record2 = CustomerFile::find($record->_id);
		$this->assertEquals($record->status, $record2->status);
		$this->assertEquals(file_get_contents($updateFileName), $record2->getFileContent());
		$this->assertEquals($record->tag, $record2->tag);
	}

	/**
	 * @depends testUpdate
	 * @depends testInsertFileContent
	 */
	public function testUpdateFileContent()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;
		$newFileContent = 'Test new file content';
		$record->setAttribute('newFileContent', $newFileContent);
		$record->save();

		$updateFileContent = 'New updated file content';
		$record = CustomerFile::find($record->_id);
		$record->setAttribute('newFileContent', $updateFileContent);
		$record->status = 55;
		$record->save();
		$this->assertEquals($updateFileContent, $record->getFileContent());

		$record2 = CustomerFile::find($record->_id);
		$this->assertEquals($record->status, $record2->status);
		$this->assertEquals($updateFileContent, $record2->getFileContent());
	}

	/**
	 * @depends testInsertFileContent
	 */
	public function testWriteFile()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;
		$newFileContent = 'Test new file content';
		$record->setAttribute('newFileContent', $newFileContent);
		$record->save();

		$outputFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'out.txt';
		$this->assertTrue($record->writeFile($outputFileName));
		$this->assertEquals($newFileContent, file_get_contents($outputFileName));

		$record2 = CustomerFile::find($record->_id);
		$outputFileName = $this->getTestFilePath() . DIRECTORY_SEPARATOR . 'out_refreshed.txt';
		$this->assertTrue($record2->writeFile($outputFileName));
		$this->assertEquals($newFileContent, file_get_contents($outputFileName));
	}

	/**
	 * @depends testInsertFileContent
	 */
	public function testGetFileResource()
	{
		$record = new CustomerFile;
		$record->tag = 'new new';
		$record->status = 7;
		$newFileContent = 'Test new file content';
		$record->setAttribute('newFileContent', $newFileContent);
		$record->save();

		$fileResource = $record->getFileResource();
		$contents = stream_get_contents($fileResource);
		fclose($fileResource);
		$this->assertEquals($newFileContent, $contents);

		$record2 = CustomerFile::find($record->_id);
		$fileResource = $record2->getFileResource();
		$contents = stream_get_contents($fileResource);
		fclose($fileResource);
		$this->assertEquals($newFileContent, $contents);
	}
}

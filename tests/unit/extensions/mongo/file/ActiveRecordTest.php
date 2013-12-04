<?php

namespace yiiunit\extensions\mongo\file;

use yiiunit\extensions\mongo\MongoTestCase;
use yii\mongo\file\ActiveQuery;
use yiiunit\data\ar\mongo\file\ActiveRecord;
use yiiunit\data\ar\mongo\file\CustomerFile;

/**
 * @group mongo
 */
class ActiveRecordTest extends MongoTestCase
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
	}

	protected function tearDown()
	{
		$this->dropFileCollection(CustomerFile::collectionName());
		parent::tearDown();
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
			$record['_id'] = $collection->storeBytes($content, $record);
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
}
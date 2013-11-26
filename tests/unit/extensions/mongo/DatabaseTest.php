<?php

namespace yiiunit\extensions\mongo;

use yii\mongo\Collection;

/**
 * @group mongo
 */
class DatabaseTest extends MongoTestCase
{
	protected function tearDown()
	{
		$this->dropCollection('customer');
		parent::tearDown();
	}

	// Tests :

	public function testGetCollection()
	{
		$database = $connection = $this->getConnection()->getDatabase();

		$collection = $database->getCollection('customer');
		$this->assertTrue($collection instanceof Collection);
		$this->assertTrue($collection->mongoCollection instanceof \MongoCollection);

		$collection2 = $database->getCollection('customer');
		$this->assertTrue($collection === $collection2);

		$collectionRefreshed = $database->getCollection('customer', true);
		$this->assertFalse($collection === $collectionRefreshed);
	}
}
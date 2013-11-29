<?php

namespace yiiunit\extensions\mongo;

use yii\data\ActiveDataProvider;
use yii\mongo\Query;
use yiiunit\data\ar\mongo\ActiveRecord;
use yiiunit\data\ar\mongo\Customer;

/**
 * @group mongo
 */
class ActiveDataProviderTest extends MongoTestCase
{
	protected function setUp()
	{
		parent::setUp();
		ActiveRecord::$db = $this->getConnection();
		$this->setUpTestRows();
	}

	protected function tearDown()
	{
		$this->dropCollection(Customer::collectionName());
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
				'email' => 'email' . $i,
				'address' => 'address' . $i,
				'status' => $i,
			];
		}
		$collection->batchInsert($rows);
	}

	// Tests :

	public function testQuery()
	{
		$query = new Query;
		$query->from('customer');

		$provider = new ActiveDataProvider([
			'query' => $query,
			'db' => $this->getConnection(),
		]);
		$models = $provider->getModels();
		$this->assertEquals(10, count($models));

		$provider = new ActiveDataProvider([
			'query' => $query,
			'db' => $this->getConnection(),
			'pagination' => [
				'pageSize' => 5,
			]
		]);
		$models = $provider->getModels();
		$this->assertEquals(5, count($models));
	}

	public function testActiveQuery()
	{
		$provider = new ActiveDataProvider([
			'query' => Customer::find()->orderBy('id ASC'),
		]);
		$models = $provider->getModels();
		$this->assertEquals(10, count($models));
		$this->assertTrue($models[0] instanceof Customer);
		$keys = $provider->getKeys();
		$this->assertTrue($keys[0] instanceof \MongoId);

		$provider = new ActiveDataProvider([
			'query' => Customer::find(),
			'pagination' => [
				'pageSize' => 5,
			]
		]);
		$models = $provider->getModels();
		$this->assertEquals(5, count($models));
	}
}
<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\Collection;
use yii\authclient\BaseClient;

class CollectionTest extends TestCase
{
	// Tests :

	public function testSetGet()
	{
		$collection = new Collection();

		$clients = [
			'testClient1' => new TestClient(),
			'testClient2' => new TestClient(),
		];
		$collection->setClients($clients);
		$this->assertEquals($clients, $collection->getClients(), 'Unable to setup clients!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testGetProviderById()
	{
		$collection = new Collection();

		$clientId = 'testClientId';
		$client = new TestClient();
		$clients = [
			$clientId => $client
		];
		$collection->setClients($clients);

		$this->assertEquals($client, $collection->getClient($clientId), 'Unable to get client by id!');
	}

	/**
	 * @depends testGetProviderById
	 */
	public function testCreateProvider()
	{
		$collection = new Collection();

		$clientId = 'testClientId';
		$clientClassName = TestClient::className();
		$clients = [
			$clientId => [
				'class' => $clientClassName
			]
		];
		$collection->setClients($clients);

		$provider = $collection->getClient($clientId);
		$this->assertTrue(is_object($provider), 'Unable to create client by config!');
		$this->assertTrue(is_a($provider, $clientClassName), 'Client has wrong class name!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testHasProvider()
	{
		$collection = new Collection();

		$clientName = 'testClientName';
		$clients = [
			$clientName => [
				'class' => 'TestClient1'
			],
		];
		$collection->setClients($clients);

		$this->assertTrue($collection->hasClient($clientName), 'Existing client check fails!');
		$this->assertFalse($collection->hasClient('unExistingClientName'), 'Not existing client check fails!');
	}
}

class TestClient extends BaseClient
{
}
<?php

namespace yiiunit\extensions\authclient\provider;

use yii\authclient\provider\Collection;
use yii\authclient\provider\ProviderInterface;
use yii\authclient\provider\ProviderTrait;
use yii\base\Object;
use yiiunit\extensions\authclient\TestCase;

class CollectionTest extends TestCase
{
	// Tests :

	public function testSetGet()
	{
		$collection = new Collection();

		$providers = [
			'testProvider1' => new TestProvider(),
			'testProvider2' => new TestProvider(),
		];
		$collection->setProviders($providers);
		$this->assertEquals($providers, $collection->getProviders(), 'Unable to setup providers!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testGetProviderById()
	{
		$collection = new Collection();

		$providerId = 'testProviderId';
		$provider = new TestProvider();
		$providers = [
			$providerId => $provider
		];
		$collection->setProviders($providers);

		$this->assertEquals($provider, $collection->getProvider($providerId), 'Unable to get provider by id!');
	}

	/**
	 * @depends testGetProviderById
	 */
	public function testCreateProvider()
	{
		$collection = new Collection();

		$providerId = 'testProviderId';
		$providerClassName = TestProvider::className();
		$providers = [
			$providerId => [
				'class' => $providerClassName
			]
		];
		$collection->setProviders($providers);

		$provider = $collection->getProvider($providerId);
		$this->assertTrue(is_object($provider), 'Unable to create provider by config!');
		$this->assertTrue(is_a($provider, $providerClassName), 'Provider has wrong class name!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testHasProvider()
	{
		$collection = new Collection();

		$providerName = 'testProviderName';
		$providers = [
			$providerName => [
				'class' => 'TestProvider1'
			],
		];
		$collection->setProviders($providers);

		$this->assertTrue($collection->hasProvider($providerName), 'Existing provider check fails!');
		$this->assertFalse($collection->hasProvider('unExistingProviderName'), 'Not existing provider check fails!');
	}
}

class TestProvider extends Object implements ProviderInterface
{
	use ProviderTrait;

	public function authenticate() {}
}
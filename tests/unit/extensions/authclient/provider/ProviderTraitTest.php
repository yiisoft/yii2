<?php

namespace yiiunit\extensions\authclient\provider;


use yii\authclient\provider\ProviderInterface;
use yii\authclient\provider\ProviderTrait;
use yii\base\Object;
use yiiunit\extensions\authclient\TestCase;

class ProviderTraitTest extends TestCase
{
	protected function setUp()
	{
		$config = [
			'components' => [
				'user' => [
					'identityClass' => '\yii\web\IdentityInterface'
				],
				'request' => [
					'hostInfo' => 'http://testdomain.com',
					'scriptUrl' => '/index.php',
				],
			]
		];
		$this->mockApplication($config, '\yii\web\Application');
	}

	public function testSetGet()
	{
		$provider = new Provider();

		$id = 'test_service_id';
		$provider->setId($id);
		$this->assertEquals($id, $provider->getId(), 'Unable to setup id!');

		$successUrl = 'http://test.success.url';
		$provider->setSuccessUrl($successUrl);
		$this->assertEquals($successUrl, $provider->getSuccessUrl(), 'Unable to setup success URL!');

		$cancelUrl = 'http://test.cancel.url';
		$provider->setCancelUrl($cancelUrl);
		$this->assertEquals($cancelUrl, $provider->getCancelUrl(), 'Unable to setup cancel URL!');
	}

	public function testGetDescriptiveData()
	{
		$provider = new Provider();

		$this->assertNotEmpty($provider->getName(), 'Unable to get name!');
		$this->assertNotEmpty($provider->getTitle(), 'Unable to get title!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testGetDefaultSuccessUrl()
	{
		$provider = new Provider();

		$this->assertNotEmpty($provider->getSuccessUrl(), 'Unable to get default success URL!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testGetDefaultCancelUrl()
	{
		$provider = new Provider();

		$this->assertNotEmpty($provider->getSuccessUrl(), 'Unable to get default cancel URL!');
	}

	public function testRedirect()
	{
		$provider = new Provider();

		$url = 'http://test.url';
		$response = $provider->redirect($url, true);

		$this->assertContains($url, $response->content);
	}
}

class Provider extends Object implements ProviderInterface
{
	use ProviderTrait;

	public function authenticate() {}
}
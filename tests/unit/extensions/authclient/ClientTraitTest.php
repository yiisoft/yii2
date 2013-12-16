<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\ClientInterface;
use yii\authclient\ClientTrait;
use yii\base\Object;

class ClientTraitTest extends TestCase
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
		$provider = new Client();

		$id = 'test_id';
		$provider->setId($id);
		$this->assertEquals($id, $provider->getId(), 'Unable to setup id!');

		$name = 'test_name';
		$provider->setName($name);
		$this->assertEquals($name, $provider->getName(), 'Unable to setup name!');

		$title = 'test_title';
		$provider->setTitle($title);
		$this->assertEquals($title, $provider->getTitle(), 'Unable to setup title!');
	}

	public function testGetDescriptiveData()
	{
		$provider = new Client();

		$this->assertNotEmpty($provider->getName(), 'Unable to get name!');
		$this->assertNotEmpty($provider->getTitle(), 'Unable to get title!');
	}
}

class Client extends Object implements ClientInterface
{
	use ClientTrait;

	public function authenticate() {}
}
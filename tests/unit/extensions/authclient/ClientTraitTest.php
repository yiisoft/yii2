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
		$client = new Client();

		$id = 'test_id';
		$client->setId($id);
		$this->assertEquals($id, $client->getId(), 'Unable to setup id!');

		$name = 'test_name';
		$client->setName($name);
		$this->assertEquals($name, $client->getName(), 'Unable to setup name!');

		$title = 'test_title';
		$client->setTitle($title);
		$this->assertEquals($title, $client->getTitle(), 'Unable to setup title!');

		$userAttributes = [
			'attribute1' => 'value1',
			'attribute2' => 'value2',
		];
		$client->setUserAttributes($userAttributes);
		$this->assertEquals($userAttributes, $client->getUserAttributes(), 'Unable to setup user attributes!');

		$viewOptions = [
			'option1' => 'value1',
			'option2' => 'value2',
		];
		$client->setViewOptions($viewOptions);
		$this->assertEquals($viewOptions, $client->getViewOptions(), 'Unable to setup view options!');
	}

	public function testGetDefaults()
	{
		$provider = new Client();

		$this->assertNotEmpty($provider->getName(), 'Unable to get default name!');
		$this->assertNotEmpty($provider->getTitle(), 'Unable to get default title!');
		$this->assertNotNull($provider->getViewOptions(), 'Unable to get default view options!');
	}
}

class Client extends Object implements ClientInterface
{
	use ClientTrait;
}
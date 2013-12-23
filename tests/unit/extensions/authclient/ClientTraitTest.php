<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\ClientInterface;
use yii\authclient\ClientTrait;
use yii\base\Object;

class ClientTraitTest extends TestCase
{
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

		$normalizeUserAttributeMap = [
			'name' => 'some/name',
			'email' => 'some/email',
		];
		$client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);
		$this->assertEquals($normalizeUserAttributeMap, $client->getNormalizeUserAttributeMap(), 'Unable to setup normalize user attribute map!');

		$viewOptions = [
			'option1' => 'value1',
			'option2' => 'value2',
		];
		$client->setViewOptions($viewOptions);
		$this->assertEquals($viewOptions, $client->getViewOptions(), 'Unable to setup view options!');
	}

	public function testGetDefaults()
	{
		$client = new Client();

		$this->assertNotEmpty($client->getName(), 'Unable to get default name!');
		$this->assertNotEmpty($client->getTitle(), 'Unable to get default title!');
		$this->assertNotNull($client->getViewOptions(), 'Unable to get default view options!');
		$this->assertNotNull($client->getNormalizeUserAttributeMap(), 'Unable to get default normalize user attribute map!');
	}

	/**
	 * @depends testSetGet
	 */
	public function testNormalizeUserAttributes()
	{
		$client = new Client();

		$normalizeUserAttributeMap = [
			'raw/name' => 'name',
			'raw/email' => 'email',
		];
		$client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);
		$rawUserAttributes = [
			'raw/name' => 'name value',
			'raw/email' => 'email value',
		];
		$client->setUserAttributes($rawUserAttributes);
		$normalizedUserAttributes = $client->getUserAttributes();
		$expectedNormalizedUserAttributes = array_combine(array_keys($normalizeUserAttributeMap), array_values($rawUserAttributes));
		$this->assertEquals($expectedNormalizedUserAttributes, $normalizedUserAttributes);
	}
}

class Client extends Object implements ClientInterface
{
	use ClientTrait;
}
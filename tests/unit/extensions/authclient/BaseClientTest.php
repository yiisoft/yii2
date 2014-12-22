<?php

namespace yiiunit\extensions\authclient;

use yii\authclient\BaseClient;

class BaseClientTest extends TestCase
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
     * Data provider for [[testNormalizeUserAttributes()]]
     * @return array test data
     */
    public function dataProviderNormalizeUserAttributes()
    {
        return [
            [
                [
                    'name' => 'raw/name',
                    'email' => 'raw/email',
                ],
                [
                    'raw/name' => 'name value',
                    'raw/email' => 'email value',
                ],
                [
                    'name' => 'name value',
                    'email' => 'email value',
                ],
            ],
            [
                [
                    'name' => function ($attributes) {
                            return $attributes['firstName'] . ' ' . $attributes['lastName'];
                        },
                ],
                [
                    'firstName' => 'John',
                    'lastName' => 'Smith',
                ],
                [
                    'name' => 'John Smith',
                ],
            ],
            [
                [
                    'email' => ['emails', 'prime'],
                ],
                [
                    'emails' => [
                        'prime' => 'some@email.com'
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'email' => ['emails', 0],
                    'secondaryEmail' => ['emails', 1],
                ],
                [
                    'emails' => [
                        'some@email.com',
                    ],
                ],
                [
                    'email' => 'some@email.com',
                ],
            ],
            [
                [
                    'name' => 'file_get_contents',
                ],
                [
                    'file_get_contents' => 'value',
                ],
                [
                    'name' => 'value',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderNormalizeUserAttributes
     *
     * @depends testSetGet
     *
     * @param array $normalizeUserAttributeMap
     * @param array $rawUserAttributes
     * @param array $expectedNormalizedUserAttributes
     */
    public function testNormalizeUserAttributes($normalizeUserAttributeMap, $rawUserAttributes, $expectedNormalizedUserAttributes)
    {
        $client = new Client();
        $client->setNormalizeUserAttributeMap($normalizeUserAttributeMap);

        $client->setUserAttributes($rawUserAttributes);
        $normalizedUserAttributes = $client->getUserAttributes();

        $this->assertEquals(array_merge($rawUserAttributes, $expectedNormalizedUserAttributes), $normalizedUserAttributes);
    }
}

class Client extends BaseClient
{
}

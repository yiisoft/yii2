<?php

namespace yiiunit\framework\rest;

use yii\rest\Serializer;
use yiiunit\TestCase;
use yii\web\Request;

/**
 * @group rest
 */
class SerializerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testSetupExpand()
    {
        $serializer = new Serializer();
        $expand = [
            'name1',
            'name2',
        ];
        $serializer->setExpand($expand);
        $this->assertEquals($expand, $serializer->getExpand());

        $serializer = new Serializer();
        $serializer->request = new Request();
        $serializer->request->setQueryParams(['expand' => 'param1,param2']);
        $this->assertEquals(['param1', 'param2'], $serializer->getExpand());
    }

    public function testSetupFields()
    {
        $serializer = new Serializer();
        $fields = [
            'name1',
            'name2',
        ];
        $serializer->setFields($fields);
        $this->assertEquals($fields, $serializer->getFields());

        $serializer = new Serializer();
        $serializer->request = new Request();
        $serializer->request->setQueryParams(['fields' => 'param1,param2']);
        $this->assertEquals(['param1', 'param2'], $serializer->getFields());
    }
} 
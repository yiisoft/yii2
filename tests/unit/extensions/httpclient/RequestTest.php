<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\Request;

class RequestTest extends TestCase
{
    public function testSetupMethod()
    {
        $request = new Request();

        $method = 'put';
        $request->setMethod($method);
        $this->assertEquals($method, $request->getMethod());
    }

    public function testSetupOptions()
    {
        $request = new Request();

        $options = [
            1 => 'test'
        ];
        $request->setOptions($options);
        $this->assertEquals($options, $request->getOptions());
    }
} 
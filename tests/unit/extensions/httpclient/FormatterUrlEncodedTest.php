<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\FormatterUrlEncoded;
use yii\httpclient\Request;

class FormatterUrlEncodedTest extends TestCase
{
    public function testFormat()
    {
        $document = new Request();
        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];
        $document->setData($data);

        $parser = new FormatterUrlEncoded();
        $this->assertEquals(http_build_query($data), $parser->format($document));
        $this->assertEquals('application/x-www-form-urlencoded', $document->getHeaders()->get('Content-Type'));
    }
} 
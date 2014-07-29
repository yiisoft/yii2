<?php

namespace yiiunit\extensions\httpclient;

use yii\helpers\Json;
use yii\httpclient\FormatterJson;
use yii\httpclient\Request;

class FormatterJsonTest extends TestCase
{
    public function testFormat()
    {
        $document = new Request();
        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];
        $document->setData($data);

        $parser = new FormatterJson();
        $this->assertEquals(Json::encode($data), $parser->format($document));
        $this->assertEquals('application/json; charset=UTF-8', $document->getHeaders()->get('Content-Type'));
    }
} 
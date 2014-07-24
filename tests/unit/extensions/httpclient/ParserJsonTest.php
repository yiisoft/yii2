<?php

namespace yiiunit\extensions\httpclient;

use yii\helpers\Json;
use yii\httpclient\ParserJson;
use yii\httpclient\Response;

class ParserJsonTest extends TestCase
{
    public function testParse()
    {
        $document = new Response();
        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];
        $document->setContent(Json::encode($data));

        $parser = new ParserJson();
        $this->assertEquals($data, $parser->parse($document));
    }
}
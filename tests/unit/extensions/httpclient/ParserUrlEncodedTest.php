<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\ParserUrlEncoded;
use yii\httpclient\Response;

class ParserUrlEncodedTest extends TestCase
{
    public function testParse()
    {
        $document = new Response();
        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];
        $document->setContent(http_build_query($data));

        $parser = new ParserUrlEncoded();
        $this->assertEquals($data, $parser->parse($document));
    }
} 
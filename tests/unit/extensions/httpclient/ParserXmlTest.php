<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\ParserXml;
use yii\httpclient\Response;

class ParserXmlTest extends TestCase
{
    public function testParse()
    {
        $document = new Response();
        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<main>
    <name1>value1</name1>
    <name2>value2</name2>
</main>
XML;
        $document->setContent($xml);

        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];
        $parser = new ParserXml();
        $this->assertEquals($data, $parser->parse($document));
    }
} 
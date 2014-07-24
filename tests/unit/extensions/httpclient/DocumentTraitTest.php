<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\DocumentInterface;
use yii\httpclient\DocumentTrait;
use yii\web\HeaderCollection;

class DocumentTraitTest extends TestCase
{
    public function testSetupHeaders()
    {
        $document = new Document();

        $headers = [
            'header1' => 'value1',
            'header2' => 'value2',
        ];
        $document->setHeaders($headers);

        $this->assertTrue($document->getHeaders() instanceof HeaderCollection);
        $expectedHeaders = [
            'header1' => ['value1'],
            'header2' => ['value2'],
        ];
        $this->assertEquals($expectedHeaders, $document->getHeaders()->toArray());

        $additionalHeaders = [
            'header3' => 'value3'
        ];
        $document->addHeaders($additionalHeaders);

        $expectedHeaders = [
            'header1' => ['value1'],
            'header2' => ['value2'],
            'header3' => ['value3'],
        ];
        $this->assertEquals($expectedHeaders, $document->getHeaders()->toArray());
    }

    public function testSetupFormat()
    {
        $document = new Document();

        $format = 'json';
        $document->setFormat($format);
        $this->assertEquals($format, $document->getFormat());
    }

    public function testSetupBody()
    {
        $document = new Document();
        $content = 'test raw body';
        $document->setContent($content);
        $this->assertEquals($content, $document->getContent());
    }

    public function testSetupBodyFields()
    {
        $document = new Document();
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $document->setData($data);
        $this->assertEquals($data, $document->getData());
    }
}

class Document implements DocumentInterface
{
    use DocumentTrait;
}
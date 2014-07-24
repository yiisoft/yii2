<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\HttpDocumentInterface;
use yii\httpclient\HttpDocumentTrait;
use yii\web\HeaderCollection;

class HttpDocumentTraitTest extends TestCase
{
    public function testSetupHeaders()
    {
        $document = new HttpDocument();

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
        $document = new HttpDocument();

        $format = 'json';
        $document->setFormat($format);
        $this->assertEquals($format, $document->getFormat());
    }

    public function testSetupBody()
    {
        $document = new HttpDocument();
        $content = 'test raw body';
        $document->setContent($content);
        $this->assertEquals($content, $document->getContent());
    }

    public function testSetupBodyFields()
    {
        $document = new HttpDocument();
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $document->setData($data);
        $this->assertEquals($data, $document->getData());
    }
}

class HttpDocument implements HttpDocumentInterface
{
    use HttpDocumentTrait;
}
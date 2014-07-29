<?php

namespace yiiunit\extensions\httpclient;

use yii\httpclient\DocumentInterface;
use yii\httpclient\Response;

class ResponseTest extends TestCase
{
    /**
     * Data provider for [[testDetectFormatByHeaders()]]
     * @return array test data
     */
    public function dataProviderDetectFormatByHeaders()
    {
        return [
            [
                'application/x-www-form-urlencoded',
                DocumentInterface::FORMAT_URLENCODED
            ],
            [
                'application/json',
                DocumentInterface::FORMAT_JSON
            ],
            [
                'text/xml',
                DocumentInterface::FORMAT_XML
            ],
        ];
    }

    /**
     * @dataProvider dataProviderDetectFormatByHeaders
     *
     * @param string $contentType
     * @param string $expectedFormat
     */
    public function testDetectFormatByHeaders($contentType, $expectedFormat)
    {
        $response = new Response();
        $response->setHeaders(['Content-type' => $contentType]);
        $this->assertEquals($expectedFormat, $response->getFormat());
    }

    /**
     * Data provider for [[testDetectFormatByContent()]]
     * @return array test data
     */
    public function dataProviderDetectFormatByContent()
    {
        return [
            [
                'name1=value1&name2=value2',
                DocumentInterface::FORMAT_URLENCODED
            ],
            [
                '{"name1":"value1", "name2":"value2"}',
                DocumentInterface::FORMAT_JSON
            ],
            [
                '<?xml version="1.0" encoding="utf-8"?><root></root>',
                DocumentInterface::FORMAT_XML
            ],
        ];
    }

    /**
     * @dataProvider dataProviderDetectFormatByContent
     *
     * @param string $content
     * @param string $expectedFormat
     */
    public function testDetectFormatByContent($content, $expectedFormat)
    {
        $response = new Response();
        $response->setContent($content);
        $this->assertEquals($expectedFormat, $response->getFormat());
    }
}
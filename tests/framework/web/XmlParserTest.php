<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\XmlParser;
use yiiunit\TestCase;

class XmlParserTest extends TestCase
{
    public function testParse()
    {
        $parser = new XmlParser();

        $rawBody = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<main>
    <name1>value1</name1>
    <name2>value2</name2>
</main>
XML;
        $data = [
            'name1' => 'value1',
            'name2' => 'value2',
        ];

        $this->assertEquals($data, $parser->parse($rawBody, 'application/xml'));
        $this->assertEquals($data, $parser->parse($rawBody, 'text/xml'));
    }
    /**
     * @depends testParse
     */
    public function testParseCData()
    {
        $parser = new XmlParser();

        $rawBody = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<main>
    <name1><![CDATA[<tag>]]></name1>
    <name2><![CDATA[value2]]></name2>
</main>
XML;
        $data = [
            'name1' => '<tag>',
            'name2' => 'value2',
        ];

        $this->assertEquals($data, $parser->parse($rawBody, 'application/xml'));
        $this->assertEquals($data, $parser->parse($rawBody, 'text/xml'));
    }
    /**
     * @depends testParse
     */
    public function testParseEncoding()
    {
        $parser = new XmlParser();

        $rawBody = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<main>
    <enname>test</enname>
    <rusname>тест</rusname>
</main>
XML;
        $result = $parser->parse($rawBody, 'text/xml; charset=windows-1251');

        $this->assertEquals('test', $result['enname']);
        $this->assertNotEquals('тест', $result['rusname']); // UTF characters should be broken during parsing by 'windows-1251'
    }
    /**
     * @see https://github.com/yiisoft/yii2-httpclient/issues/102
     *
     * @depends testParse
     */
    public function testParseGroupTag()
    {
        $parser = new XmlParser();

        $rawBody = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<items>
    <item>
        <id>1</id>
        <name>item1</name>
    </item>
    <item>
        <id>2</id>
        <name>item2</name>
    </item>
</items>
XML;
        $data = [
            'item' => [
                [
                    'id' => '1',
                    'name' => 'item1',
                ],
                [
                    'id' => '2',
                    'name' => 'item2',
                ],
            ],
        ];

        $this->assertEquals($data, $parser->parse($rawBody, 'application/xml'));
        $this->assertEquals($data, $parser->parse($rawBody, 'text/xml'));
    }
}

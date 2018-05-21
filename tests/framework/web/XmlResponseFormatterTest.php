<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\XmlResponseFormatter;
use yiiunit\framework\web\stubs\ModelStub;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @group web
 */
class XmlResponseFormatterTest extends FormatterTest
{
    /**
     * @param array $options
     * @return XmlResponseFormatter
     */
    protected function getFormatterInstance($options = [])
    {
        return new XmlResponseFormatter($options);
    }

    private $xmlHead = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    private function addXmlHead(array $data)
    {
        foreach ($data as &$item) {
            $item[1] = $this->xmlHead . $item[1];
        }

        return $data;
    }

    public function formatScalarDataProvider()
    {
        return $this->addXmlHead([
            [1, "<response>1</response>\n"],
            ['abc', "<response>abc</response>\n"],
            [true, "<response>true</response>\n"],
            [false, "<response>false</response>\n"],
            ['<>', "<response>&lt;&gt;</response>\n"],
        ]);
    }

    public function formatArrayDataProvider()
    {
        return $this->addXmlHead([
            [[], "<response/>\n"],
            [[1, 'abc'], "<response><item>1</item><item>abc</item></response>\n"],
            [[
                'a' => 1,
                'b' => 'abc',
            ], "<response><a>1</a><b>abc</b></response>\n"],
            [[
                1,
                'abc',
                [2, 'def'],
                true,
            ], "<response><item>1</item><item>abc</item><item><item>2</item><item>def</item></item><item>true</item></response>\n"],
            [[
                'a' => 1,
                'b' => 'abc',
                'c' => [2, '<>'],
                'city' => [
                    'value' => 'New York',
                    'xml-attributes' => [
                        'type' => 'metropolitan',
                        'population' => '10000000'
                    ]
                ],
                false,
            ], "<response><a>1</a><b>abc</b><c><item>2</item><item>&lt;&gt;</item></c><city type=\"metropolitan\" population=\"10000000\">New York</city><item>false</item></response>\n"],

            // Checks if empty keys and keys not valid in XML are processed.
            // See https://github.com/yiisoft/yii2/pull/10346/
            [[
                '' => 1,
                '2015-06-18' => '2015-06-18',
                'b:c' => 'b:c',
                'a b c' => 'a b c',
                'äøñ' => 'äøñ',
            ], "<response><item>1</item><item>2015-06-18</item><item>b:c</item><item>a b c</item><äøñ>äøñ</äøñ></response>\n"],
        ]);
    }

    public function formatTraversableObjectDataProvider()
    {
        $expectedXmlForStack = '';

        $postsStack = new \SplStack();

        $postsStack->push(new Post(915, 'record1', [
            'value' => 'New York',
            'xml-attributes' => [
                'type' => 'metropolitan',
                'population' => '10000000'
            ]
        ]));
        $expectedXmlForStack = '<Post><id>915</id><title>record1</title><city type="metropolitan" population="10000000">New York</city></Post>' .
          $expectedXmlForStack;

        $postsStack->push(new Post(456, 'record2'));
        $expectedXmlForStack = '<Post><id>456</id><title>record2</title><city></city></Post>' .
          $expectedXmlForStack;

        $data = [
            [$postsStack, "<response>$expectedXmlForStack</response>\n"],
        ];

        return $this->addXmlHead($data);
    }

    public function formatObjectDataProvider()
    {
        return $this->addXmlHead([
            [new Post(123, 'abc'), "<response><Post><id>123</id><title>abc</title><city></city></Post></response>\n"],
            [[
                new Post(123, 'abc'),
                new Post(456, 'def'),
            ], "<response><Post><id>123</id><title>abc</title><city></city></Post><Post><id>456</id><title>def</title><city></city></Post></response>\n"],
            [[
                new Post(123, '<>'),
                'a' => new Post(456, 'def'),
            ], "<response><Post><id>123</id><title>&lt;&gt;</title><city></city></Post><a><Post><id>456</id><title>def</title><city></city></Post></a></response>\n"],
        ]);
    }

    public function formatModelDataProvider()
    {
        return $this->addXmlHead([
            [
                new ModelStub(['id' => 123, 'title' => 'abc', 'hidden' => 'hidden']),
                "<response><ModelStub><id>123</id><title>abc</title></ModelStub></response>\n",
            ],
        ]);
    }

    public function testCustomRootTag()
    {
        $rootTag = 'custom';
        $formatter = $this->getFormatterInstance([
            'rootTag' => $rootTag,
        ]);

        $this->response->data = 1;
        $formatter->format($this->response);
        $this->assertEquals($this->xmlHead . "<$rootTag>1</$rootTag>\n", $this->response->content);
    }

    public function testRootTagRemoval()
    {
        $formatter = $this->getFormatterInstance([
            'rootTag' => null,
        ]);

        $this->response->data = 1;
        $formatter->format($this->response);
        $this->assertEquals($this->xmlHead . "1\n", $this->response->content);
    }

    public function testNoObjectTags()
    {
        $formatter = $this->getFormatterInstance([
            'useObjectTags' => false,
        ]);

        $this->response->data = new Post(123, 'abc');
        $formatter->format($this->response);
        $this->assertEquals($this->xmlHead . "<response><id>123</id><title>abc</title><city></city></response>\n", $this->response->content);
    }
}

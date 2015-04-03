<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\XmlResponseFormatter;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @group web
 */
class XmlResponseFormatterTest extends FormatterTest
{
    /**
     * @return XmlResponseFormatter
     */
    protected function getFormatterInstance()
    {
        return new XmlResponseFormatter();
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
            [true, "<response>1</response>\n"],
            ["<>", "<response>&lt;&gt;</response>\n"],
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
            ], "<response><item>1</item><item>abc</item><item><item>2</item><item>def</item></item><item>1</item></response>\n"],
            [[
                'a' => 1,
                'b' => 'abc',
                'c' => [2, '<>'],
                true,
            ], "<response><a>1</a><b>abc</b><c><item>2</item><item>&lt;&gt;</item></c><item>1</item></response>\n"],
        ]);
    }

    public function formatObjectDataProvider()
    {
        return $this->addXmlHead([
            [new Post(123, 'abc'), "<response><Post><id>123</id><title>abc</title></Post></response>\n"],
            [[
                new Post(123, 'abc'),
                new Post(456, 'def'),
            ], "<response><Post><id>123</id><title>abc</title></Post><Post><id>456</id><title>def</title></Post></response>\n"],
            [[
                new Post(123, '<>'),
                'a' => new Post(456, 'def'),
            ], "<response><Post><id>123</id><title>&lt;&gt;</title></Post><a><Post><id>456</id><title>def</title></Post></a></response>\n"],
        ]);
    }
}

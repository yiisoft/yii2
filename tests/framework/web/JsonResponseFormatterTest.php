<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\JsonResponseFormatter;
use yiiunit\framework\web\stubs\ModelStub;

/**
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.3
 *
 * @group web
 */
class JsonResponseFormatterTest extends FormatterTest
{
    /**
     * @return JsonResponseFormatter
     */
    protected function getFormatterInstance()
    {
        return new JsonResponseFormatter();
    }

    public function formatScalarDataProvider()
    {
        return [
            [1, 1],
            ['abc', '"abc"'],
            [true, 'true'],
            ['<>', '"<>"'],
        ];
    }

    public function formatArrayDataProvider()
    {
        return [
            // input, json, pretty json
            [[], '[]', '[]'],
            [[1, 'abc'], '[1,"abc"]', "[\n    1,\n    \"abc\"\n]"],
            [
                [
                    'a' => 1,
                    'b' => 'abc',
                ],
                '{"a":1,"b":"abc"}',
                "{\n    \"a\": 1,\n    \"b\": \"abc\"\n}",
            ],
            [
                [
                    1,
                    'abc',
                    [2, 'def'],
                    true,
                ],
                '[1,"abc",[2,"def"],true]',
                "[\n    1,\n    \"abc\",\n    [\n        2,\n        \"def\"\n    ],\n    true\n]",
            ],
            [
                [
                    'a' => 1,
                    'b' => 'abc',
                    'c' => [2, '<>'],
                    true,
                ],
                '{"a":1,"b":"abc","c":[2,"<>"],"0":true}',
                "{\n    \"a\": 1,\n    \"b\": \"abc\",\n    \"c\": [\n        2,\n        \"<>\"\n    ],\n    \"0\": true\n}",
            ],
        ];
    }

    public function formatObjectDataProvider()
    {
        return [
            [new Post(123, 'abc'), '{"id":123,"title":"abc"}'],
            [[
                new Post(123, 'abc'),
                new Post(456, 'def'),
            ], '[{"id":123,"title":"abc"},{"id":456,"title":"def"}]'],
            [[
                new Post(123, '<>'),
                'a' => new Post(456, 'def'),
            ], '{"0":{"id":123,"title":"<>"},"a":{"id":456,"title":"def"}}'],
        ];
    }

    public function formatTraversableObjectDataProvider()
    {
        $postsStack = new \SplStack();
        $postsStack->push(new Post(915, 'record1'));
        $postsStack->push(new Post(456, 'record2'));

        return [
            [$postsStack, '{"1":{"id":456,"title":"record2"},"0":{"id":915,"title":"record1"}}'],
        ];
    }

    public function formatModelDataProvider()
    {
        return [
            [new ModelStub(['id' => 123, 'title' => 'abc', 'hidden' => 'hidden']), '{"id":123,"title":"abc"}'],
        ];
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @param string $prettyJson the expected pretty JSON body
     * @dataProvider formatArrayDataProvider
     */
    public function testFormatArraysPretty($data, $json, $prettyJson)
    {
        $this->response->data = $data;
        $this->formatter->prettyPrint = true;
        $this->formatter->format($this->response);
        $this->assertEquals($prettyJson, $this->response->content);
    }
}

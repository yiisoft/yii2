<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\helpers\Json;
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
    protected function getFormatterInstance($configuration = [])
    {
        return new JsonResponseFormatter($configuration);
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

    public function contentTypeGenerationDataProvider()
    {
        return [
            [
                [
                ],
                'application/json; charset=UTF-8',
            ],
            [
                [
                    'useJsonp' => false,
                ],
                'application/json; charset=UTF-8',
            ],
            [
                [
                    'useJsonp' => true,
                ],
                'application/javascript; charset=UTF-8',
            ],
            [
                [
                    'contentType' => 'application/javascript; charset=UTF-8',
                    'useJsonp' => false,
                ],
                'application/javascript; charset=UTF-8',
            ],
            [
                [
                    'contentType' => 'application/json; charset=UTF-8',
                    'useJsonp' => true,
                ],
                'application/json; charset=UTF-8',
            ],
            [
                [
                    'contentType' => 'application/hal+json; charset=UTF-8',
                    'useJsonp' => false,
                ],
                'application/hal+json; charset=UTF-8',
            ],
            [
                [
                    'contentType' => 'application/hal+json; charset=UTF-8',
                    'useJsonp' => true,
                ],
                'application/hal+json; charset=UTF-8',
            ],
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

    /**
     * @param array $configuration JSON formatter configuration array.
     * @param string $contentTypeExpected Expected value of the response `Content-Type` header.
     * @dataProvider contentTypeGenerationDataProvider
     */
    public function testContentTypeGeneration($configuration, $contentTypeExpected)
    {
        $formatter = $this->getFormatterInstance($configuration);
        $formatter->format($this->response);
        $contentTypeActual = $this->response->headers->get('Content-Type');

        $this->assertEquals($contentTypeExpected, $contentTypeActual);
    }

    /**
     * Formatter must return 'null' string.
     */
    public function testFormatNull()
    {
        $this->response->data = null;
        $this->formatter->format($this->response);
        $this->assertEquals('null', $this->response->content);
    }

    /**
     * Formatter must return early sets content,
     * e.g. content may be sets by PageCache filter
     */
    public function testFormatFilledContent()
    {
        $content = '{"text": "early seted content"}';
        $this->response->data = null;
        $this->response->content = $content;
        $this->formatter->format($this->response);
        $this->assertEquals($content, $this->response->content);
    }

    /**
     * Formatter configuration keepObjectType affects how zero-indexed objects are encoded
     */
    public function testFormatZeroIndexedObjectKeepObject()
    {
        $formatter = $this->getFormatterInstance([
            'keepObjectType' => true,
        ]);
        $this->response->data = (object)['test'];
        $formatter->format($this->response);
        $this->assertEquals('{"0":"test"}', $this->response->content);
    }

    /**
     * Formatter configuration keepObjectType affects how zero-indexed objects are encoded
     */
    public function testFormatZeroIndexedObjectAllowArray()
    {
        $formatter = $this->getFormatterInstance([
            'keepObjectType' => false,
        ]);
        $this->response->data = (object)['test'];
        $formatter->format($this->response);
        $this->assertEquals('["test"]', $this->response->content);
    }

    /**
     * Formatter configuration keepObjectType reverts Json::$keepObjectType to its previous value
     */
    public function testFormatCleanupKeepObjectType()
    {
        $default = Json::$keepObjectType;
        Json::$keepObjectType = false;
        $formatter = $this->getFormatterInstance([
            'keepObjectType' => true,
        ]);
        $this->response->data = (object)['test'];
        $formatter->format($this->response);
        $this->assertFalse(Json::$keepObjectType);
        Json::$keepObjectType = $default;
    }
}

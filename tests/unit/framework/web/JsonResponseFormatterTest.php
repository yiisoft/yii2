<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\base\Object;
use yii\web\JsonResponseFormatter;
use yii\web\Response;

class Post extends Object
{
    public $id;
    public $title;

    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}

/**
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.3
 *
 * @group web
 */
class JsonResponseFormatterTest extends \yiiunit\TestCase
{
    /**
     * @var Response
     */
    public $response;
    /**
     * @var JsonResponseFormatter
     */
    public $formatter;

    protected function setUp()
    {
        $this->mockApplication();
        $this->response = new Response;
        $this->formatter = new JsonResponseFormatter;
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatScalarDataProvider
     */
    public function testFormatScalar($data, $json)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    public function formatScalarDataProvider()
    {
        return [
            [null, 'null'],
            [1, 1],
            ['abc', '"abc"'],
            [true, 'true'],
            ["<>", '"<>"'],
        ];
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatArrayDataProvider
     */
    public function testFormatArrays($data, $json)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
    }

    public function formatArrayDataProvider()
    {
        return [
            [[], "[]"],
            [[1, 'abc'], '[1,"abc"]'],
            [[
                'a' => 1,
                'b' => 'abc',
            ], '{"a":1,"b":"abc"}'],
            [[
                1,
                'abc',
                [2, 'def'],
                true,
            ], '[1,"abc",[2,"def"],true]'],
            [[
                'a' => 1,
                'b' => 'abc',
                'c' => [2, '<>'],
                true,
            ], '{"a":1,"b":"abc","c":[2,"<>"],"0":true}'],
        ];
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $json the expected JSON body
     * @dataProvider formatObjectDataProvider
     */
    public function testFormatObjects($data, $json)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEquals($json, $this->response->content);
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
}

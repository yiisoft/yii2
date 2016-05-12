<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\JsonpResponseFormatter;
use yiiunit\framework\web\stubs\ModelStub;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.1
 *
 * @group web
 */
class JsonpResponseFormatterTest extends FormatterTest
{
    /**
     * @return JsonResponseFormatter
     */
    protected function getFormatterInstance()
    {
        return new JsonpResponseFormatter([
            'callback' => 'process'
        ]);
    }

    public function formatScalarDataProvider()
    {
        return [
            [1, 'process(1);'],
            ['abc', 'process("abc");'],
            [true, 'process(true);'],
            ["<>", 'process("\u003C\u003E");'],
        ];
    }

    public function formatArrayDataProvider()
    {
        return [
            // input, response
            [[], "process([]);"],
            [[1, 'abc'], 'process([1,"abc"]);'],
            [[
                'a' => 1,
                '1<33' => 1 < 33,
            ], 'process({"a":1,"1\u003C33":true});'],
            [[
                1,
                'abc',
                [2, 'def'],
                true,
            ], 'process([1,"abc",[2,"def"],true]);'],
            [[
                'a' => 1,
                'b' => 'abc',
                'c' => [2, '<>'],
                true,
            ], 'process({"a":1,"b":"abc","c":[2,"\u003C\u003E"],"0":true});'],
        ];
    }

    public function formatObjectDataProvider()
    {
        return [
            [new Post(123, 'abc'), 'process({"id":123,"title":"abc"});'],
            [[
                new Post(123, 'abc'),
                new Post(456, 'def'),
            ], 'process([{"id":123,"title":"abc"},{"id":456,"title":"def"}]);'],
            [[
                new Post(123, '<>'),
                'a' => new Post(456, 'def'),
            ], 'process({"0":{"id":123,"title":"\u003C\u003E"},"a":{"id":456,"title":"def"}});'],
        ];
    }

    public function formatTraversableObjectDataProvider()
    {
        $postsStack = new \SplStack();
        $postsStack->push(new Post(915, 'record1'));
        $postsStack->push(new Post(456, 'record2'));

        return [
            [$postsStack, 'process({"1":{"id":456,"title":"record2"},"0":{"id":915,"title":"record1"}});']
        ];
    }

    public function formatModelDataProvider()
    {
        return [
            [new ModelStub(['id' => 123, 'title' => 'abc', 'hidden' => 'hidden']), 'process({"id":123,"title":"abc"});']
        ];
    }

    public function testFormatNull()
    {
        $this->response->data = null;
        $this->formatter->format($this->response);
        $this->assertEquals('process(null);', $this->response->content);
    }

    public function testCallbackParam()
    {
        \Yii::$app->set('request', new \yii\web\Request());
        $formatter = new JsonpResponseFormatter();

        $_GET['callback'] = 'jQuery_random_callback';
        $this->response->data = 3426;
        $formatter->format($this->response);
        $this->assertEquals('jQuery_random_callback(3426);', $this->response->content);

        $this->response->data = ['1<33' => 1 < 33];
        $formatter->format($this->response);
        $this->assertEquals('jQuery_random_callback({"1\u003C33":true});', $this->response->content);

        $_GET['callback'] = 'jQuery_3426';
        $this->response->data = new Post(123, 'mdmunir');
        $formatter->format($this->response);
        $this->assertEquals('jQuery_3426({"id":123,"title":"mdmunir"});', $this->response->content);
    }
}

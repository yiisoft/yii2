<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\JsonResponseFormatter;

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
            ["<>", '"<>"'],
        ];
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

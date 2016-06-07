<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\web\CsvResponseFormatter;
use yii\web\Response;
use yiiunit\framework\web\stubs\ModelStub;

/**
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.9
 *
 * @group web
 * @property CsvResponseFormatter $formatter
 */
class CsvResponseFormatterTest extends FormatterTest
{
    /**
     * @return CsvResponseFormatter
     */
    protected function getFormatterInstance(array $config = [])
    {
        return new CsvResponseFormatter($config);
    }

    public function formatArrayDataProvider()
    {
        return [
            // input, csv
            [[], ""],
            [[[1, 'abc']], "1,abc\n"],
            [[[
                "a" => 1,
                "b" => 2
            ]], "a,b\n1,2\n"]
        ];
    }

    public function formatSparseArrayDataProvider()
    {
        return [
            // input, csv
            [[], ""],
            [[
                ['a' => 1, 'b' => 'abc'],
                ['b' => 1, 'c' => 'abc']
            ], "a,b,c\n1,abc,{{missing}}\n{{missing}},1,abc\n"]
        ];
    }


    public function formatObjectDataProvider()
    {
        return [
            [[new Post(123, 'abc')], "123,abc\n"],
            [[
                new Post(123, 'abc'),
                new Post(456, 'def'),
            ], "123,abc\n456,def\n"],
            [[
                new Post(123, '<>'),
                'a' => new Post(456, 'def'),
            ], "123,<>\n456,def\n"],
        ];
    }

    public function formatScalarDataProvider()
    {
        $this->markTestSkipped("Not applicable");
    }

    public function formatTraversableObjectDataProvider()
    {
        $postsStack = new \SplStack();
        $postsStack->push(new Post(915, 'record1'));
        $postsStack->push(new Post(456, 'record2'));

        return [
            [$postsStack, "456,record2\n915,record1\n"]
        ];
    }

    public function formatModelDataProvider()
    {
        return [
            [[new ModelStub(['id' => 123, 'title' => 'abc', 'hidden' => 'hidden'])], "123,abc\n"]
        ];
    }


    /**
     * Formatter should format null using `$formatter->nullValue`
     */
    public function testFormatNull()
    {
        $this->response->data = [[null]];
        $this->formatter->format($this->response);
        $this->assertEqualsReplace($this->formatter, $this->formatter->nullValue . "\n");
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $csv the expected CSV body
     * @dataProvider formatArrayDataProvider
     */
    public function testFormatArrays($data, $csv)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEqualsReplace($this->formatter, $csv);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $csv the expected CSV body
     * @dataProvider formatTraversableObjectDataProvider
     */
    public function testFormatTraversableObjects($data, $csv)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEqualsReplace($this->formatter, $csv);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $csv the expected CSV body
     * @dataProvider formatObjectDataProvider
     */
    public function testFormatObjects($data, $csv)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEqualsReplace($this->formatter, $csv);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $csv the expected body
     * @dataProvider formatModelDataProvider
     */
    public function testFormatModels($data, $csv)
    {
        $this->response->data = $data;
        $this->formatter->format($this->response);
        $this->assertEqualsReplace($this->formatter, $csv);
    }

    /**
     * @param mixed  $data the data to be formatted
     * @param string $csv the expected CSV body
     * @dataProvider formatSparseArrayDataProvider
     */
    public function testFormatSparseArrays($data, $csv)
    {
        $this->response->data = $data;
        $formatter = $this->getFormatterInstance([
            'checkAllRows' => true
        ]);
        $formatter->format($this->response);
        $this->assertEqualsReplace($formatter, $csv);

    }

    protected function assertEqualsReplace(CsvResponseFormatter $formatter, $expected)
    {
        $this->assertEquals(strtr($expected, [
            '{{missing}}' => $formatter->missingValue,
            '{{null}}' => $formatter->nullValue,
        ]), stream_get_contents($this->response->stream));
    }

}

<?php
namespace yiiunit\framework\helpers;

use yii\helpers\VarDumper;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class VarDumperTest extends TestCase
{
    public function testDumpObject()
    {
        $obj = new \StdClass();
        ob_start();
        VarDumper::dump($obj);
        $this->assertEquals("stdClass#1\n(\n)", ob_get_contents());
        ob_end_clean();
    }

    /**
     * Data provider for [[testExport()]]
     * @return array test data
     */
    public function dataProviderExport()
    {
        // Regular :

        $data = [
            [
                'test string',
                var_export('test string', true)
            ],
            [
                75,
                var_export(75, true)
            ],
            [
                7.5,
                var_export(7.5, true)
            ],
            [
                null,
                'null'
            ],
            [
                true,
                'true'
            ],
            [
                false,
                'false'
            ],
            [
                [],
                '[]'
            ],
        ];

        // Arrays :

        $var = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $expectedResult = <<<RESULT
[
    'key1' => 'value1',
    'key2' => 'value2',
]
RESULT;
        $data[] = [$var, $expectedResult];

        $var = [
            'value1',
            'value2',
        ];
        $expectedResult = <<<RESULT
[
    'value1',
    'value2',
]
RESULT;
        $data[] = [$var, $expectedResult];

        // Objects :

        $var = new \StdClass();
        $var->testField = 'Test Value';
        $expectedResult = "unserialize('" . serialize($var) . "')";
        $data[] = [$var, $expectedResult];

        $var = new \StdClass();
        $var->testFunction = function () {return 2;};
        $expectedResult = var_export($var, true);
        $data[] = [$var, $expectedResult];

        return $data;
    }

    /**
     * @dataProvider dataProviderExport
     *
     * @param mixed $var
     * @param string $expectedResult
     */
    public function testExport($var, $expectedResult)
    {
        $exportResult = VarDumper::export($var);
        $this->assertEqualsWithoutLE($expectedResult, $exportResult);
        //$this->assertEquals($var, eval('return ' . $exportResult . ';'));
    }
}

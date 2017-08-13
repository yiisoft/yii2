<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\VarDumper;
use yiiunit\data\helpers\CustomDebugInfo;
use yiiunit\TestCase;

/**
 * @group helpers
 */
class VarDumperTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();
    }

    public function testDumpIncompleteObject()
    {
        $serializedObj = 'O:16:"nonExistingClass":0:{}';
        $incompleteObj = unserialize($serializedObj);
        $dumpResult = VarDumper::dumpAsString($incompleteObj);
        $this->assertContains("__PHP_Incomplete_Class#1\n(", $dumpResult);
        $this->assertContains('nonExistingClass', $dumpResult);
    }

    public function testExportIncompleteObject()
    {
        $serializedObj = 'O:16:"nonExistingClass":0:{}';
        $incompleteObj = unserialize($serializedObj);
        $exportResult = VarDumper::export($incompleteObj);
        $this->assertContains('nonExistingClass', $exportResult);
    }

    public function testDumpObject()
    {
        $obj = new \StdClass();
        $this->assertEquals("stdClass#1\n(\n)", VarDumper::dumpAsString($obj));

        $obj = new \StdClass();
        $obj->name = 'test-name';
        $obj->price = 19;
        $dumpResult = VarDumper::dumpAsString($obj);
        $this->assertContains("stdClass#1\n(", $dumpResult);
        $this->assertContains("[name] => 'test-name'", $dumpResult);
        $this->assertContains('[price] => 19', $dumpResult);
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
                var_export('test string', true),
            ],
            [
                75,
                var_export(75, true),
            ],
            [
                7.5,
                var_export(7.5, true),
            ],
            [
                null,
                'null',
            ],
            [
                true,
                'true',
            ],
            [
                false,
                'false',
            ],
            [
                [],
                '[]',
            ],
        ];

        // Arrays :

        $var = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $expectedResult = <<<'RESULT'
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
        $expectedResult = <<<'RESULT'
[
    'value1',
    'value2',
]
RESULT;
        $data[] = [$var, $expectedResult];

        $var = [
            'key1' => [
                'subkey1' => 'value2',
            ],
            'key2' => [
                'subkey2' => 'value3',
            ],
        ];
        $expectedResult = <<<'RESULT'
[
    'key1' => [
        'subkey1' => 'value2',
    ],
    'key2' => [
        'subkey2' => 'value3',
    ],
]
RESULT;
        $data[] = [$var, $expectedResult];

        // Objects :

        $var = new \StdClass();
        $var->testField = 'Test Value';
        $expectedResult = "unserialize('" . serialize($var) . "')";
        $data[] = [$var, $expectedResult];

        $var = function () {return 2;};
        $expectedResult = 'function () {return 2;}';
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

    /**
     * @depends testExport
     */
    public function testExportObjectFallback()
    {
        $var = new \StdClass();
        $var->testFunction = function () {return 2;};
        $exportResult = VarDumper::export($var);
        $this->assertNotEmpty($exportResult);

        $master = new \StdClass();
        $slave = new \StdClass();
        $master->slave = $slave;
        $slave->master = $master;
        $master->function = function () {return true;};

        $exportResult = VarDumper::export($master);
        $this->assertNotEmpty($exportResult);
    }

    /**
     * @depends testDumpObject
     */
    public function testDumpClassWithCustomDebugInfo()
    {
        $object = new CustomDebugInfo();
        $object->volume = 10;
        $object->unitPrice = 15;

        $dumpResult = VarDumper::dumpAsString($object);
        $this->assertContains('totalPrice', $dumpResult);
        $this->assertNotContains('unitPrice', $dumpResult);
    }
}

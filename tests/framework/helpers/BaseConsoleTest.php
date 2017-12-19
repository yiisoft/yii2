<?php
namespace yiiunit\framework\helpers;

use yiiunit\TestCase;
use yii\helpers\BaseConsole;

/**
 * Unit test for [[yii\helpers\BaseConsole]]
 *
 * @see BaseConsole
 * @group helpers
 */
class BaseConsoleTest extends TestCase
{
    /**
     * @test
     */
    public function renderColoredString()
    {
        $data = '%yfoo';
        $actual = BaseConsole::renderColoredString($data);
        $expected = "\033[33mfoo";
        $this->assertEquals($expected, $actual);
        
        $actual = BaseConsole::renderColoredString($data, false);
        $expected = "foo";
        $this->assertEquals($expected, $actual);
    }
}

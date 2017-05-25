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
    
    /**
     * @test
     */
    public function progress()
    {
        $expected = '50% (1/2) ETA: 1495723175 sec.';
        /*        ob_start();
        ob_implicit_flush(false);
        BaseConsole::endProgress();
        $actual =ob_get_clean();
        $actual = $this->getActualOutput();
        $this->assertEquals($expected, $actual);*/
        /*        ob_start();
                ob_implicit_flush(false);
                BaseConsole::startProgress(0, 1000);
                BaseConsole::updateProgress(2, 1000);
                BaseConsole::endProgress();
                $actual =ob_get_clean();
                $actual = file_get_contents('application.log');
                $this->assertEquals($expected, $actual);*/
    }
}

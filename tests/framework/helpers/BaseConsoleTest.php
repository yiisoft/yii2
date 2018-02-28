<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use yii\helpers\BaseConsole;
use yiiunit\TestCase;

/**
 * Unit test for [[yii\helpers\BaseConsole]].
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
        $expected = 'foo';
        $this->assertEquals($expected, $actual);
    }
}

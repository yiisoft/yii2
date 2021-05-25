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
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

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
    public function ansiColorizedSubstr_withoutColors()
    {
        $str = 'FooBar';

        $actual = BaseConsole::ansiColorizedSubstr($str, 0, 3);
        $expected = BaseConsole::renderColoredString('Foo');
        $this->assertEquals($expected, $actual);

        $actual = BaseConsole::ansiColorizedSubstr($str, 3, 3);
        $expected = BaseConsole::renderColoredString('Bar');
        $this->assertEquals($expected, $actual);

        $actual = BaseConsole::ansiColorizedSubstr($str, 1, 4);
        $expected = BaseConsole::renderColoredString('ooBa');
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider ansiColorizedSubstr_withColors_data
     * @param $str
     * @param $start
     * @param $length
     * @param $expected
     */
    public function ansiColorizedSubstr_withColors($str, $start, $length, $expected)
    {
        $ansiStr = BaseConsole::renderColoredString($str);

        $ansiActual = BaseConsole::ansiColorizedSubstr($ansiStr, $start, $length);
        $ansiExpected = BaseConsole::renderColoredString($expected);
        $this->assertEquals($ansiExpected, $ansiActual);
    }

    public function ansiColorizedSubstr_withColors_data()
    {
        return [
            ['%rFoo%gBar%n', 0, 3, '%rFoo%n'],
            ['%rFoo%gBar%n', 3, 3, '%gBar%n'],
            ['%rFoo%gBar%n', 1, 4, '%roo%gBa%n'],
            ['Foo%yBar%nYes', 1, 7, 'oo%yBar%nYe'],
            ['Foo%yBar%nYes', 5, 3, '%yr%nYe'],
        ];
    }

    public function testAnsiStrlen()
    {
        $this->assertSame(3, BaseConsole::ansiStrlen('Foo'));
        $this->assertSame(3, BaseConsole::ansiStrlen(BaseConsole::renderColoredString('Bar%y')));
    }
}

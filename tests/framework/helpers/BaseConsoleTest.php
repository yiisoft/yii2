<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

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
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * @test
     */
    public function renderColoredString(): void
    {
        $data = '%yfoo';
        $actual = BaseConsole::renderColoredString($data);
        $expected = "\033[33mfoo";
        $this->assertSame($expected, $actual);

        $actual = BaseConsole::renderColoredString($data, false);
        $expected = 'foo';
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function ansiColorizedSubstrWithoutColors(): void
    {
        $str = 'FooBar';

        $actual = BaseConsole::ansiColorizedSubstr($str, 0, 3);
        $expected = BaseConsole::renderColoredString('Foo');
        $this->assertSame($expected, $actual);

        $actual = BaseConsole::ansiColorizedSubstr($str, 3, 3);
        $expected = BaseConsole::renderColoredString('Bar');
        $this->assertSame($expected, $actual);

        $actual = BaseConsole::ansiColorizedSubstr($str, 1, 4);
        $expected = BaseConsole::renderColoredString('ooBa');
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider ansiColorizedSubstrWithColorsData
     * @param $str
     * @param $start
     * @param $length
     * @param $expected
     */
    public function ansiColorizedSubstrWithColors($str, $start, $length, $expected): void
    {
        $ansiStr = BaseConsole::renderColoredString($str);

        $ansiActual = BaseConsole::ansiColorizedSubstr($ansiStr, $start, $length);
        $ansiExpected = BaseConsole::renderColoredString($expected);
        $this->assertSame($ansiExpected, $ansiActual);
    }

    public static function ansiColorizedSubstrWithColorsData(): array
    {
        return [
            ['%rFoo%gBar%n', 0, 3, '%rFoo%n'],
            ['%rFoo%gBar%n', 3, 3, '%gBar%n'],
            ['%rFoo%gBar%n', 1, 4, '%roo%gBa%n'],
            ['Foo%yBar%nYes', 1, 7, 'oo%yBar%nYe'],
            ['Foo%yBar%nYes', 5, 3, '%yr%nYe'],
        ];
    }

    public function testAnsiStrlen(): void
    {
        $this->assertSame(3, BaseConsole::ansiStrlen('Foo'));
        $this->assertSame(3, BaseConsole::ansiStrlen(BaseConsole::renderColoredString('Bar%y')));
        $this->assertSame(4, BaseConsole::ansiStrlen('тест'));
    }

    public function testAnsiStrwidth(): void
    {
        $this->assertSame(3, BaseConsole::ansiStrwidth('Foo'));
        $this->assertSame(2, BaseConsole::ansiStrwidth('中'));
        $this->assertSame(4, BaseConsole::ansiStrwidth('中中'));
    }

    public function testAnsiFormat(): void
    {
        $this->assertSame("\033[0m\033[31mhello\033[0m", BaseConsole::ansiFormat('hello', [BaseConsole::FG_RED]));
        $this->assertSame("\033[0mhello\033[0m", BaseConsole::ansiFormat('hello'));
    }

    public function testAnsiColorizedSubstrEdgeCases(): void
    {
        $this->assertSame('', BaseConsole::ansiColorizedSubstr('test', -1, 3));
        $this->assertSame('', BaseConsole::ansiColorizedSubstr('test', 0, 0));
    }

    public function testAnsiColorizedSubstrMultiByte(): void
    {
        $this->assertSame('Те', BaseConsole::ansiColorizedSubstr('Тест', 0, 2));
        $this->assertSame('ст', BaseConsole::ansiColorizedSubstr('Тест', 2, 2));

        $str = BaseConsole::renderColoredString('%rТест%gОк%n');
        $actual = BaseConsole::ansiColorizedSubstr($str, 2, 4);
        $expected = BaseConsole::renderColoredString('%rст%gОк%n');
        $this->assertSame($expected, $actual);

        $str = BaseConsole::renderColoredString('%rТест%gABC%n');
        $actual = BaseConsole::ansiColorizedSubstr($str, 4, 2);
        $expected = BaseConsole::renderColoredString('%gAB%n');
        $this->assertSame($expected, $actual);
    }

    public function testMarkdownToAnsi(): void
    {
        $result = BaseConsole::markdownToAnsi('**bold** text');
        $this->assertStringContainsString('bold', BaseConsole::stripAnsiFormat($result));
    }

    public function testRenderColoredStringAllCodes(): void
    {
        $this->assertSame("\033[33m", BaseConsole::renderColoredString('%y'));
        $this->assertSame("\033[32m", BaseConsole::renderColoredString('%g'));
        $this->assertSame("\033[34m", BaseConsole::renderColoredString('%b'));
        $this->assertSame("\033[31m", BaseConsole::renderColoredString('%r'));
        $this->assertSame("\033[35m", BaseConsole::renderColoredString('%p'));
        $this->assertSame("\033[35m", BaseConsole::renderColoredString('%m'));
        $this->assertSame("\033[36m", BaseConsole::renderColoredString('%c'));
        $this->assertSame("\033[37m", BaseConsole::renderColoredString('%w'));
        $this->assertSame("\033[30m", BaseConsole::renderColoredString('%k'));
        $this->assertSame("\033[0m", BaseConsole::renderColoredString('%n'));

        $this->assertSame("\033[33;1m", BaseConsole::renderColoredString('%Y'));
        $this->assertSame("\033[32;1m", BaseConsole::renderColoredString('%G'));
        $this->assertSame("\033[34;1m", BaseConsole::renderColoredString('%B'));
        $this->assertSame("\033[31;1m", BaseConsole::renderColoredString('%R'));
        $this->assertSame("\033[35;1m", BaseConsole::renderColoredString('%P'));
        $this->assertSame("\033[35;1m", BaseConsole::renderColoredString('%M'));
        $this->assertSame("\033[36;1m", BaseConsole::renderColoredString('%C'));
        $this->assertSame("\033[37;1m", BaseConsole::renderColoredString('%W'));
        $this->assertSame("\033[30;1m", BaseConsole::renderColoredString('%K'));
        $this->assertSame("\033[0;1m", BaseConsole::renderColoredString('%N'));

        $this->assertSame("\033[43m", BaseConsole::renderColoredString('%3'));
        $this->assertSame("\033[42m", BaseConsole::renderColoredString('%2'));
        $this->assertSame("\033[44m", BaseConsole::renderColoredString('%4'));
        $this->assertSame("\033[41m", BaseConsole::renderColoredString('%1'));
        $this->assertSame("\033[45m", BaseConsole::renderColoredString('%5'));
        $this->assertSame("\033[46m", BaseConsole::renderColoredString('%6'));
        $this->assertSame("\033[47m", BaseConsole::renderColoredString('%7'));
        $this->assertSame("\033[40m", BaseConsole::renderColoredString('%0'));

        $this->assertSame("\033[5m", BaseConsole::renderColoredString('%F'));
        $this->assertSame("\033[4m", BaseConsole::renderColoredString('%U'));
        $this->assertSame("\033[7m", BaseConsole::renderColoredString('%8'));
        $this->assertSame("\033[1m", BaseConsole::renderColoredString('%9'));
        $this->assertSame("\033[1m", BaseConsole::renderColoredString('%_'));

        $this->assertSame('%', BaseConsole::renderColoredString('%%'));
    }

    public function testEscape(): void
    {
        $this->assertSame('%%test%%', BaseConsole::escape('%test%'));
        $this->assertSame('no escape', BaseConsole::escape('no escape'));
    }

    public function testStreamSupportsAnsiColors(): void
    {
        $stream = fopen('php://memory', 'w+');
        $this->assertIsBool(BaseConsole::streamSupportsAnsiColors($stream));
        fclose($stream);
    }

    public function testIsRunningOnWindows(): void
    {
        $this->assertSame(DIRECTORY_SEPARATOR === '\\', BaseConsole::isRunningOnWindows());
    }

    public function testGetScreenSize(): void
    {
        $result = BaseConsole::getScreenSize(true);
        if ($result !== false) {
            $this->assertIsArray($result);
            $this->assertCount(2, $result);
            $this->assertGreaterThan(0, $result[0]);
            $this->assertGreaterThan(0, $result[1]);
        }

        $this->assertSame($result, BaseConsole::getScreenSize(false));
    }

    public function testStdout(): void
    {
        $this->assertSame(0, BaseConsole::stdout(''));
    }

    public function testStderr(): void
    {
        $this->assertSame(0, BaseConsole::stderr(''));
    }
}

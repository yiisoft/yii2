<?php

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Console;
use yiiunit\TestCase;

/**
 * @group helpers
 * @group console
 */
class ConsoleTest extends TestCase
{
    public function testStripAnsiFormat()
    {
        ob_start();
        ob_implicit_flush(false);
        echo 'a';
        Console::moveCursorForward(1);
        echo 'a';
        Console::moveCursorDown(1);
        echo 'a';
        Console::moveCursorUp(1);
        echo 'a';
        Console::moveCursorBackward(1);
        echo 'a';
        Console::moveCursorNextLine(1);
        echo 'a';
        Console::moveCursorPrevLine(1);
        echo 'a';
        Console::moveCursorTo(1);
        echo 'a';
        Console::moveCursorTo(1, 2);
        echo 'a';
        Console::clearLine();
        echo 'a';
        Console::clearLineAfterCursor();
        echo 'a';
        Console::clearLineBeforeCursor();
        echo 'a';
        Console::clearScreen();
        echo 'a';
        Console::clearScreenAfterCursor();
        echo 'a';
        Console::clearScreenBeforeCursor();
        echo 'a';
        Console::scrollDown();
        echo 'a';
        Console::scrollUp();
        echo 'a';
        Console::hideCursor();
        echo 'a';
        Console::showCursor();
        echo 'a';
        Console::saveCursorPosition();
        echo 'a';
        Console::restoreCursorPosition();
        echo 'a';
        Console::beginAnsiFormat([Console::FG_GREEN, Console::BG_BLUE, Console::UNDERLINE]);
        echo 'a';
        Console::endAnsiFormat();
        echo 'a';
        Console::beginAnsiFormat([Console::xtermBgColor(128), Console::xtermFgColor(55)]);
        echo 'a';
        Console::endAnsiFormat();
        echo 'a';
        $output = Console::stripAnsiFormat(ob_get_clean());
        ob_implicit_flush(true);
        // $output = str_replace("\033", 'X003', $output );// uncomment for debugging
        $this->assertEquals(str_repeat('a', 25), $output);
    }

/*	public function testScreenSize()
    {
        for ($i = 1; $i < 20; $i++) {
            echo implode(', ', Console::getScreenSize(true)) . "\n";
            ob_flush();
            sleep(1);
        }
    }*/

    public function ansiFormats()
    {
        return [
            ['test', 'test'],
            [Console::ansiFormat('test', [Console::FG_RED]), '<span style="color: red;">test</span>'],
            ['abc' . Console::ansiFormat('def', [Console::FG_RED]) . 'ghj', 'abc<span style="color: red;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::FG_RED, Console::BG_GREEN]) . 'ghj', 'abc<span style="color: red;background-color: lime;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::FG_GREEN, Console::FG_RED, Console::BG_GREEN]) . 'ghj', 'abc<span style="color: red;background-color: lime;">def</span>ghj'],
            ['abc' . Console::ansiFormat('def', [Console::BOLD, Console::BG_GREEN]) . 'ghj', 'abc<span style="font-weight: bold;background-color: lime;">def</span>ghj'],

            [
                Console::ansiFormat('test', [Console::UNDERLINE, Console::OVERLINED, Console::CROSSED_OUT, Console::FG_GREEN]),
                '<span style="text-decoration: underline overline line-through;color: lime;">test</span>'
            ],

            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]), ''],
            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]) . 'test', 'test'],
            [Console::ansiFormatCode([Console::RESET]) . 'test' . Console::ansiFormatCode([Console::RESET]), 'test'],

            [
                Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET, Console::FG_GREEN]) . 'ghj' . Console::ansiFormatCode([Console::RESET]),
                '<span style="font-weight: bold;">abc</span><span style="color: lime;">ghj</span>'
            ],
            [
                Console::ansiFormatCode([Console::FG_GREEN]) . ' a ' . Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET]) . 'ghj',
                '<span style="color: lime;"> a <span style="font-weight: bold;">abc</span></span>ghj'
            ],
            [
                Console::ansiFormat('test', [Console::FG_GREEN, Console::BG_BLUE, Console::NEGATIVE]),
                '<span style="background-color: lime;color: blue;">test</span>'
            ],
            [
                Console::ansiFormat('test', [Console::NEGATIVE]),
                'test'
            ],
            [
                Console::ansiFormat('test', [Console::CONCEALED]),
                '<span style="visibility: hidden;">test</span>'
            ],
        ];
    }

    /**
     * @dataProvider ansiFormats
     */
    public function testAnsi2Html($ansi, $html)
    {
        $this->assertEquals($html, Console::ansiToHtml($ansi));
    }
}

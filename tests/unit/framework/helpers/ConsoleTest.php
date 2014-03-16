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
        $ouput = Console::stripAnsiFormat(ob_get_clean());
        ob_implicit_flush(true);
        // $output = str_replace("\033", 'X003', $ouput );// uncomment for debugging
        $this->assertEquals(str_repeat('a', 25), $ouput);
    }

/*	public function testScreenSize()
    {
        for ($i = 1; $i < 20; $i++) {
            echo implode(', ', Console::getScreenSize(true)) . "\n";
            ob_flush();
            sleep(1);
        }
    }*/
}

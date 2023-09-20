<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers;

use Yii;
use yii\helpers\Console;
use yiiunit\TestCase;
use yii\base\DynamicModel;

/**
 * @group helpers
 * @group console
 */
class ConsoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Helper must work without Yii::$app
        $this->destroyApplication();

        $this->setupStreams();
    }

    /**
     * Set up streams for Console helper stub
     */
    protected function setupStreams()
    {
        ConsoleStub::$inputStream = fopen('php://memory', 'w+');
        ConsoleStub::$outputStream = fopen('php://memory', 'w+');
        ConsoleStub::$errorStream = fopen('php://memory', 'w+');
    }

    /**
     * Clean streams in Console helper stub
     */
    protected function truncateStreams()
    {
        ftruncate(ConsoleStub::$inputStream, 0);
        rewind(ConsoleStub::$inputStream);
        ftruncate(ConsoleStub::$outputStream, 0);
        rewind(ConsoleStub::$outputStream);
        ftruncate(ConsoleStub::$errorStream, 0);
        rewind(ConsoleStub::$errorStream);
    }

    /**
     * Read data from Console helper stream, defaults to output stream
     *
     * @param resource $stream
     * @return string
     */
    protected function readOutput($stream = null)
    {
        if ($stream === null) {
            $stream = ConsoleStub::$outputStream;
        }

        rewind($stream);

        $output = '';

        while (!feof($stream) && ($buffer = fread($stream, 1024)) !== false) {
            $output .= $buffer;
        }

        return $output;
    }

    /**
     * Write passed arguments to Console helper input stream and rewind the position
     * of a input stream pointer
     */
    protected function sendInput()
    {
        fwrite(ConsoleStub::$inputStream, implode(PHP_EOL, func_get_args()) . PHP_EOL);

        rewind(ConsoleStub::$inputStream);
    }

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

    /*public function testScreenSize()
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
                '<span style="text-decoration: underline overline line-through;color: lime;">test</span>',
            ],

            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]), ''],
            [Console::ansiFormatCode([Console::RESET]) . Console::ansiFormatCode([Console::RESET]) . 'test', 'test'],
            [Console::ansiFormatCode([Console::RESET]) . 'test' . Console::ansiFormatCode([Console::RESET]), 'test'],

            [
                Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET, Console::FG_GREEN]) . 'ghj' . Console::ansiFormatCode([Console::RESET]),
                '<span style="font-weight: bold;">abc</span><span style="color: lime;">ghj</span>',
            ],
            [
                Console::ansiFormatCode([Console::FG_GREEN]) . ' a ' . Console::ansiFormatCode([Console::BOLD]) . 'abc' . Console::ansiFormatCode([Console::RESET]) . 'ghj',
                '<span style="color: lime;"> a <span style="font-weight: bold;">abc</span></span>ghj',
            ],
            [
                Console::ansiFormat('test', [Console::FG_GREEN, Console::BG_BLUE, Console::NEGATIVE]),
                '<span style="background-color: lime;color: blue;">test</span>',
            ],
            [
                Console::ansiFormat('test', [Console::NEGATIVE]),
                'test',
            ],
            [
                Console::ansiFormat('test', [Console::CONCEALED]),
                '<span style="visibility: hidden;">test</span>',
            ],
        ];
    }

    /**
     * @dataProvider ansiFormats
     * @param string $ansi
     * @param string $html
     */
    public function testAnsi2Html($ansi, $html)
    {
        $this->assertEquals($html, Console::ansiToHtml($ansi));
    }

    public function testErrorSummary()
    {
        $model = new TestConsoleModel();
        $model->name = 'not_an_integer';
        $model->addError('name', 'Error message. Here are some chars: < >');
        $model->addError('name', 'Error message. Here are even more chars: ""');
        $model->validate(null, false);
        $options = ['showAllErrors' => true];
        $expectedHtml =  "Error message. Here are some chars: < >\nError message. Here are even more chars: \"\"";
        $this->assertEqualsWithoutLE($expectedHtml, Console::errorSummary($model, $options));
    }

    /**
     * @covers \yii\helpers\BaseConsole::input()
     */
    public function testInput()
    {
        $this->sendInput('test1');
        $result = ConsoleStub::input();
        $this->assertEquals('test1', $result);
        $this->assertEmpty($this->readOutput());
        $this->truncateStreams();

        $this->sendInput('test2');
        $result = ConsoleStub::input('MyPrompt');
        $this->assertEquals('test2', $result);
        $this->assertEquals('MyPrompt', $this->readOutput());
        $this->truncateStreams();
    }

    /**
     * @covers \yii\helpers\BaseConsole::output()
     */
    public function testOutput()
    {
        $result = ConsoleStub::output('Smth');
        $this->assertEquals('Smth' . PHP_EOL, $this->readOutput());
        $this->assertEmpty($this->readOutput(ConsoleStub::$errorStream));
    }

    /**
     * @covers \yii\helpers\BaseConsole::error()
     */
    public function testError()
    {
        $result = ConsoleStub::error('SomeError');
        $this->assertEquals('SomeError' . PHP_EOL, $this->readOutput(ConsoleStub::$errorStream));
        $this->assertEmpty($this->readOutput());
    }

    /**
     * @covers \yii\helpers\BaseConsole::prompt()
     */
    public function testPrompt()
    {
        // testing output variations

        $this->sendInput('smth');
        ConsoleStub::prompt('Testing prompt');
        $this->assertEquals('Testing prompt ', $this->readOutput());
        $this->truncateStreams();

        $this->sendInput('smth');
        ConsoleStub::prompt('Testing prompt with default', ['default' => 'myDefault']);
        $this->assertEquals('Testing prompt with default [myDefault] ', $this->readOutput());
        $this->truncateStreams();

        // testing base successful scenario
        $this->sendInput('cat');
        $result = ConsoleStub::prompt('Check clear input');
        $this->assertEquals('cat', $result);
        $this->truncateStreams();

        // testing applying default value ("default" param)
        $this->sendInput('');
        $result = ConsoleStub::prompt('No input with default', ['default' => 'x']);
        $this->assertEquals('x', $result);
        $this->truncateStreams();

        // testing requiring value ("required" param)
        $this->sendInput('', 'smth');
        $result = ConsoleStub::prompt('SmthRequired', ['required' => true]);
        $this->assertEquals('SmthRequired Invalid input.' . PHP_EOL . 'SmthRequired ', $this->readOutput());
        $this->assertEquals('smth', $result);
        $this->truncateStreams();

        // testing custom error text ("error" param)
        $this->sendInput('', 'smth');
        $result = ConsoleStub::prompt('TestCustomError', ['required' => true, 'error' => 'ThisOne']);
        $this->assertEquals('TestCustomError ThisOne' . PHP_EOL . 'TestCustomError ', $this->readOutput());
        $this->assertEquals('smth', $result);
        $this->truncateStreams();

        // testing pattern check ("pattern" param)
        $this->sendInput('cat', '15');
        $result = ConsoleStub::prompt('SmthDigit', ['pattern' => '/^\d+$/']);
        $this->assertEquals('SmthDigit Invalid input.' . PHP_EOL . 'SmthDigit ', $this->readOutput());
        $this->assertEquals('15', $result);
        $this->truncateStreams();

        // testing custom callable check ("validator" param)
        $this->sendInput('cat', '15');
        $result = ConsoleStub::prompt('SmthNumeric', ['validator' => function ($value, &$error) {
            return is_numeric($value);
        }]);
        $this->assertEquals('SmthNumeric Invalid input.' . PHP_EOL . 'SmthNumeric ', $this->readOutput());
        $this->assertEquals('15', $result);
        $this->truncateStreams();

        // testing custom callable check with custom error message
        $this->sendInput('cat', '15');
        $result = ConsoleStub::prompt('SmthNumeric', [
            'validator' => function ($value, &$error) {
                if (!$response = is_numeric($value)) {
                    $error = 'RealCustomError';
                }

                return $response;
            },
            'error' => 'ExternalError',
        ]);
        $this->assertEquals('SmthNumeric RealCustomError' . PHP_EOL . 'SmthNumeric ', $this->readOutput());
        $this->assertEquals('15', $result);
        $this->truncateStreams();

        // testing combined options
        $this->sendInput('14', '15');
        $result = ConsoleStub::prompt('Combined', [
            'required' => true,
            'default' => 'kraken',
            'pattern' => '/^\d+$/',
            'validator' => function ($value, &$error) {
                return $value == 15;
            },
            'error' => 'CustomError',
        ]);
        $this->assertEquals('Combined [kraken] CustomError' . PHP_EOL . 'Combined [kraken] ', $this->readOutput());
        $this->assertEquals('15', $result);
        $this->truncateStreams();
    }

    /**
     * @covers \yii\helpers\BaseConsole::confirm()
     */
    public function testConfirm()
    {
        $this->sendInput('y');
        ConsoleStub::confirm('Are you sure?');
        $this->assertEquals('Are you sure? (yes|no) [no]:', $this->readOutput());
        $this->truncateStreams();

        $this->sendInput('');
        $result = ConsoleStub::confirm('Are you sure?', true);
        $this->assertEquals('Are you sure? (yes|no) [yes]:', $this->readOutput());
        $this->assertTrue($result);
        $this->truncateStreams();

        $this->sendInput('');
        $result = ConsoleStub::confirm('Are you sure?', false);
        $this->assertEquals('Are you sure? (yes|no) [no]:', $this->readOutput());
        $this->assertFalse($result);
        $this->truncateStreams();

        foreach ([
                     'y' => true,
                     'Y' => true,
                     'yes' => true,
                     'YeS' => true,
                     'n' => false,
                     'N' => false,
                     'no' => false,
                     'NO' => false,
                     'WHAT?!' . PHP_EOL . 'yes' => true,
                 ] as $currInput => $currAssertion) {
            $this->sendInput($currInput);
            $result = ConsoleStub::confirm('Are you sure?');
            $this->assertEquals($currAssertion, $result, $currInput);
            $this->truncateStreams();
        }
    }

    /**
     * @covers \yii\helpers\BaseConsole::select()
     */
    public function testSelect()
    {
        $options = [
            'c' => 'cat',
            'd' => 'dog',
            'm' => 'mouse',
        ];

        $this->sendInput('c');
        $result = ConsoleStub::select('Usual behavior', $options);
        $this->assertEquals('Usual behavior (c,d,m,?): ', $this->readOutput());
        $this->assertEquals('c', $result);
        $this->truncateStreams();

        $this->sendInput('x', 'd');
        $result = ConsoleStub::select('Wrong character', $options);
        $this->assertEquals('Wrong character (c,d,m,?): Wrong character (c,d,m,?): ', $this->readOutput());
        $this->assertEquals('d', $result);
        $this->truncateStreams();

        $this->sendInput('?', 'm');
        $result = ConsoleStub::select('Using help', $options);
        $this->assertEquals(
            'Using help (c,d,m,?): '
            . ' c - cat'
            . PHP_EOL
            . ' d - dog'
            . PHP_EOL
            . ' m - mouse'
            . PHP_EOL
            . ' ? - Show help'
            . PHP_EOL
            . 'Using help (c,d,m,?): ',
            $this->readOutput()
        );
        $this->truncateStreams();

        $this->sendInput('');
        $result = ConsoleStub::select('Use Default', $options, 'm');
        $this->assertEquals('m', $result);
        $this->truncateStreams();

        $this->sendInput('', 'd');
        $result = ConsoleStub::select('Empty without Default', $options);
        $this->assertEquals('Empty without Default (c,d,m,?): Empty without Default (c,d,m,?): ', $this->readOutput());
        $this->assertEquals('d', $result);
        $this->truncateStreams();
    }
}

/**
 * @property string name
 * @property array types
 * @property string description
 */
class TestConsoleModel extends DynamicModel
{
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100]
        ];
    }

    public function init()
    {
        $this->defineAttribute('name');
    }
}

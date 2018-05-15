<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\LogLevel;
use yii\log\Logger;
use yii\log\Target;
use yiiunit\TestCase;

/**
 * @group log
 */
class TargetTest extends TestCase
{
    public static $messages;

    public function filters()
    {
        return [
            [[], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],

            [['levels' => []], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],
            [
                ['levels' => [LogLevel::INFO, LogLevel::WARNING, LogLevel::ERROR, LogLevel::DEBUG]],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'],
            ],
            [['levels' => ['error']], ['B', 'G', 'H', 'I']],
            [['levels' => [LogLevel::ERROR]], ['B', 'G', 'H', 'I']],
            [['levels' => ['error', 'warning']], ['B', 'C', 'G', 'H', 'I']],
            [['levels' => [LogLevel::ERROR, LogLevel::WARNING]], ['B', 'C', 'G', 'H', 'I']],

            [['categories' => ['application']], ['A', 'B', 'C', 'D', 'E']],
            [['categories' => ['application*']], ['A', 'B', 'C', 'D', 'E', 'F']],
            [['categories' => ['application.*']], ['F']],
            [['categories' => ['application.components']], []],
            [['categories' => ['application.components.Test']], ['F']],
            [['categories' => ['application.components.*']], ['F']],
            [['categories' => ['application.*', 'yii.db.*']], ['F', 'G', 'H']],
            [['categories' => ['application.*', 'yii.db.*'], 'except' => ['yii.db.Command.*', 'yii\db\*']], ['F', 'G']],
            [['except' => ['yii\db\*']], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            [['categories' => ['yii*'], 'except' => ['yii\db\*']], ['G', 'H']],

            [['categories' => ['application', 'yii.db.*'], 'levels' => [LogLevel::ERROR]], ['B', 'G', 'H']],
            [['categories' => ['application'], 'levels' => [LogLevel::ERROR]], ['B']],
            [['categories' => ['application'], 'levels' => [LogLevel::ERROR, LogLevel::WARNING]], ['B', 'C']],
        ];
    }

    /**
     * @dataProvider filters
     * @param array $filter
     * @param array $expected
     */
    public function testFilter($filter, $expected)
    {
        static::$messages = [];

        $logger = new Logger([
            'targets' => [new TestTarget(array_merge($filter, ['logVars' => []]))],
            'flushInterval' => 1,
        ]);
        $logger->log(LogLevel::INFO, 'testA');
        $logger->log(LogLevel::ERROR, 'testB');
        $logger->log(LogLevel::WARNING, 'testC');
        $logger->log(LogLevel::DEBUG, 'testD');
        $logger->log(LogLevel::INFO, 'testE', ['category' => 'application']);
        $logger->log(LogLevel::INFO, 'testF', ['category' => 'application.components.Test']);
        $logger->log(LogLevel::ERROR, 'testG', ['category' => 'yii.db.Command']);
        $logger->log(LogLevel::ERROR, 'testH', ['category' => 'yii.db.Command.whatever']);
        $logger->log(LogLevel::ERROR, 'testI', ['category' => 'yii\db\Command::query']);

        $this->assertEquals(count($expected), count(static::$messages), 'Expected ' . implode(',', $expected) . ', got ' . implode(',', array_column(static::$messages, 0)));
        $i = 0;
        foreach ($expected as $e) {
            $this->assertEquals('test' . $e, static::$messages[$i++][1]);
        }
    }

    public function testGetContextMessage()
    {
        $target = new TestTarget([
            'logVars' => [
                'A', '!A.A_b', 'A.A_d',
                'B.B_a',
                'C', 'C.C_a',
                'D',
            ],
        ]);
        $GLOBALS['A'] = [
            'A_a' => 1,
            'A_b' => 1,
            'A_c' => 1,
        ];
        $GLOBALS['B'] = [
            'B_a' => 1,
            'B_b' => 1,
            'B_c' => 1,
        ];
        $GLOBALS['C'] = [
            'C_a' => 1,
            'C_b' => 1,
            'C_c' => 1,
        ];
        $GLOBALS['E'] = [
            'C_a' => 1,
            'C_b' => 1,
            'C_c' => 1,
        ];
        $context = $target->getContextMessage();
        $this->assertContains('A_a', $context);
        $this->assertNotContains('A_b', $context);
        $this->assertContains('A_c', $context);
        $this->assertContains('B_a', $context);
        $this->assertNotContains('B_b', $context);
        $this->assertNotContains('B_c', $context);
        $this->assertContains('C_a', $context);
        $this->assertContains('C_b', $context);
        $this->assertContains('C_c', $context);
        $this->assertNotContains('D_a', $context);
        $this->assertNotContains('D_b', $context);
        $this->assertNotContains('D_c', $context);
        $this->assertNotContains('E_a', $context);
        $this->assertNotContains('E_b', $context);
        $this->assertNotContains('E_c', $context);
    }

    public function testGetEnabled()
    {
        /** @var Target $target */
        $target = $this->getMockForAbstractClass('yii\\log\\Target');

        $target->enabled = true;
        $this->assertTrue($target->enabled);

        $target->enabled = false;
        $this->assertFalse($target->enabled);

        $target->enabled = function ($target) {
            return empty($target->messages);
        };
        $this->assertTrue($target->enabled);
    }

    public function testFormatMessage()
    {
        /** @var Target $target */
        $target = $this->getMockForAbstractClass('yii\\log\\Target');

        $text = 'message';
        $level = LogLevel::INFO;
        $category = 'application';
        $timestamp = 1508160390.6083;

        $expectedWithoutMicro = '2017-10-16 13:26:30 [info][application] message';
        $formatted = $target->formatMessage([$level, $text, ['category' => $category, 'time' => $timestamp]]);
        $this->assertSame($expectedWithoutMicro, $formatted);

        $target->microtime = true;

        $expectedWithMicro = '2017-10-16 13:26:30.6083 [info][application] message';
        $formatted = $target->formatMessage([$level, $text, ['category' => $category, 'time' => $timestamp]]);
        $this->assertSame($expectedWithMicro, $formatted);

        $timestamp = 1508160390;

        $expectedWithoutMicro = '2017-10-16 13:26:30 [info][application] message';
        $formatted = $target->formatMessage([$level, $text, ['category' => $category, 'time' => $timestamp]]);
        $this->assertSame($expectedWithoutMicro, $formatted);
    }
}

class TestTarget extends Target
{
    public $exportInterval = 1;

    /**
     * Exports log [[messages]] to a specific destination.
     * Child classes must implement this method.
     */
    public function export()
    {
        TargetTest::$messages = array_merge(TargetTest::$messages, $this->messages);
        $this->messages = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getContextMessage()
    {
        return parent::getContextMessage();
    }
}

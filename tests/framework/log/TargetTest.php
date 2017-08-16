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
            [[], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],

            [['levels' => []], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            [
                ['levels' => [LogLevel::INFO, LogLevel::WARNING, LogLevel::ERROR, LogLevel::DEBUG]],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
            ],
            [['levels' => ['error']], ['B', 'G', 'H']],
            [['levels' => [LogLevel::ERROR]], ['B', 'G', 'H']],
            [['levels' => ['error', 'warning']], ['B', 'C', 'G', 'H']],
            [['levels' => [LogLevel::ERROR, LogLevel::WARNING]], ['B', 'C', 'G', 'H']],

            [['categories' => ['application']], ['A', 'B', 'C', 'D', 'E']],
            [['categories' => ['application*']], ['A', 'B', 'C', 'D', 'E', 'F']],
            [['categories' => ['application.*']], ['F']],
            [['categories' => ['application.components']], []],
            [['categories' => ['application.components.Test']], ['F']],
            [['categories' => ['application.components.*']], ['F']],
            [['categories' => ['application.*', 'yii.db.*']], ['F', 'G', 'H']],
            [['categories' => ['application.*', 'yii.db.*'], 'except' => ['yii.db.Command.*']], ['F', 'G']],

            [['categories' => ['application', 'yii.db.*'], 'levels' => [LogLevel::ERROR]], ['B', 'G', 'H']],
            [['categories' => ['application'], 'levels' => [LogLevel::ERROR]], ['B']],
            [['categories' => ['application'], 'levels' => [LogLevel::ERROR, LogLevel::WARNING]], ['B', 'C']],
        ];
    }

    /**
     * @dataProvider filters
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

        $this->assertEquals(count($expected), count(static::$messages));
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
     * @inheritdoc
     */
    public function getContextMessage()
    {
        return parent::getContextMessage();
    }
}

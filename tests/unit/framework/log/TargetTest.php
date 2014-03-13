<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\log;

use yii\log\Logger;
use yii\log\Target;
use yiiunit\TestCase;

class TargetTest extends TestCase
{
    public static $messages;

    public function filters()
    {
        return [
            [[], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],

            [['levels' => 0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            [
                ['levels' => Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_ERROR | Logger::LEVEL_TRACE],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']
            ],
            [['levels' => ['error']], ['B', 'G', 'H']],
            [['levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H']],
            [['levels' => ['error', 'warning']], ['B', 'C', 'G', 'H']],
            [['levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C', 'G', 'H']],

            [['categories' => ['application']], ['A', 'B', 'C', 'D', 'E']],
            [['categories' => ['application*']], ['A', 'B', 'C', 'D', 'E', 'F']],
            [['categories' => ['application.*']], ['F']],
            [['categories' => ['application.components']], []],
            [['categories' => ['application.components.Test']], ['F']],
            [['categories' => ['application.components.*']], ['F']],
            [['categories' => ['application.*', 'yii.db.*']], ['F', 'G', 'H']],
            [['categories' => ['application.*', 'yii.db.*'], 'except' => ['yii.db.Command.*']], ['F', 'G']],

            [['categories' => ['application', 'yii.db.*'], 'levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR], ['B']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C']],
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
        $logger->log('testA', Logger::LEVEL_INFO);
        $logger->log('testB', Logger::LEVEL_ERROR);
        $logger->log('testC', Logger::LEVEL_WARNING);
        $logger->log('testD', Logger::LEVEL_TRACE);
        $logger->log('testE', Logger::LEVEL_INFO, 'application');
        $logger->log('testF', Logger::LEVEL_INFO, 'application.components.Test');
        $logger->log('testG', Logger::LEVEL_ERROR, 'yii.db.Command');
        $logger->log('testH', Logger::LEVEL_ERROR, 'yii.db.Command.whatever');

        $this->assertEquals(count($expected), count(static::$messages));
        $i = 0;
        foreach ($expected as $e) {
            $this->assertEquals('test' . $e, static::$messages[$i++][0]);
        }
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
}

<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use yii\log\Dispatcher;
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

            [['levels' => 0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            [
                ['levels' => Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_ERROR | Logger::LEVEL_TRACE],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
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

        $logger = new Logger();
        $dispatcher = new Dispatcher([
            'logger' => $logger,
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

    /**
     * @covers \yii\log\Target::setLevels()
     * @covers \yii\log\Target::getLevels()
     */
    public function testSetupLevelsThroughArray()
    {
        $target = $this->getMockForAbstractClass('yii\\log\\Target');

        $target->setLevels(['info', 'error']);
        $this->assertEquals(Logger::LEVEL_INFO | Logger::LEVEL_ERROR, $target->getLevels());

        $target->setLevels(['trace']);
        $this->assertEquals(Logger::LEVEL_TRACE, $target->getLevels());

        $this->expectException('yii\\base\\InvalidConfigException');
        $this->expectExceptionMessage('Unrecognized level: unknown level');
        $target->setLevels(['info', 'unknown level']);
    }

    /**
     * @covers \yii\log\Target::setLevels()
     * @covers \yii\log\Target::getLevels()
     */
    public function testSetupLevelsThroughBitmap()
    {
        $target = $this->getMockForAbstractClass('yii\\log\\Target');

        $target->setLevels(Logger::LEVEL_INFO | Logger::LEVEL_WARNING);
        $this->assertEquals(Logger::LEVEL_INFO | Logger::LEVEL_WARNING, $target->getLevels());

        $target->setLevels(Logger::LEVEL_TRACE);
        $this->assertEquals(Logger::LEVEL_TRACE, $target->getLevels());

        $this->expectException('yii\\base\\InvalidConfigException');
        $this->expectExceptionMessage('Incorrect 128 value');
        $target->setLevels(128);
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

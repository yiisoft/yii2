<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
            [[], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],

            [['levels' => 0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],
            [
                ['levels' => Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_ERROR | Logger::LEVEL_TRACE],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'],
            ],
            [['levels' => ['error']], ['B', 'G', 'H', 'I']],
            [['levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H', 'I']],
            [['levels' => ['error', 'warning']], ['B', 'C', 'G', 'H', 'I']],
            [['levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C', 'G', 'H', 'I']],

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

            [['categories' => ['application', 'yii.db.*'], 'levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR], ['B']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C']],
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
        $logger->log('testI', Logger::LEVEL_ERROR, 'yii\db\Command::query');

        $messageColumn = [];
        foreach (static::$messages as $message) {
            $messageColumn[] = $message[0];
        }

        $this->assertEquals(count($expected), count(static::$messages), 'Expected ' . implode(',', $expected) . ', got ' . implode(',', $messageColumn));
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
            'maskVars' => [
                'C.C_b',
                'D.D_a'
            ]
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
            'C_b' => 'mySecret',
            'C_c' => 1,
        ];
        $GLOBALS['E'] = [
            'C_a' => 1,
            'C_b' => 1,
            'C_c' => 1,
        ];
        $context = $target->getContextMessage();
        $this->assertStringContainsString('A_a', $context);
        $this->assertStringNotContainsString('A_b', $context);
        $this->assertStringContainsString('A_c', $context);
        $this->assertStringContainsString('B_a', $context);
        $this->assertStringNotContainsString('B_b', $context);
        $this->assertStringNotContainsString('B_c', $context);
        $this->assertStringContainsString('C_a', $context);
        $this->assertStringContainsString('C_b', $context);
        $this->assertStringContainsString('C_c', $context);
        $this->assertStringNotContainsString('D_a', $context);
        $this->assertStringNotContainsString('D_b', $context);
        $this->assertStringNotContainsString('D_c', $context);
        $this->assertStringNotContainsString('E_a', $context);
        $this->assertStringNotContainsString('E_b', $context);
        $this->assertStringNotContainsString('E_c', $context);
        $this->assertStringNotContainsString('mySecret', $context);
        $this->assertStringContainsString('***', $context);
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

        date_default_timezone_set('UTC');

        $text = 'message';
        $level = Logger::LEVEL_INFO;
        $category = 'application';
        $timestamp = 1508160390.6083;

        $expectedWithoutMicro = '2017-10-16 13:26:30 [info][application] message';
        $formatted = $target->formatMessage([$text, $level, $category, $timestamp]);
        $this->assertSame($expectedWithoutMicro, $formatted);

        $target->microtime = true;

        $expectedWithMicro = '2017-10-16 13:26:30.608300 [info][application] message';
        $formatted = $target->formatMessage([$text, $level, $category, $timestamp]);
        $this->assertSame($expectedWithMicro, $formatted);

        $timestamp = 1508160390;

        $expectedWithMicro = '2017-10-16 13:26:30.000000 [info][application] message';
        $formatted = $target->formatMessage([$text, $level, $category, $timestamp]);
        $this->assertSame($expectedWithMicro, $formatted);
    }

    public function testCollectMessageStructure()
    {
        $target = new TestTarget(['logVars' => ['_SERVER']]);
        static::$messages = [];

        $messages = [
            ['test', 1, 'application', 1560428356.212978, [], 1888416]
        ];

        $target->collect($messages, false);

        $this->assertCount(2, static::$messages);
        $this->assertCount(6, static::$messages[0]);
        $this->assertCount(6, static::$messages[1]);
    }

    public function testBreakProfilingWithFlushWithProfilingDisabled()
    {
        $dispatcher = $this->getMockBuilder('yii\log\Dispatcher')
            ->setMethods(['dispatch'])
            ->getMock();
        $dispatcher->expects($this->once())->method('dispatch')->with($this->callback(function ($messages) {
            return count($messages) === 2
                && $messages[0][0] === 'token.a'
                && $messages[0][1] == Logger::LEVEL_PROFILE_BEGIN
                && $messages[1][0] === 'info';
        }), false);

        $logger = new Logger([
            'dispatcher' => $dispatcher,
            'flushInterval' => 2,
        ]);

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('info', Logger::LEVEL_INFO, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testNotBreakProfilingWithFlushWithProfilingEnabled()
    {
        $dispatcher = $this->getMockBuilder('yii\log\Dispatcher')
            ->setMethods(['dispatch'])
            ->getMock();
        $dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            [
                $this->callback(function ($messages) {
                    return count($messages) === 1 && $messages[0][0] === 'info';
                }),
                false
            ],
            [
                $this->callback(function ($messages) {
                    return count($messages) === 2
                        && $messages[0][0] === 'token.a'
                        && $messages[0][1] == Logger::LEVEL_PROFILE_BEGIN
                        && $messages[1][0] === 'token.a'
                        && $messages[1][1] == Logger::LEVEL_PROFILE_END;
                }),
                false
            ]
        );

        $logger = new Logger([
            'profilingAware' => true,
            'dispatcher' => $dispatcher,
            'flushInterval' => 2,
        ]);

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('info', Logger::LEVEL_INFO, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testFlushingWithProfilingEnabledAndOverflow()
    {
        $dispatcher = $this->getMockBuilder('yii\log\Dispatcher')
            ->setMethods(['dispatch'])
            ->getMock();
        $dispatcher->expects($this->exactly(3))->method('dispatch')->withConsecutive(
            [
                $this->callback(function ($messages) {
                    return count($messages) === 2
                        && $messages[0][0] === 'token.a'
                        && $messages[0][1] == Logger::LEVEL_PROFILE_BEGIN
                        && $messages[1][0] === 'token.b'
                        && $messages[1][1] == Logger::LEVEL_PROFILE_BEGIN;
                }),
                false
            ],
            [
                $this->callback(function ($messages) {
                    return count($messages) === 1
                        && $messages[0][0] === 'Number of dangling profiling block messages reached flushInterval value and therefore these were flushed. Please consider setting higher flushInterval value or making profiling blocks shorter.';
                }),
                false
            ],
            [
                $this->callback(function ($messages) {
                    return count($messages) === 2
                        && $messages[0][0] === 'token.b'
                        && $messages[0][1] == Logger::LEVEL_PROFILE_END
                        && $messages[1][0] === 'token.a'
                        && $messages[1][1] == Logger::LEVEL_PROFILE_END;
                }),
                false
            ]
        );

        $logger = new Logger([
            'profilingAware' => true,
            'dispatcher' => $dispatcher,
            'flushInterval' => 2,
        ]);

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('token.b', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('token.b', Logger::LEVEL_PROFILE_END, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testWildcardsInMaskVars()
    {
        $keys = [
            'PASSWORD',
            'password',
            'password_repeat',
            'repeat_password',
            'repeat_password_again',
            '1password',
            'password1',
        ];

        $password = '!P@$$w0rd#';

        $items = array_fill_keys($keys, $password);

        $GLOBALS['_TEST'] = array_merge(
            $items,
            ['a' => $items],
            ['b' => ['c' => $items]],
            ['d' => ['e' => ['f' => $items]]],
        );

        $target = new TestTarget([
            'logVars' => ['_SERVER', '_TEST'],
            'maskVars' => [
                // option 1: exact value(s)
                '_SERVER.DOCUMENT_ROOT',
                // option 2: pattern(s)
                '_TEST.*password*',
            ]
        ]);

        $message = $target->getContextMessage();

        $this->assertStringContainsString("'DOCUMENT_ROOT' => '***'", $message);
        $this->assertStringNotContainsString($password, $message);
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

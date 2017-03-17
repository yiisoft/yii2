<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\log;

use yii\log\Logger;
use yiiunit\TestCase;

/**
 * @group log
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    protected function setUp()
    {
        $this->logger = new Logger();
        $this->dispatcher = $this->getMock('yii\\log\\Dispatcher', ['dispatch']);
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLog()
    {
        $memory = memory_get_usage();
        $this->logger->log('test1', Logger::LEVEL_INFO);
        $this->assertCount(1, $this->logger->messages);
        $this->assertEquals('test1', $this->logger->messages[0][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]);
        $this->assertEquals([], $this->logger->messages[0][4]);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[0][5]);

        $this->logger->log('test2', Logger::LEVEL_ERROR, 'category');
        $this->assertCount(2, $this->logger->messages);
        $this->assertEquals('test2', $this->logger->messages[1][0]);
        $this->assertEquals(Logger::LEVEL_ERROR, $this->logger->messages[1][1]);
        $this->assertEquals('category', $this->logger->messages[1][2]);
        $this->assertEquals([], $this->logger->messages[1][4]);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[1][5]);
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLogWithTraceLevel()
    {
        $memory = memory_get_usage();
        $this->logger->traceLevel = 3;
        $this->logger->log('test3', Logger::LEVEL_INFO);
        $this->assertCount(1, $this->logger->messages);
        $this->assertEquals('test3', $this->logger->messages[0][0]);
        $this->assertEquals(Logger::LEVEL_INFO, $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]);
        $this->assertEquals([
            'file' => __FILE__,
            'line' => 62,
            'function' => 'log',
            'class' => get_class($this->logger),
            'type' => '->'
        ], $this->logger->messages[0][4][0]);
        $this->assertCount(3, $this->logger->messages[0][4]);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[0][5]);
    }

    /**
     * @covers yii\log\Logger::Log()
     */
    public function testLogWithFlush()
    {
        $logger = $this->getMock('yii\\log\\Logger', ['flush']);
        $logger->flushInterval = 1;
        $logger->expects($this->exactly(1))->method('flush');
        $logger->log('test1', Logger::LEVEL_INFO);
    }

    /**
     * @covers yii\log\Logger::Flush()
     */
    public function testFlushWithoutDispatcher()
    {
        $dispatcher = $this->getMock('\stdClass');
        $dispatcher->expects($this->never())->method($this->anything());

        $this->logger->messages = ['anything'];
        $this->logger->dispatcher = $dispatcher;
        $this->logger->flush();
        $this->assertEmpty($this->logger->messages);
    }

    /**
     * @covers yii\log\Logger::Flush()
     */
    public function testFlushWithDispatcherAndDefaultParam()
    {
        $message = ['anything'];
        $this->dispatcher->expects($this->once())
            ->method('dispatch')->with($this->equalTo($message), $this->equalTo(false));

        $this->logger->messages = $message;
        $this->logger->dispatcher = $this->dispatcher;
        $this->logger->flush();
        $this->assertEmpty($this->logger->messages);
    }

    /**
     * @covers yii\log\Logger::Flush()
     */
    public function testFlushWithDispatcherAndDefinedParam()
    {
        $message = ['anything'];
        $this->dispatcher->expects($this->once())
            ->method('dispatch')->with($this->equalTo($message), $this->equalTo(true));

        $this->logger->messages = $message;
        $this->logger->dispatcher = $this->dispatcher;
        $this->logger->flush(true);
        $this->assertEmpty($this->logger->messages);
    }

    /**
     * @covers yii\log\Logger::getDbProfiling()
     */
    public function testGetDbProfiling()
    {
        $timings = [
            ['duration' => 5],
            ['duration' => 15],
            ['duration' => 30],
        ];

        $logger = $this->getMock('yii\\log\\Logger', ['getProfiling']);
        $logger->method('getProfiling')->willReturn($timings);
        $logger->expects($this->once())
            ->method('getProfiling')
            ->with($this->equalTo(['yii\db\Command::query', 'yii\db\Command::execute']));
        $this->assertEquals([3, 50], $logger->getDbProfiling());
    }

    /**
     * @covers yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithEmptyMessages()
    {
        $this->assertEmpty($this->logger->calculateTimings([]));
    }

    /**
     * @covers yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileNotBeginOrEnd()
    {
        $messages = [
            ['message0', Logger::LEVEL_ERROR, 'category', 'time', 'trace', 1048576],
            ['message1', Logger::LEVEL_INFO, 'category', 'time', 'trace', 1048576],
            ['message2', Logger::LEVEL_PROFILE, 'category', 'time', 'trace', 1048576],
            ['message3', Logger::LEVEL_TRACE, 'category', 'time', 'trace', 1048576],
            ['message4', Logger::LEVEL_WARNING, 'category', 'time', 'trace', 1048576],
        ];
        $this->assertEmpty($this->logger->calculateTimings($messages));
    }

    /**
     * @covers yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileBeginEnd()
    {
        $messages = [
            'anyKey' => ['token', Logger::LEVEL_PROFILE_BEGIN, 'category', 10, 'trace', 1048576],
            'anyKey2' => ['token', Logger::LEVEL_PROFILE_END, 'category', 15, 'trace', 2097152],
        ];
        $this->assertEquals([
            [
                'info' => 'token',
                'category' => 'category',
                'timestamp' => 10,
                'trace' => 'trace',
                'level' => 0,
                'duration' => 5,
                'memory' => 2097152,
                'memoryDiff' => 1048576
            ]
        ],
            $this->logger->calculateTimings($messages)
        );
    }

    /**
     * @covers yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileBeginEndAndNestedLevels()
    {
        $messages = [
            ['firstLevel', Logger::LEVEL_PROFILE_BEGIN, 'firstLevelCategory', 10, 'firstTrace', 1048576],
            ['secondLevel', Logger::LEVEL_PROFILE_BEGIN, 'secondLevelCategory', 15, 'secondTrace', 2097152],
            ['secondLevel', Logger::LEVEL_PROFILE_END, 'secondLevelCategory', 55, 'secondTrace', 3145728],
            ['firstLevel', Logger::LEVEL_PROFILE_END, 'firstLevelCategory', 80, 'firstTrace', 4194304],
        ];
        $this->assertEquals([
            [
                'info' => 'firstLevel',
                'category' => 'firstLevelCategory',
                'timestamp' => 10,
                'trace' => 'firstTrace',
                'level' => 0,
                'duration' => 70,
                'memory' => 4194304,
                'memoryDiff' => 3145728
            ],
            [
                'info' => 'secondLevel',
                'category' => 'secondLevelCategory',
                'timestamp' => 15,
                'trace' => 'secondTrace',
                'level' => 1,
                'duration' => 40,
                'memory' => 3145728,
                'memoryDiff' => 1048576
            ]
        ],
            $this->logger->calculateTimings($messages)
        );
    }

    /**
     * @covers yii\log\Logger::getElapsedTime()
     */
    public function testGetElapsedTime()
    {
        $timeBefore = \microtime(true) - YII_BEGIN_TIME;
        usleep(1);
        $actual = $this->logger->getElapsedTime();
        usleep(1);
        $timeAfter = \microtime(true) - YII_BEGIN_TIME;

        $this->assertGreaterThan($timeBefore, $actual);
        $this->assertLessThan($timeAfter, $actual);
    }

    /**
     * @covers yii\log\Logger::getLevelName()
     */
    public function testGetLevelName()
    {
        $this->assertEquals('info', Logger::getLevelName(Logger::LEVEL_INFO));
        $this->assertEquals('error', Logger::getLevelName(Logger::LEVEL_ERROR));
        $this->assertEquals('warning', Logger::getLevelName(Logger::LEVEL_WARNING));
        $this->assertEquals('trace', Logger::getLevelName(Logger::LEVEL_TRACE));
        $this->assertEquals('profile', Logger::getLevelName(Logger::LEVEL_PROFILE));
        $this->assertEquals('profile begin', Logger::getLevelName(Logger::LEVEL_PROFILE_BEGIN));
        $this->assertEquals('profile end', Logger::getLevelName(Logger::LEVEL_PROFILE_END));
        $this->assertEquals('unknown', Logger::getLevelName(0));
    }

    /**
     * @covers yii\log\Logger::getProfiling()
     */
    public function testGetProfilingWithEmptyCategoriesAndExcludeCategories()
    {
        $messages = ['anyData'];
        $returnValue = 'return value';
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals($returnValue, $logger->getProfiling());
    }

    /**
     * @covers yii\log\Logger::getProfiling()
     */
    public function testGetProfilingWithNotEmptyCategoriesAndNotMatched()
    {
        $messages = ['anyData'];
        $returnValue = [
            [
                'info' => 'token',
                'category' => 'category',
                'timestamp' => 10,
                'trace' => 'trace',
                'level' => 0,
                'duration' => 5,
            ]
        ];
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([], $logger->getProfiling(['not-matched-category']));
    }

    /**
     * @covers yii\log\Logger::getProfiling()
     */
    public function testGetProfilingWithNotEmptyCategoriesAndMatched()
    {
        $messages = ['anyData'];
        $matchedByCategoryName = [
            'info' => 'token',
            'category' => 'category',
            'timestamp' => 10,
            'trace' => 'trace',
            'level' => 0,
            'duration' => 5,
        ];
        $secondCategory = [
            'info' => 'secondToken',
            'category' => 'category2',
            'timestamp' => 10,
            'trace' => 'trace',
            'level' => 0,
            'duration' => 5,
        ];
        $returnValue = [
            'anyKey' => $matchedByCategoryName,
            $secondCategory
        ];
        /**
         * Matched by category name
         */
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$matchedByCategoryName], $logger->getProfiling(['category']));

        /**
         * Matched by prefix
         */
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$matchedByCategoryName, $secondCategory], $logger->getProfiling(['category*']));
    }

    /**
     * @covers yii\log\Logger::getProfiling()
     */
    public function testGetProfilingWithNotEmptyCategoriesMatchedAndExcludeCategories()
    {
        $messages = ['anyData'];
        $fistCategory = [
            'info' => 'fistToken',
            'category' => 'cat',
            'timestamp' => 10,
            'trace' => 'trace',
            'level' => 0,
            'duration' => 5,
        ];
        $secondCategory = [
            'info' => 'secondToken',
            'category' => 'category2',
            'timestamp' => 10,
            'trace' => 'trace',
            'level' => 0,
            'duration' => 5,
        ];
        $returnValue = [
            $fistCategory,
            $secondCategory,
            [
                'info' => 'anotherToken',
                'category' => 'category3',
                'timestamp' => 10,
                'trace' => 'trace',
                'level' => 0,
                'duration' => 5,
            ]
        ];

        /**
         * Exclude by category name
         */
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$fistCategory, $secondCategory], $logger->getProfiling(['cat*'], ['category3']));

        /**
         * Exclude by category prefix
         */
        $logger = $this->getMock('yii\\log\\Logger', ['calculateTimings']);

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$fistCategory], $logger->getProfiling(['cat*'], ['category*']));
    }
}

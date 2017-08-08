<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\LogLevel;
use yii\log\Logger;
use yii\profile\Profiler;
use yiiunit\TestCase;

/**
 * @group log
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['dispatch'])
            ->getMock();
    }

    /**
     * @covers \yii\log\Logger::Log()
     */
    public function testLog()
    {
        $memory = memory_get_usage();
        $this->logger->log(LogLevel::INFO, 'test1');
        $this->assertCount(1, $this->logger->messages);
        $this->assertEquals(LogLevel::INFO, $this->logger->messages[0][0]);
        $this->assertEquals('test1', $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]['category']);
        $this->assertEquals([], $this->logger->messages[0][2]['trace']);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[0][2]['memory']);

        $this->logger->log(LogLevel::ERROR, 'test2', ['category' => 'category']);
        $this->assertCount(2, $this->logger->messages);
        $this->assertEquals(LogLevel::ERROR, $this->logger->messages[1][0]);
        $this->assertEquals('test2', $this->logger->messages[1][1]);
        $this->assertEquals('category', $this->logger->messages[1][2]['category']);
        $this->assertEquals([], $this->logger->messages[1][2]['trace']);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[1][2]['memory']);
    }

    /**
     * @covers \yii\log\Logger::Log()
     */
    public function testLogWithTraceLevel()
    {
        $memory = memory_get_usage();
        $this->logger->traceLevel = 3;
        $this->logger->log(LogLevel::INFO, 'test3');
        $this->assertCount(1, $this->logger->messages);
        $this->assertEquals(LogLevel::INFO, $this->logger->messages[0][0]);
        $this->assertEquals('test3', $this->logger->messages[0][1]);
        $this->assertEquals('application', $this->logger->messages[0][2]['category']);
        $this->assertEquals([
            'file' => __FILE__,
            'line' => 63,
            'function' => 'log',
            'class' => Logger::class,
            'type' => '->',
        ], $this->logger->messages[0][2]['trace'][0]);
        $this->assertCount(3, $this->logger->messages[0][2]['trace']);
        $this->assertGreaterThanOrEqual($memory, $this->logger->messages[0][2]['memory']);
    }

    /**
     * @covers \yii\log\Logger::Log()
     */
    public function testLogWithFlush()
    {
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder(Logger::class)
            ->setMethods(['flush'])
            ->getMock();
        $logger->flushInterval = 1;
        $logger->expects($this->exactly(1))->method('flush');
        $logger->log(LogLevel::INFO, 'test1');
    }

    /**
     * @covers \yii\log\Logger::Flush()
     */
    public function testFlushWithDispatch()
    {
        $message = ['anything'];
        $this->logger->expects($this->once())
            ->method('dispatch')->with($this->equalTo($message), $this->equalTo(false));

        $this->logger->messages = $message;
        $this->logger->flush();
        $this->assertEmpty($this->logger->messages);
    }

    /**
     * @covers \yii\log\Logger::Flush()
     */
    public function testFlushWithDispatchAndDefinedParam()
    {
        $message = ['anything'];
        $this->logger->expects($this->once())
            ->method('dispatch')->with($this->equalTo($message), $this->equalTo(true));

        $this->logger->messages = $message;
        $this->logger->flush(true);
        $this->assertEmpty($this->logger->messages);
    }

    /**
     * @covers \yii\log\Logger::getDbProfiling()
     */
    public function testGetDbProfiling()
    {
        $timings = [
            ['duration' => 5],
            ['duration' => 15],
            ['duration' => 30],
        ];

        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder(Logger::class)
            ->setMethods(['getProfiling'])
            ->getMock();
        $logger->method('getProfiling')->willReturn($timings);
        $logger->expects($this->once())
            ->method('getProfiling')
            ->with($this->equalTo(['yii\db\Command::query', 'yii\db\Command::execute']));
        $this->assertEquals([3, 50], $logger->getDbProfiling());
    }

    /**
     * @covers \yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithEmptyMessages()
    {
        $this->assertEmpty($this->logger->calculateTimings([]));
    }

    /**
     * @covers \yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileNotBeginOrEnd()
    {
        $messages = [
            [LogLevel::ERROR, 'message0', ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
            [LogLevel::INFO, 'message1', ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
            //[Profiler::LEVEL_PROFILE, 'message2', ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
            [LogLevel::DEBUG, 'message3', ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
            [LogLevel::WARNING, 'message4', ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
            [LogLevel::ERROR, ['message5', 'message6'], ['category' => 'category', 'time' => 'time', 'trace' => 'trace', 'memory' => 1048576]],
        ];
        $this->assertEmpty($this->logger->calculateTimings($messages));
    }

    /**
     * @covers \yii\log\Logger::calculateTimings()
     *
     * See https://github.com/yiisoft/yii2/issues/14264
     */
    public function testCalculateTimingsWithProfileBeginEnd()
    {
        $messages = [
            'anyKey' => [Profiler::LEVEL_PROFILE_BEGIN, 'token', ['category' => 'category', 'time' => 10, 'trace' => 'trace', 'memory' => 1048576]],
            'anyKey2' => [Profiler::LEVEL_PROFILE_END, 'token', ['category' => 'category', 'time' => 15, 'trace' => 'trace', 'memory' => 2097152]],
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
                'memoryDiff' => 1048576,
            ],
        ],
            $this->logger->calculateTimings($messages)
        );

        $messages = [
            'anyKey' => [Profiler::LEVEL_PROFILE_BEGIN, ['a', 'b'], ['category' => 'category', 'time' => 10, 'trace' => 'trace', 'memory' => 1048576]],
            'anyKey2' => [Profiler::LEVEL_PROFILE_END, ['a', 'b'], ['category' => 'category', 'time' => 15, 'trace' => 'trace', 'memory' => 2097152]],
        ];
        $this->assertEquals([
            [
                'info' => ['a', 'b'],
                'category' => 'category',
                'timestamp' => 10,
                'trace' => 'trace',
                'level' => 0,
                'duration' => 5,
                'memory' => 2097152,
                'memoryDiff' => 1048576,
            ],
        ],
            $this->logger->calculateTimings($messages)
        );
    }

    /**
     * @covers \yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileBeginEndAndNestedLevels()
    {
        $messages = [
            [Profiler::LEVEL_PROFILE_BEGIN, 'firstLevel', ['category' => 'firstLevelCategory', 'time' => 10, 'trace' => 'firstTrace', 'memory' => 1048576]],
            [Profiler::LEVEL_PROFILE_BEGIN, 'secondLevel', ['category' => 'secondLevelCategory', 'time' => 15, 'trace' => 'secondTrace', 'memory' => 2097152]],
            [Profiler::LEVEL_PROFILE_END, 'secondLevel', ['category' => 'secondLevelCategory', 'time' => 55, 'trace' => 'secondTrace', 'memory' => 3145728]],
            [Profiler::LEVEL_PROFILE_END, 'firstLevel', ['category' => 'firstLevelCategory', 'time' => 80, 'trace' => 'firstTrace', 'memory' => 4194304]],
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
                'memoryDiff' => 3145728,
            ],
            [
                'info' => 'secondLevel',
                'category' => 'secondLevelCategory',
                'timestamp' => 15,
                'trace' => 'secondTrace',
                'level' => 1,
                'duration' => 40,
                'memory' => 3145728,
                'memoryDiff' => 1048576,
            ],
        ],
            $this->logger->calculateTimings($messages)
        );
    }

    /**
     * See https://github.com/yiisoft/yii2/issues/14133
     *
     * @covers \yii\log\Logger::calculateTimings()
     */
    public function testCalculateTimingsWithProfileBeginEndAndNestedMixedLevels()
    {
        $messages = [
            [Profiler::LEVEL_PROFILE_BEGIN, 'firstLevel', ['category' => 'firstLevelCategory', 'time' => 10, 'trace' => 'firstTrace', 'memory' => 1048576]],
            [Profiler::LEVEL_PROFILE_BEGIN, 'secondLevel', ['category' => 'secondLevelCategory', 'time' => 15, 'trace' => 'secondTrace', 'memory' => 2097152]],
            [Profiler::LEVEL_PROFILE_END, 'firstLevel', ['category' => 'firstLevelCategory', 'time' => 80, 'trace' => 'firstTrace', 'memory' => 4194304]],
            [Profiler::LEVEL_PROFILE_END, 'secondLevel', ['category' => 'secondLevelCategory', 'time' => 55, 'trace' => 'secondTrace', 'memory' => 3145728]],
        ];
        $this->assertEquals([
            [
                'info' => 'firstLevel',
                'category' => 'firstLevelCategory',
                'timestamp' => 10,
                'trace' => 'firstTrace',
                'level' => 1,
                'duration' => 70,
                'memory' => 4194304,
                'memoryDiff' => 3145728,
            ],
            [
                'info' => 'secondLevel',
                'category' => 'secondLevelCategory',
                'timestamp' => 15,
                'trace' => 'secondTrace',
                'level' => 0,
                'duration' => 40,
                'memory' => 3145728,
                'memoryDiff' => 1048576,
            ],
        ],
            $this->logger->calculateTimings($messages)
        );
    }

    /**
     * @covers \yii\log\Logger::getElapsedTime()
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
     * @covers \yii\log\Logger::getLevelName()
     */
    public function testGetLevelName()
    {
        $this->assertEquals('info', Logger::getLevelName(LogLevel::INFO));
        $this->assertEquals('error', Logger::getLevelName(LogLevel::ERROR));
        $this->assertEquals('warning', Logger::getLevelName(LogLevel::WARNING));
        $this->assertEquals('debug', Logger::getLevelName(LogLevel::DEBUG));
        $this->assertEquals('emergency', Logger::getLevelName(LogLevel::EMERGENCY));
        $this->assertEquals('alert', Logger::getLevelName(LogLevel::ALERT));
        $this->assertEquals('critical', Logger::getLevelName(LogLevel::CRITICAL));
        $this->assertEquals('unknown', Logger::getLevelName(0));
    }

    /**
     * @covers \yii\log\Logger::getProfiling()
     */
    public function testGetProfilingWithEmptyCategoriesAndExcludeCategories()
    {
        $messages = ['anyData'];
        $returnValue = 'return value';
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals($returnValue, $logger->getProfiling());
    }

    /**
     * @covers \yii\log\Logger::getProfiling()
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
            ],
        ];
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([], $logger->getProfiling(['not-matched-category']));
    }

    /**
     * @covers \yii\log\Logger::getProfiling()
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
            $secondCategory,
        ];
        /*
         * Matched by category name
         */
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$matchedByCategoryName], $logger->getProfiling(['category']));

        /*
         * Matched by prefix
         */
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$matchedByCategoryName, $secondCategory], $logger->getProfiling(['category*']));
    }

    /**
     * @covers \yii\log\Logger::getProfiling()
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
            ],
        ];

        /*
         * Exclude by category name
         */
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$fistCategory, $secondCategory], $logger->getProfiling(['cat*'], ['category3']));

        /*
         * Exclude by category prefix
         */
        /* @var $logger Logger|\PHPUnit_Framework_MockObject_MockObject */
        $logger = $this->getMockBuilder('yii\log\Logger')
            ->setMethods(['calculateTimings'])
            ->getMock();

        $logger->messages = $messages;
        $logger->method('calculateTimings')->willReturn($returnValue);
        $logger->expects($this->once())->method('calculateTimings')->with($this->equalTo($messages));
        $this->assertEquals([$fistCategory], $logger->getProfiling(['cat*'], ['category*']));
    }
}

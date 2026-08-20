<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Xepozz\InternalMocker\MockerState;
use yii\log\Dispatcher;
use yii\log\Logger;
use yiiunit\framework\log\providers\LoggerProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see Logger}.
 */
#[Group('log')]
final class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Dispatcher&MockObject
     */
    protected $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Logger();

        $this->dispatcher = $this->createPartialMock(Dispatcher::class, ['dispatch']);
    }

    public function testRegisterShutdownFlushesMessagesAndSchedulesFinalFlush(): void
    {
        MockerState::resetState();

        $logger = new Logger();

        $logger->messages = [
            ['message', Logger::LEVEL_INFO, 'application', 10.25, []],
        ];

        $traces = MockerState::getTraces(
            'yii\log',
            'register_shutdown_function',
        );

        self::assertCount(
            1,
            $traces,
            'Logger initialization must register the first shutdown callback.',
        );

        $traces[0]['arguments'][0]();

        $traces = MockerState::getTraces(
            'yii\log',
            'register_shutdown_function',
        );

        self::assertSame(
            [],
            $logger->messages,
            'The first shutdown callback must flush queued messages.',
        );
        self::assertCount(
            2,
            $traces,
            'The first shutdown callback must schedule the final flush.',
        );
        self::assertSame(
            [$logger, 'flush'],
            $traces[1]['arguments'][0],
            'The final shutdown callback must invoke Logger::flush().',
        );
        self::assertSame(
            [true],
            $traces[1]['arguments'][1],
            'The final shutdown callback must flush with the final flag.',
        );
    }

    public function testLog(): void
    {
        $memory = memory_get_usage();

        $this->logger->log('test1', Logger::LEVEL_INFO);

        self::assertCount(
            1,
            $this->logger->messages,
            'Logger must collect the first message.',
        );
        self::assertSame(
            'test1',
            $this->logger->messages[0][0],
            'Logger must preserve the message text.',
        );
        self::assertSame(
            Logger::LEVEL_INFO,
            $this->logger->messages[0][1],
            'Logger must preserve the info level.',
        );
        self::assertSame(
            'application',
            $this->logger->messages[0][2],
            'Logger must use the default category.',
        );
        self::assertSame(
            [],
            $this->logger->messages[0][4],
            'Logger must omit traces when traceLevel is zero.',
        );
        self::assertGreaterThanOrEqual(
            $memory,
            $this->logger->messages[0][5],
            'Logger must capture current memory usage.',
        );

        $this->logger->log('test2', Logger::LEVEL_ERROR, 'category');

        self::assertCount(
            2,
            $this->logger->messages,
            'Logger must append the second message.',
        );
        self::assertSame(
            'test2',
            $this->logger->messages[1][0],
            'Logger must preserve the second message text.',
        );
        self::assertSame(
            Logger::LEVEL_ERROR,
            $this->logger->messages[1][1],
            'Logger must preserve the error level.',
        );
        self::assertSame(
            'category',
            $this->logger->messages[1][2],
            'Logger must preserve the explicit category.',
        );
        self::assertSame(
            [],
            $this->logger->messages[1][4],
            'Logger must omit traces when traceLevel is zero.',
        );
        self::assertGreaterThanOrEqual(
            $memory,
            $this->logger->messages[1][5],
            'Logger must capture current memory usage for the second message.',
        );
    }

    public function testLogWithTraceLevel(): void
    {
        $memory = memory_get_usage();

        $this->logger->traceLevel = 3;
        $expectedLine = __LINE__ + 2;

        $this->logger->log('test3', Logger::LEVEL_INFO);

        self::assertCount(
            1,
            $this->logger->messages,
            'Logger must collect the traced message.',
        );
        self::assertSame(
            'test3',
            $this->logger->messages[0][0],
            'Logger must preserve the traced message text.',
        );
        self::assertSame(
            Logger::LEVEL_INFO,
            $this->logger->messages[0][1],
            'Logger must preserve the traced level.',
        );
        self::assertSame(
            'application',
            $this->logger->messages[0][2],
            'Logger must preserve the traced category.',
        );
        self::assertSame(
            [
                'file' => __FILE__,
                'line' => $expectedLine,
                'function' => 'log',
                'class' => $this->logger::class,
                'type' => '->',
            ],
            $this->logger->messages[0][4][0],
            'The first trace frame must identify the log call.',
        );
        self::assertCount(
            3,
            $this->logger->messages[0][4],
            'Logger must honor the configured trace level.',
        );
        self::assertGreaterThanOrEqual(
            $memory,
            $this->logger->messages[0][5],
            'Logger must capture current memory usage for the traced message.',
        );
    }

    public function testLogWithFlush(): void
    {
        $logger = $this->createPartialMock(Logger::class, ['flush']);

        $logger->flushInterval = 1;

        $logger->expects($this->exactly(1))->method('flush');
        $logger->log('test1', Logger::LEVEL_INFO);
    }

    public function testFlushWithoutDispatcher(): void
    {
        $dispatcher = $this->createMock(stdClass::class);

        $dispatcher
            ->expects($this->never())
            ->method($this->anything());

        $this->logger->messages = [
            ['anything', Logger::LEVEL_INFO, 'application', 0.0, []],
        ];

        // @phpstan-ignore assign.propertyType (We intentionally use an invalid value here to test its processing)
        $this->logger->dispatcher = $dispatcher;

        $this->logger->flush();

        self::assertSame(
            [],
            $this->logger->messages,
            'Flush must clear messages without a valid dispatcher.',
        );
    }

    public function testFlushWithDispatcherAndDefaultParam(): void
    {
        $message = [
            ['anything', Logger::LEVEL_INFO, 'application', 0.0, []],
        ];

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($message), $this->equalTo(false));

        $this->logger->messages = $message;
        $this->logger->dispatcher = $this->dispatcher;

        $this->logger->flush();

        self::assertSame(
            [],
            $this->logger->messages,
            'Flush must clear dispatched messages.',
        );
    }

    public function testFlushWithDispatcherAndDefinedParam(): void
    {
        $message = [
            ['anything', Logger::LEVEL_INFO, 'application', 0.0, []],
        ];

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo($message), $this->equalTo(true));

        $this->logger->messages = $message;
        $this->logger->dispatcher = $this->dispatcher;

        $this->logger->flush(true);

        self::assertSame(
            [],
            $this->logger->messages,
            'Final flush must clear dispatched messages.',
        );
    }

    public function testGetDbProfiling(): void
    {
        $timings = [
            ['duration' => 5],
            ['duration' => 15],
            ['duration' => 30],
        ];

        $logger = $this->createPartialMock(Logger::class, ['getProfiling']);

        $logger
            ->method('getProfiling')
            ->willReturn($timings);
        $logger->expects($this->once())
            ->method('getProfiling')
            ->with($this->equalTo(['yii\db\Command::query', 'yii\db\Command::execute']));

        self::assertSame(
            [3, 50],
            $logger->getDbProfiling(),
            'Database profiling must return query count and total time.',
        );
    }

    public function testCalculateTimingsWithEmptyMessages(): void
    {
        self::assertSame(
            [],
            $this->logger->calculateTimings([]),
            'No messages must produce no profiling timings.',
        );
    }

    public function testCalculateTimingsWithProfileNotBeginOrEnd(): void
    {
        $messages = [
            ['message0', Logger::LEVEL_ERROR, 'category', 'time', 'trace', 1_048_576],
            ['message1', Logger::LEVEL_INFO, 'category', 'time', 'trace', 1_048_576],
            ['message2', Logger::LEVEL_PROFILE, 'category', 'time', 'trace', 1_048_576],
            ['message3', Logger::LEVEL_TRACE, 'category', 'time', 'trace', 1_048_576],
            ['message4', Logger::LEVEL_WARNING, 'category', 'time', 'trace', 1_048_576],
            [['message5', 'message6'], Logger::LEVEL_ERROR, 'category', 'time', 'trace', 1_048_576],
        ];

        self::assertSame(
            [],
            $this->logger->calculateTimings($messages),
            'Non-paired profiling levels must produce no timings.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14264
     */
    public function testCalculateTimingsWithProfileBeginEnd(): void
    {
        $messages = [
            'anyKey' => ['token', Logger::LEVEL_PROFILE_BEGIN, 'category', 10, 'trace', 1_048_576],
            'anyKey2' => ['token', Logger::LEVEL_PROFILE_END, 'category', 15, 'trace', 2_097_152],
        ];

        self::assertSame(
            [
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
            $this->logger->calculateTimings($messages),
            'String profiling tokens must produce the expected timing.',
        );

        $messages = [
            'anyKey' => [['a', 'b'], Logger::LEVEL_PROFILE_BEGIN, 'category', 10, 'trace', 1_048_576],
            'anyKey2' => [['a', 'b'], Logger::LEVEL_PROFILE_END, 'category', 15, 'trace', 2_097_152],
        ];

        self::assertSame(
            [
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
            $this->logger->calculateTimings($messages),
            'Array profiling tokens must produce the expected timing.',
        );
    }

    public function testCalculateTimingsWithProfileBeginEndAndNestedLevels(): void
    {
        $messages = [
            ['firstLevel', Logger::LEVEL_PROFILE_BEGIN, 'firstLevelCategory', 10, 'firstTrace', 1_048_576],
            ['secondLevel', Logger::LEVEL_PROFILE_BEGIN, 'secondLevelCategory', 15, 'secondTrace', 2_097_152],
            ['secondLevel', Logger::LEVEL_PROFILE_END, 'secondLevelCategory', 55, 'secondTrace', 3_145_728],
            ['firstLevel', Logger::LEVEL_PROFILE_END, 'firstLevelCategory', 80, 'firstTrace', 4_194_304],
        ];

        self::assertSame(
            [
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
            $this->logger->calculateTimings($messages),
            'Nested profiling pairs must preserve their nesting levels.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14133
     */
    public function testCalculateTimingsWithProfileBeginEndAndNestedMixedLevels(): void
    {
        $messages = [
            ['firstLevel', Logger::LEVEL_PROFILE_BEGIN, 'firstLevelCategory', 10, 'firstTrace', 1_048_576],
            ['secondLevel', Logger::LEVEL_PROFILE_BEGIN, 'secondLevelCategory', 15, 'secondTrace', 2_097_152],
            ['firstLevel', Logger::LEVEL_PROFILE_END, 'firstLevelCategory', 80, 'firstTrace', 4_194_304],
            ['secondLevel', Logger::LEVEL_PROFILE_END, 'secondLevelCategory', 55, 'secondTrace', 3_145_728],
        ];

        self::assertSame(
            [
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
            $this->logger->calculateTimings($messages),
            'Mixed profiling pairs must preserve the calculated nesting levels.',
        );
    }

    public function testGetElapsedTime(): void
    {
        $timeBefore = \microtime(true) - YII_BEGIN_TIME;

        usleep(1);

        $actual = $this->logger->getElapsedTime();

        usleep(1);

        $timeAfter = \microtime(true) - YII_BEGIN_TIME;

        self::assertGreaterThan(
            $timeBefore,
            $actual,
            'Elapsed time must be later than the first measurement.',
        );
        self::assertLessThan(
            $timeAfter,
            $actual,
            'Elapsed time must be earlier than the final measurement.',
        );
    }

    #[DataProviderExternal(LoggerProvider::class, 'levelNames')]
    public function testGetLevelName(int $level, string $expected): void
    {
        self::assertSame(
            $expected,
            Logger::getLevelName($level),
            'Level name must match the configured level bitmap.',
        );
    }

    public function testGetProfilingWithEmptyCategoriesAndExcludeCategories(): void
    {
        $messages = [['anyData', Logger::LEVEL_INFO, 'application', 0.0, []]];

        $returnValue = 'return value';

        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            $returnValue,
            $logger->getProfiling(),
            'Unfiltered profiling must return all timings.',
        );
    }

    public function testGetProfilingWithNotEmptyCategoriesAndNotMatched(): void
    {
        $messages = [['anyData', Logger::LEVEL_INFO, 'application', 0.0, []]];

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

        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            [],
            $logger->getProfiling(['not-matched-category']),
            'An unmatched category must filter out every timing.',
        );
    }

    public function testGetProfilingWithNotEmptyCategoriesAndMatched(): void
    {
        $messages = [['anyData', Logger::LEVEL_INFO, 'application', 0.0, []]];

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
        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            [$matchedByCategoryName],
            $logger->getProfiling(['category']),
            'An exact category must return only the matching timing.',
        );

        /*
         * Matched by prefix
         */
        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            [$matchedByCategoryName, $secondCategory],
            $logger->getProfiling(['category*']),
            'A wildcard category must return every matching prefix.',
        );
    }

    public function testGetProfilingWithNotEmptyCategoriesMatchedAndExcludeCategories(): void
    {
        $messages = [['anyData', Logger::LEVEL_INFO, 'application', 0.0, []]];

        $firstCategory = [
            'info' => 'firstToken',
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
            $firstCategory,
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
        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            [$firstCategory, $secondCategory],
            $logger->getProfiling(['cat*'], ['category3']),
            'An exact exclusion must remove only the matching timing.',
        );

        /*
         * Exclude by category prefix
         */
        $logger = $this->createPartialMock(Logger::class, ['calculateTimings']);

        $logger->messages = $messages;

        $logger
            ->method('calculateTimings')
            ->willReturn($returnValue);
        $logger
            ->expects($this->once())
            ->method('calculateTimings')
            ->with($messages);

        self::assertSame(
            [$firstCategory],
            $logger->getProfiling(['cat*'], ['category*']),
            'A wildcard exclusion must remove every matching prefix.',
        );
    }

    #[DataProviderExternal(LoggerProvider::class, 'nonProfilingMessages')]
    public function testGatheringNonProfilingMessages(int $level): void
    {
        $logger = new Logger(['flushInterval' => 0]);

        $logger->log('aaa', $level);
        $logger->log('aaa', Logger::LEVEL_PROFILE_END);

        self::assertSame(
            [],
            $logger->getProfiling(),
            'Non-profiling levels must not produce profiling timings.',
        );
        self::assertCount(
            2,
            $logger->messages,
            'Logger must retain both non-profiling messages.',
        );
    }

    public function testGatheringProfilingMessages(): void
    {
        $logger = new Logger(['flushInterval' => 0]);

        $logger->log('aaa', Logger::LEVEL_PROFILE_BEGIN);
        $logger->log('aaa', Logger::LEVEL_PROFILE_END);

        self::assertCount(
            1,
            $logger->getProfiling(),
            'A profiling pair must produce one timing.',
        );

        $profiling = $logger->getProfiling()[0];

        self::assertSame(
            'aaa',
            $profiling['info'],
            'Profiling timing must preserve the token.',
        );
        self::assertSame(
            'application',
            $profiling['category'],
            'Profiling timing must preserve the category.',
        );
        self::assertSame(
            0,
            $profiling['level'],
            'Top-level profiling timing must have level zero.',
        );
        self::assertSame(
            [],
            $profiling['trace'],
            'Profiling timing must preserve the empty trace.',
        );
        self::assertArrayHasKey(
            'timestamp',
            $profiling,
            'Profiling timing must contain a timestamp.',
        );
        self::assertArrayHasKey(
            'duration',
            $profiling,
            'Profiling timing must contain a duration.',
        );
        self::assertArrayHasKey(
            'memory',
            $profiling,
            'Profiling timing must contain memory usage.',
        );
        self::assertArrayHasKey(
            'memoryDiff',
            $profiling,
            'Profiling timing must contain a memory difference.',
        );
        self::assertCount(
            2,
            $logger->messages,
            'Logger must retain both profiling messages.',
        );
    }
}

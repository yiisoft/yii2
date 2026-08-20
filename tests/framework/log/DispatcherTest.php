<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\base\UserException;
use yii\log\Dispatcher;
use yii\log\Logger;
use yii\log\SyslogTarget;
use yii\log\Target;
use yiiunit\framework\log\mocks\TargetMock;
use yiiunit\TestCase;

/**
 * Unit tests for {@see Dispatcher}.
 */
#[Group('log')]
final class DispatcherTest extends TestCase
{
    public static bool $microtimeIsMocked = false;

    private Dispatcher $dispatcher;
    private Logger $logger;
    private int $targetThrowFirstCount = 0;
    /**
     * @var list<array>
     */
    private array $targetThrowSecondOutputs = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::$microtimeIsMocked = false;
        $this->dispatcher = new Dispatcher();
        $this->logger = new Logger();
    }

    public function testConfigureLogger(): void
    {
        $dispatcher = new Dispatcher();

        self::assertSame(
            Yii::getLogger(),
            $dispatcher->getLogger(),
            'Dispatcher must use the global logger by default.',
        );

        $logger = new Logger();
        $dispatcher = new Dispatcher(
            [
                'logger' => $logger,
            ],
        );

        self::assertSame(
            $logger,
            $dispatcher->getLogger(),
            'Dispatcher must preserve a logger instance.',
        );

        $dispatcher = new Dispatcher(
            [
                'logger' => Logger::class,
            ],
        );

        self::assertInstanceOf(
            Logger::class,
            $dispatcher->getLogger(),
            'Dispatcher must create a logger from a class name.',
        );
        self::assertSame(
            0,
            $dispatcher->getLogger()->traceLevel,
            'Logger created from a class name must keep the default trace level.',
        );

        $dispatcher = new Dispatcher(
            [
                'logger' => [
                    'class' => Logger::class,
                    'traceLevel' => 42,
                ],
            ],
        );

        self::assertInstanceOf(
            Logger::class,
            $dispatcher->getLogger(),
            'Dispatcher must create a logger from configuration.',
        );
        self::assertSame(
            42,
            $dispatcher->getLogger()->traceLevel,
            'Logger configuration must set the trace level.',
        );
    }

    public function testSetLogger(): void
    {
        $this->dispatcher->setLogger($this->logger);

        self::assertSame(
            $this->logger,
            $this->dispatcher->getLogger(),
            'setLogger() must preserve a logger instance.',
        );

        $this->dispatcher->setLogger(Logger::class);

        self::assertInstanceOf(
            Logger::class,
            $this->dispatcher->getLogger(),
            'setLogger() must create a logger from a class name.',
        );
        self::assertSame(
            0,
            $this->dispatcher->getLogger()->traceLevel,
            'Logger created by setLogger() must keep the default trace level.',
        );

        $this->dispatcher->setLogger([
            'class' => Logger::class,
            'traceLevel' => 42,
        ]);

        self::assertInstanceOf(
            Logger::class,
            $this->dispatcher->getLogger(),
            'setLogger() must create a logger from configuration.',
        );
        self::assertSame(
            42,
            $this->dispatcher->getLogger()->traceLevel,
            'setLogger() configuration must set the trace level.',
        );
    }

    public function testGetTraceLevel(): void
    {
        $this->logger->traceLevel = 123;

        $this->dispatcher->setLogger($this->logger);

        self::assertSame(
            123,
            $this->dispatcher->getTraceLevel(),
            'Dispatcher must return the logger trace level.',
        );
    }

    public function testSetTraceLevel(): void
    {
        $this->dispatcher->setLogger($this->logger);
        $this->dispatcher->setTraceLevel(123);

        self::assertSame(
            123,
            $this->logger->traceLevel,
            'Dispatcher must update the logger trace level.',
        );
    }

    public function testGetFlushInterval(): void
    {
        $this->logger->flushInterval = 99;

        $this->dispatcher->setLogger($this->logger);

        self::assertSame(
            99,
            $this->dispatcher->getFlushInterval(),
            'Dispatcher must return the logger flush interval.',
        );
    }

    public function testSetFlushInterval(): void
    {
        $this->dispatcher->setLogger($this->logger);
        $this->dispatcher->setFlushInterval(99);

        self::assertSame(
            99,
            $this->logger->flushInterval,
            'Dispatcher must update the logger flush interval.',
        );
    }

    public function testDispatchWithDisabledTarget(): void
    {
        $target = $this->createPartialMock(Target::class, ['collect', 'export']);

        $target
            ->expects($this->never())
            ->method($this->anything());

        $target->enabled = false;

        $dispatcher = new Dispatcher(
            [
                'targets' => ['fakeTarget' => $target],
            ],
        );

        $dispatcher->dispatch(
            [['message', Logger::LEVEL_INFO, 'application', 10.25, []]],
            true,
        );
    }

    public function testDispatchWithSuccessTargetCollect(): void
    {
        $messages = [
            ['message', Logger::LEVEL_INFO, 'application', 10.25, []],
        ];

        $target = $this->createPartialMock(Target::class, ['collect', 'export']);

        $target->expects($this->once())
            ->method('collect')
            ->with(
                $this->equalTo($messages),
                $this->equalTo(true)
            );

        $dispatcher = new Dispatcher(
            [
                'targets' => ['fakeTarget' => $target],
            ],
        );

        $dispatcher->dispatch($messages, true);
    }

    public function testDispatchReportsTargetFailure(): void
    {
        require_once dirname(__DIR__, 2) . '/data/log/microtime.php';

        self::$microtimeIsMocked = true;

        $messages = [
            ['message', Logger::LEVEL_INFO, 'application', 10.0, []],
        ];

        $target1 = $this->createPartialMock(Target::class, ['collect', 'export']);
        $target2 = $this->createPartialMock(Target::class, ['collect', 'export']);
        $matcher = $this->exactly(2);

        $target1
            ->expects($matcher)
            ->method('collect')
            ->willReturnCallback(
                function (...$parameters) use ($matcher, $messages, $target1): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertSame(
                            $messages,
                            $parameters[0],
                            'The first dispatch must contain the original messages.',
                        );
                        self::assertTrue(
                            $parameters[1],
                            'The first dispatch must preserve the final flag.',
                        );

                        return;
                    }

                    self::assertCount(
                        1,
                        $parameters[0],
                        'The recursive dispatch must contain one target failure.',
                    );

                    $failure = $parameters[0][0];

                    self::assertStringStartsWith(
                        'Unable to send log via ' . $target1::class
                            . ': Exception (Exception) \'yii\base\UserException\' with message \'some error\'',
                        (string) $failure[0],
                        'Target failure text must identify the failed target and exception.',
                    );
                    self::assertSame(
                        Logger::LEVEL_WARNING,
                        $failure[1],
                        'Target failures must use the warning level.',
                    );
                    self::assertSame(
                        'yii\log\Dispatcher::dispatch',
                        $failure[2],
                        'Target failure category must identify Dispatcher::dispatch().',
                    );
                    self::assertSame(
                        10.25,
                        $failure[3],
                        'Target failure must preserve the mocked timestamp.',
                    );
                    self::assertSame(
                        [],
                        $failure[4],
                        'Target failure must have an empty trace.',
                    );
                    self::assertTrue(
                        $parameters[1],
                        'The recursive dispatch must always be final.',
                    );
                },
            );

        $target2
            ->expects($this->once())
            ->method('collect')
            ->with(
                $this->equalTo($messages),
                $this->equalTo(true)
            )->willThrowException(new UserException('some error'));

        $dispatcher = new Dispatcher(
            [
                'targets' => [
                    'fakeTarget1' => $target1,
                    'fakeTarget2' => $target2,
                ],
            ],
        );

        try {
            $dispatcher->dispatch($messages, true);
        } finally {
            self::$microtimeIsMocked = false;
        }
    }

    public static function microtime(bool $asFloat): float
    {
        self::assertTrue(
            $asFloat,
            "Dispatcher must request 'microtime' as a floating-point value.",
        );

        return 10.25;
    }

    public function testInitCreatesConfiguredTarget(): void
    {
        $dispatcher = new Dispatcher(
            [
                'targets' => [
                    'syslog' => ['class' => SyslogTarget::class],
                ],
            ],
        );

        self::assertInstanceOf(
            SyslogTarget::class,
            $dispatcher->targets['syslog'],
            'Dispatcher initialization must create configured targets.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/20874
     */
    public function testDispatchContinuesAfterThrowable(): void
    {
        $this->targetThrowFirstCount = 0;
        $this->targetThrowSecondOutputs = [];

        $targetFirst = new TargetMock(
            [
                'collectOverride' => function (): void {
                    $this->targetThrowFirstCount++;
                    require_once __DIR__ . '/mocks/typed_error.php';
                    typed_error_test_mock([]);
                },
            ],
        );
        $targetSecond = new TargetMock(
            [
                'collectOverride' => function (array $messages): void {
                    $this->targetThrowSecondOutputs[] = array_pop($messages);
                },
            ],
        );
        $dispatcher = new Dispatcher(
            [
                'logger' => new Logger(),
                'targets' => [$targetFirst, $targetSecond],
            ],
        );

        $message = ['test' . time(), Logger::LEVEL_INFO, 'application', 10.25, []];

        $dispatcher->dispatch([$message], false);

        self::assertSame(
            1,
            $this->targetThrowFirstCount,
            'The failing target must be called once.',
        );
        self::assertCount(
            2,
            $this->targetThrowSecondOutputs,
            'The next target must receive the original message and the generated failure.',
        );
        self::assertSame(
            $message,
            array_shift($this->targetThrowSecondOutputs),
            'The next target must receive the original message first.',
        );

        $failure = array_shift($this->targetThrowSecondOutputs);

        self::assertIsArray(
            $failure,
            'The generated target failure must be a log message array.',
        );
        self::assertStringStartsWith(
            'Unable to send log via',
            (string) $failure[0],
            'The generated target failure must describe the failed target.',
        );
    }
}

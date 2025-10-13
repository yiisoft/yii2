<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log {
    function microtime($get_as_float)
    {
        if (\yiiunit\framework\log\DispatcherTest::$microtimeIsMocked) {
            return \yiiunit\framework\log\DispatcherTest::microtime(func_get_args());
        }

        return \microtime($get_as_float);
    }
}

namespace yiiunit\framework\log {
    use yiiunit\framework\log\mocks\TargetMock;
    use Yii;
    use yii\base\UserException;
    use yii\log\Dispatcher;
    use yii\log\Logger;
    use yii\log\Target;
    use yiiunit\TestCase;

    /**
     * @group log
     */
    class DispatcherTest extends TestCase
    {
        /**
         * @var Logger
         */
        protected $logger;

        /**
         * @var Dispatcher
         */
        protected $dispatcher;

        /**
         * @var bool
         */
        public static $microtimeIsMocked = false;

        /**
         * Array of static functions.
         *
         * @var array
         */
        public static $functions = [];

        protected function setUp(): void
        {
            static::$microtimeIsMocked = false;
            $this->dispatcher = new Dispatcher();
            $this->logger = new Logger();
        }

        public function testConfigureLogger(): void
        {
            $dispatcher = new Dispatcher();
            $this->assertSame(Yii::getLogger(), $dispatcher->getLogger());


            $logger = new Logger();
            $dispatcher = new Dispatcher([
                'logger' => $logger,
            ]);
            $this->assertSame($logger, $dispatcher->getLogger());


            $dispatcher = new Dispatcher([
                'logger' => 'yii\log\Logger',
            ]);
            $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
            $this->assertEquals(0, $dispatcher->getLogger()->traceLevel);


            $dispatcher = new Dispatcher([
                'logger' => [
                    'class' => 'yii\log\Logger',
                    'traceLevel' => 42,
                ],
            ]);
            $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
            $this->assertEquals(42, $dispatcher->getLogger()->traceLevel);
        }

        /**
         * @covers \yii\log\Dispatcher::setLogger()
         */
        public function testSetLogger(): void
        {
            $this->dispatcher->setLogger($this->logger);
            $this->assertSame($this->logger, $this->dispatcher->getLogger());

            $this->dispatcher->setLogger('yii\log\Logger');
            $this->assertInstanceOf('yii\log\Logger', $this->dispatcher->getLogger());
            $this->assertEquals(0, $this->dispatcher->getLogger()->traceLevel);

            $this->dispatcher->setLogger([
                'class' => 'yii\log\Logger',
                'traceLevel' => 42,
            ]);
            $this->assertInstanceOf('yii\log\Logger', $this->dispatcher->getLogger());
            $this->assertEquals(42, $this->dispatcher->getLogger()->traceLevel);
        }

        /**
         * @covers \yii\log\Dispatcher::getTraceLevel()
         */
        public function testGetTraceLevel(): void
        {
            $this->logger->traceLevel = 123;
            $this->dispatcher->setLogger($this->logger);
            $this->assertEquals(123, $this->dispatcher->getTraceLevel());
        }

        /**
         * @covers \yii\log\Dispatcher::setTraceLevel()
         */
        public function testSetTraceLevel(): void
        {
            $this->dispatcher->setLogger($this->logger);
            $this->dispatcher->setTraceLevel(123);
            $this->assertEquals(123, $this->logger->traceLevel);
        }

        /**
         * @covers \yii\log\Dispatcher::getFlushInterval()
         */
        public function testGetFlushInterval(): void
        {
            $this->logger->flushInterval = 99;
            $this->dispatcher->setLogger($this->logger);
            $this->assertEquals(99, $this->dispatcher->getFlushInterval());
        }

        /**
         * @covers \yii\log\Dispatcher::setFlushInterval()
         */
        public function testSetFlushInterval(): void
        {
            $this->dispatcher->setLogger($this->logger);
            $this->dispatcher->setFlushInterval(99);
            $this->assertEquals(99, $this->logger->flushInterval);
        }

        /**
         * @covers \yii\log\Dispatcher::dispatch()
         */
        public function testDispatchWithDisabledTarget(): void
        {
            $target = $this->createPartialMock(Target::class, ['collect', 'export']);

            $target->expects($this->never())->method($this->anything());
            $target->enabled = false;

            $dispatcher = new Dispatcher(['targets' => ['fakeTarget' => $target]]);
            $dispatcher->dispatch('messages', true);
        }

        /**
         * @covers \yii\log\Dispatcher::dispatch()
         */
        public function testDispatchWithSuccessTargetCollect(): void
        {
            $target = $this->createPartialMock(Target::class, ['collect', 'export']);

            $target->expects($this->once())
                ->method('collect')
                ->with(
                    $this->equalTo('messages'),
                    $this->equalTo(true)
                );

            $dispatcher = new Dispatcher(['targets' => ['fakeTarget' => $target]]);
            $dispatcher->dispatch('messages', true);
        }

        /**
         * @covers \yii\log\Dispatcher::dispatch()
         */
        public function testDispatchWithFakeTarget2ThrowExceptionWhenCollect(): void
        {
            static::$microtimeIsMocked = true;
            $target1 = $this->createPartialMock(Target::class, ['collect', 'export']);
            $target2 = $this->createPartialMock(Target::class, ['collect', 'export']);

            $target1->expects($this->exactly(2))
                ->method('collect')
                ->withConsecutive(
                    [$this->equalTo('messages'), $this->equalTo(true)],
                    [
                        $this->callback(function ($arg) use ($target1) {
                            if (!isset($arg[0][0], $arg[0][1], $arg[0][2], $arg[0][3])) {
                                return false;
                            }

                            if (!str_starts_with((string) $arg[0][0], 'Unable to send log via ' . $target1::class . ': Exception (Exception) \'yii\base\UserException\' with message \'some error\'')) {
                                return false;
                            }

                            if ($arg[0][1] !== Logger::LEVEL_WARNING) {
                                return false;
                            }

                            if ($arg[0][2] !== 'yii\log\Dispatcher::dispatch') {
                                return false;
                            }

                            if ($arg[0][3] !== 'time data') {
                                return false;
                            }

                            if ($arg[0][4] !== []) {
                                return false;
                            }

                            return true;
                        }),
                        true,
                    ]
                );

            $target2->expects($this->once())
                ->method('collect')
                ->with(
                    $this->equalTo('messages'),
                    $this->equalTo(true)
                )->will($this->throwException(new UserException('some error')));

            $dispatcher = new Dispatcher(['targets' => ['fakeTarget1' => $target1, 'fakeTarget2' => $target2]]);

            static::$functions['microtime'] = function ($arguments) {
                $this->assertEquals([true], $arguments);
                return 'time data';
            };

            $dispatcher->dispatch('messages', true);
        }

        /**
         * @covers \yii\log\Dispatcher::init()
         */
        public function testInitWithCreateTargetObject(): void
        {
            $dispatcher = new Dispatcher(
                [
                    'targets' => [
                        'syslog' => [
                            'class' => 'yii\log\SyslogTarget',
                            ],
                    ],
                ]
            );

            $this->assertEquals($dispatcher->targets['syslog'], Yii::createObject('yii\log\SyslogTarget'));
        }

        /**
         * @param $name
         * @param $arguments
         * @return mixed
         */
        public static function __callStatic($name, $arguments)
        {
            if (isset(static::$functions[$name]) && is_callable(static::$functions[$name])) {
                $arguments = $arguments[0] ?? $arguments;
                return forward_static_call(static::$functions[$name], $arguments);
            }
            static::fail("Function '$name' has not implemented yet!");
        }

        private ?int $targetThrowFirstCount = null;
        private ?array $targetThrowSecondOutputs = null;

        public function testTargetThrow(): void
        {
            $this->targetThrowFirstCount = 0;
            $this->targetThrowSecondOutputs = [];
            $targetFirst = new TargetMock([
                'collectOverride' => function (): void {
                    $this->targetThrowFirstCount++;
                    require_once __DIR__ . DIRECTORY_SEPARATOR . 'mocks' . DIRECTORY_SEPARATOR . 'typed_error.php';
                    typed_error_test_mock([]);
                }
            ]);
            $targetSecond = new TargetMock([
                'collectOverride' => function ($message, $final): void {
                    $this->targetThrowSecondOutputs[] = array_pop($message);
                }
            ]);
            $dispatcher = new Dispatcher([
                'logger' => new Logger(),
                'targets' => [$targetFirst, $targetSecond],
            ]);
            $message = 'test' . time();
            $dispatcher->dispatch([$message], false);
            $this->assertSame(1, $this->targetThrowFirstCount);
            $this->assertSame(2, count($this->targetThrowSecondOutputs));
            $this->assertSame($message, array_shift($this->targetThrowSecondOutputs));
            $this->assertStringStartsWith('Unable to send log via', array_shift($this->targetThrowSecondOutputs)[0]);
        }
    }
}

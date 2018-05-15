<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\log {
    function microtime($get_as_float)
    {
        if (\yiiunit\framework\log\LoggerDispatchingTest::$microtimeIsMocked) {
            return \yiiunit\framework\log\LoggerDispatchingTest::microtime(func_get_args());
        }

        return \microtime($get_as_float);
    }
}

namespace yiiunit\framework\log {

    use Psr\Log\LogLevel;
    use Yii;
    use yii\base\UserException;
    use yii\log\Logger;
    use yiiunit\TestCase;

    /**
     * @group log
     */
    class LoggerDispatchingTest extends TestCase
    {
        /**
         * @var Logger
         */
        protected $logger;

        /**
         * @var bool
         */
        public static $microtimeIsMocked = false;

        /**
         * Array of static functions
         *
         * @var array
         */
        public static $functions = [];

        protected function setUp()
        {
            static::$microtimeIsMocked = false;
            $this->logger = new Logger();
        }

        /**
         * @covers \yii\log\Logger::dispatch()
         */
        public function testDispatchWithDisabledTarget()
        {
            $target = $this->getMockBuilder('yii\\log\\Target')
                ->setMethods(['collect'])
                ->getMockForAbstractClass();

            $target->expects($this->never())->method($this->anything());
            $target->enabled = false;

            $logger = new Logger(['targets' => ['fakeTarget' => $target]]);
            $logger->messages = 'messages';
            $logger->flush(true);
        }

        /**
         * @covers \yii\log\Logger::dispatch()
         */
        public function testDispatchWithSuccessTargetCollect()
        {
            $target = $this->getMockBuilder('yii\\log\\Target')
                ->setMethods(['collect'])
                ->getMockForAbstractClass();

            $target->expects($this->once())
                ->method('collect')
                ->with(
                    $this->equalTo('messages'),
                    $this->equalTo(true)
                );

            $logger = new Logger(['targets' => ['fakeTarget' => $target]]);

            $logger->messages = 'messages';
            $logger->flush(true);
        }

        /**
         * @covers \yii\log\Logger::dispatch()
         */
        public function testDispatchWithFakeTarget2ThrowExceptionWhenCollect()
        {
            static::$microtimeIsMocked = true;

            $target1 = $this->getMockBuilder('yii\\log\\Target')
                ->setMethods(['collect'])
                ->getMockForAbstractClass();

            $target2 = $this->getMockBuilder('yii\\log\\Target')
                ->setMethods(['collect'])
                ->getMockForAbstractClass();

            $target1->expects($this->exactly(2))
                ->method('collect')
                ->withConsecutive(
                    [$this->equalTo('messages'), $this->equalTo(true)],
                    [
                        [[
                            'Unable to send log via ' . get_class($target1) . ': Exception: some error',
                            LogLevel::WARNING,
                            'yii\log\Logger::dispatch',
                            'time data',
                            [],
                        ]],
                        true,
                    ]
                );

            $target2->expects($this->once())
                ->method('collect')
                ->with(
                    $this->equalTo('messages'),
                    $this->equalTo(true)
                )->will($this->throwException(new UserException('some error')));

            $logger = new Logger(['targets' => ['fakeTarget1' => $target1, 'fakeTarget2' => $target2]]);

            static::$functions['microtime'] = function ($arguments) {
                $this->assertEquals([true], $arguments);
                return 'time data';
            };

            $logger->messages = 'messages';
            $logger->flush(true);
        }

        /**
         * @covers \yii\log\Logger::init()
         */
        public function testInitWithCreateTargetObject()
        {
            $logger = new Logger(
                [
                    'targets' => [
                        'syslog' => [
                            '__class' => \yii\log\SyslogTarget::class,
                        ],
                    ],
                ]
            );

            $this->assertEquals($logger->targets['syslog'], Yii::createObject('yii\log\SyslogTarget'));
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
    }
}

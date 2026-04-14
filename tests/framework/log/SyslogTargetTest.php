<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log {
    function openlog()
    {
        return \yiiunit\framework\log\SyslogTargetTest::openlog(func_get_args());
    }

    function syslog()
    {
        return \yiiunit\framework\log\SyslogTargetTest::syslog(func_get_args());
    }

    function closelog()
    {
        return \yiiunit\framework\log\SyslogTargetTest::closelog(func_get_args());
    }
}

namespace yiiunit\framework\log {
    use PHPUnit_Framework_MockObject_MockObject;
    use yii\helpers\VarDumper;
    use yii\log\Logger;
    use yii\log\SyslogTarget;
    use yiiunit\TestCase;

    /**
     * Class SyslogTargetTest.
     *
     * @group log
     */
    class SyslogTargetTest extends TestCase
    {
        /**
         * Array of static functions.
         *
         * @var array
         */
        public static $functions = [];

        /**
         * @var PHPUnit_Framework_MockObject_MockObject
         */
        protected $syslogTarget;

        /**
         * Set up syslogTarget as the mock object.
         */
        protected function setUp(): void
        {
            $this->syslogTarget = $this->createPartialMock('yii\\log\\SyslogTarget', ['getMessagePrefix']);
        }

        /**
         * @covers \yii\log\SyslogTarget::export()
         */
        public function testExport(): void
        {
            $identity = 'identity string';
            $options = LOG_ODELAY | LOG_PID;
            $facility = 'facility string';
            $messages = [
                ['info message', Logger::LEVEL_INFO],
                ['error message', Logger::LEVEL_ERROR],
                ['warning message', Logger::LEVEL_WARNING],
                ['trace message', Logger::LEVEL_TRACE],
                ['profile message', Logger::LEVEL_PROFILE],
                ['profile begin message', Logger::LEVEL_PROFILE_BEGIN],
                ['profile end message', Logger::LEVEL_PROFILE_END],
            ];

            /** @var SyslogTarget $syslogTarget */
            $syslogTarget = $this->getMockBuilder(SyslogTarget::class)
                ->addMethods(['openlog', 'syslog', 'closelog'])
                ->onlyMethods(['formatMessage'])
                ->getMock();

            $syslogTarget->identity = $identity;
            $syslogTarget->options = $options;
            $syslogTarget->facility = $facility;
            $syslogTarget->messages = $messages;

            $syslogTarget->expects($this->once())
                ->method('openlog')
                ->with(
                    $this->equalTo($identity),
                    $this->equalTo($options),
                    $this->equalTo($facility)
                );

            $syslogTarget->expects($this->exactly(7))
                ->method('formatMessage')
                ->withConsecutive(
                    [$this->equalTo($messages[0])],
                    [$this->equalTo($messages[1])],
                    [$this->equalTo($messages[2])],
                    [$this->equalTo($messages[3])],
                    [$this->equalTo($messages[4])],
                    [$this->equalTo($messages[5])],
                    [$this->equalTo($messages[6])]
                )->willReturnMap([
                    [$messages[0], 'formatted message 1'],
                    [$messages[1], 'formatted message 2'],
                    [$messages[2], 'formatted message 3'],
                    [$messages[3], 'formatted message 4'],
                    [$messages[4], 'formatted message 5'],
                    [$messages[5], 'formatted message 6'],
                    [$messages[6], 'formatted message 7'],
                ]);

            /**
             * @link https://github.com/sebastianbergmann/phpunit/issues/5063
             */
            $matcher = $this->exactly(7);
            $syslogTarget
                ->expects($matcher)
                ->method('syslog')
                ->willReturnCallback(
                    function (...$parameters) use ($matcher): void {
                        if ($matcher->getInvocationCount() === 1) {
                            $this->assertEquals(LOG_INFO, $parameters[0]);
                            $this->assertEquals('formatted message 1', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 2) {
                            $this->assertEquals(LOG_ERR, $parameters[0]);
                            $this->assertEquals('formatted message 2', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 3) {
                            $this->assertEquals(LOG_WARNING, $parameters[0]);
                            $this->assertEquals('formatted message 3', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 4) {
                            $this->assertEquals(LOG_DEBUG, $parameters[0]);
                            $this->assertEquals('formatted message 4', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 5) {
                            $this->assertEquals(LOG_DEBUG, $parameters[0]);
                            $this->assertEquals('formatted message 5', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 6) {
                            $this->assertEquals(LOG_DEBUG, $parameters[0]);
                            $this->assertEquals('formatted message 6', $parameters[1]);
                        }

                        if ($matcher->getInvocationCount() === 7) {
                            $this->assertEquals(LOG_DEBUG, $parameters[0]);
                            $this->assertEquals('formatted message 7', $parameters[1]);
                        }
                    }
                );

            $syslogTarget->expects($this->once())->method('closelog');

            static::$functions['openlog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(3, $arguments);
                list($identity, $option, $facility) = $arguments;
                return $syslogTarget->openlog($identity, $option, $facility);
            };
            static::$functions['syslog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(2, $arguments);
                list($priority, $message) = $arguments;
                return $syslogTarget->syslog($priority, $message);
            };
            static::$functions['closelog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(0, $arguments);
                return $syslogTarget->closelog();
            };

            $syslogTarget->export();
        }

        /**
         * @covers \yii\log\SyslogTarget::export()
         *
         * See https://github.com/yiisoft/yii2/issues/14296
         */
        public function testFailedExport(): void
        {
            /** @var SyslogTarget $syslogTarget */
            $syslogTarget = $this->getMockBuilder(SyslogTarget::class)
                ->addMethods(['openlog', 'syslog', 'closelog'])
                ->onlyMethods(['formatMessage'])
                ->getMock();

            $syslogTarget->method('syslog')->willReturn(false);

            $syslogTarget->identity = 'identity string';
            $syslogTarget->options = LOG_ODELAY | LOG_PID;
            $syslogTarget->facility = 'facility string';
            $syslogTarget->messages = [
                ['test', Logger::LEVEL_INFO],
            ];

            static::$functions['openlog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(3, $arguments);
                list($identity, $option, $facility) = $arguments;
                return $syslogTarget->openlog($identity, $option, $facility);
            };
            static::$functions['syslog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(2, $arguments);
                list($priority, $message) = $arguments;
                return $syslogTarget->syslog($priority, $message);
            };
            static::$functions['closelog'] = function ($arguments) use ($syslogTarget) {
                $this->assertCount(0, $arguments);
                return $syslogTarget->closelog();
            };

            $this->expectException('yii\log\LogRuntimeException');
            $syslogTarget->export();
        }

        /**
         * @param $name
         * @param $arguments
         * @return mixed
         */
        public static function __callStatic($name, $arguments)
        {
            if (isset(static::$functions[$name]) && is_callable(static::$functions[$name])) {
                $arguments = isset($arguments[0]) ? $arguments[0] : $arguments;
                return forward_static_call(static::$functions[$name], $arguments);
            }
            static::fail("Function '$name' has not implemented yet!");
        }

        /**
         * @covers \yii\log\SyslogTarget::formatMessage()
         */
        public function testFormatMessageWhereTextIsString(): void
        {
            $message = ['text', Logger::LEVEL_INFO, 'category', 'timestamp'];

            $this->syslogTarget
                ->expects($this->once())
                ->method('getMessagePrefix')
                ->with($this->equalTo($message))
                ->willReturn('some prefix');

            $result = $this->syslogTarget->formatMessage($message);
            $this->assertEquals('some prefix[info][category] text', $result);
        }

        /**
         * @covers \yii\log\SyslogTarget::formatMessage()
         */
        public function testFormatMessageWhereTextIsException(): void
        {
            $exception = new \Exception('exception text');
            $message = [$exception, Logger::LEVEL_INFO, 'category', 'timestamp'];

            $this->syslogTarget
                ->expects($this->once())
                ->method('getMessagePrefix')
                ->with($this->equalTo($message))
                ->willReturn('some prefix');

            $result = $this->syslogTarget->formatMessage($message);
            $this->assertEquals('some prefix[info][category] ' . (string) $exception, $result);
        }

        /**
         * @covers \yii\log\SyslogTarget::formatMessage()
         */
        public function testFormatMessageWhereTextIsNotStringAndNotThrowable(): void
        {
            $text = new \stdClass();
            $text->var = 'some text';
            $message = [$text, Logger::LEVEL_ERROR, 'category', 'timestamp'];

            $this->syslogTarget
                ->expects($this->once())
                ->method('getMessagePrefix')
                ->with($this->equalTo($message))
                ->willReturn('some prefix');

            $result = $this->syslogTarget->formatMessage($message);
            $this->assertEquals('some prefix[error][category] ' . VarDumper::export($text), $result);
        }
    }
}

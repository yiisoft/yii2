<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Exception;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Xepozz\InternalMocker\MockerState;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yii\log\SyslogTarget;
use yiiunit\TestCase;

use function array_column;
use function array_keys;
use function array_map;

/**
 * Unit tests for {@see SyslogTarget}.
 */
#[Group('log')]
final class SyslogTargetTest extends TestCase
{
    private SyslogTarget&MockObject $syslogTarget;

    /**
     * Set up syslogTarget as the mock object.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->syslogTarget = $this->createPartialMock(SyslogTarget::class, ['getMessagePrefix']);
    }

    public function testDefaultOptions(): void
    {
        $target = new SyslogTarget();

        self::assertSame(
            LOG_ODELAY | LOG_PID,
            $target->options,
            'Default syslog options must delay opening and include the process ID.',
        );
    }

    public function testExport(): void
    {
        $identity = 'identity string';
        $options = LOG_ODELAY | LOG_PID;
        $facility = LOG_USER;
        $messages = [
            ['info message', Logger::LEVEL_INFO, 'application', 0.0, []],
            ['error message', Logger::LEVEL_ERROR, 'application', 0.0, []],
            ['warning message', Logger::LEVEL_WARNING, 'application', 0.0, []],
            ['trace message', Logger::LEVEL_TRACE, 'application', 0.0, []],
            ['profile message', Logger::LEVEL_PROFILE, 'application', 0.0, []],
            ['profile begin message', Logger::LEVEL_PROFILE_BEGIN, 'application', 0.0, []],
            ['profile end message', Logger::LEVEL_PROFILE_END, 'application', 0.0, []],
        ];

        /** @var SyslogTarget&MockObject $syslogTarget */
        $syslogTarget = $this->getMockBuilder(SyslogTarget::class)
            ->onlyMethods(['formatMessage'])
            ->getMock();

        $syslogTarget->identity = $identity;
        $syslogTarget->options = $options;
        $syslogTarget->facility = $facility;
        $syslogTarget->messages = $messages;

        $formatMatcher = $this->exactly(7);

        $syslogTarget
            ->expects($formatMatcher)
            ->method('formatMessage')
            ->willReturnCallback(
                function (...$parameters) use ($formatMatcher, $messages): string {
                    $invocation = $formatMatcher->numberOfInvocations();

                    self::assertSame(
                        $messages[$invocation - 1],
                        $parameters[0],
                        'Formatter must receive the expected message.',
                    );

                    return "formatted message {$invocation}";
                }
            );

        $priorities = [LOG_INFO, LOG_ERR, LOG_WARNING, LOG_DEBUG, LOG_DEBUG, LOG_DEBUG, LOG_DEBUG];

        MockerState::addCondition(
            'yii\log',
            'openlog',
            [$identity, $options, $facility],
            true,
        );

        foreach ($priorities as $index => $priority) {
            MockerState::addCondition(
                'yii\log',
                'syslog',
                [$priority, 'formatted message ' . ($index + 1)],
                true,
            );
        }

        MockerState::addCondition(
            'yii\log',
            'closelog',
            [],
            true,
        );

        $syslogTarget->export();

        self::assertSame(
            [[$identity, $options, $facility]],
            array_column(MockerState::getTraces('yii\log', 'openlog'), 'arguments'),
            'Syslog connection must be opened with the configured values.',
        );
        self::assertSame(
            array_map(
                static fn(int $priority, int $index): array => [
                    $priority,
                    'formatted message ' . ($index + 1),
                ],
                $priorities,
                array_keys($priorities),
            ),
            array_column(MockerState::getTraces('yii\log', 'syslog'), 'arguments'),
            'Every message must be written with its mapped syslog priority.',
        );
        self::assertCount(
            1,
            MockerState::getTraces('yii\log', 'closelog'),
            'Syslog connection must be closed after exporting messages.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14296
     */
    public function testFailedExport(): void
    {
        /** @var SyslogTarget&MockObject $syslogTarget */
        $syslogTarget = $this
            ->getMockBuilder(SyslogTarget::class)
            ->onlyMethods(['formatMessage'])
            ->getMock();

        $syslogTarget->method('formatMessage')->willReturn('test');

        $syslogTarget->identity = 'identity string';
        $syslogTarget->options = LOG_ODELAY | LOG_PID;
        $syslogTarget->facility = LOG_USER;
        $syslogTarget->messages = [
            ['test', Logger::LEVEL_INFO, 'application', 0.0, []],
        ];

        MockerState::addCondition(
            'yii\log',
            'openlog',
            [$syslogTarget->identity, $syslogTarget->options, $syslogTarget->facility],
            true,
        );
        MockerState::addCondition(
            'yii\log',
            'syslog',
            [LOG_INFO, 'test'],
            false,
        );

        $this->expectException(LogRuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to export log through system log!',
        );

        $syslogTarget->export();
    }

    public function testFormatMessageWhereTextIsString(): void
    {
        $message = ['text', Logger::LEVEL_INFO, 'category', 10.25, []];

        $this->syslogTarget
            ->expects($this->once())
            ->method('getMessagePrefix')
            ->with($this->equalTo($message))
            ->willReturn('some prefix');

        $result = $this->syslogTarget->formatMessage($message);

        self::assertSame(
            'some prefix[info][category] text',
            $result,
            'String message must be formatted for syslog.',
        );
    }

    public function testFormatMessageWhereTextIsException(): void
    {
        $exception = new Exception('exception text');

        $message = [$exception, Logger::LEVEL_INFO, 'category', 10.25, []];

        $this->syslogTarget
            ->expects($this->once())
            ->method('getMessagePrefix')
            ->with($this->equalTo($message))
            ->willReturn('some prefix');

        $result = $this->syslogTarget->formatMessage($message);

        self::assertSame(
            'some prefix[info][category] ' . (string) $exception,
            $result,
            'Exception message must be formatted for syslog.',
        );
    }

    public function testFormatMessageWhereTextIsNotStringAndNotThrowable(): void
    {
        $text = new stdClass();

        $text->var = 'some text';

        $message = [$text, Logger::LEVEL_ERROR, 'category', 10.25, []];

        $this->syslogTarget
            ->expects($this->once())
            ->method('getMessagePrefix')
            ->with($this->equalTo($message))
            ->willReturn('some prefix');

        $result = $this->syslogTarget->formatMessage($message);

        self::assertSame(
            'some prefix[error][category] ' . VarDumper::export($text),
            $result,
            'Complex message must be formatted for syslog.',
        );
    }
}

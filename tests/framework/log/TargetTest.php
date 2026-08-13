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
use Yii;
use yii\base\InvalidConfigException;
use yii\log\Dispatcher;
use yii\log\Logger;
use yii\log\Target;
use yii\web\IdentityInterface;
use yii\web\User;
use yiiunit\data\log\TargetStub;
use yiiunit\framework\log\providers\TargetProvider;
use yiiunit\TestCase;

use function array_map;
use function count;

/**
 * Unit tests for {@see yii\log\Target}.
 */
#[Group('log')]
class TargetTest extends TestCase
{
    #[DataProviderExternal(TargetProvider::class, 'filters')]
    public function testFilter(array $filter, array $expected): void
    {
        TargetStub::$exportedMessages = [];

        $logger = new Logger();

        $dispatcher = new Dispatcher(
            [
                'logger' => $logger,
                'targets' => [new TargetStub([...$filter, 'logVars' => []])],
                'flushInterval' => 1,
            ],
        );

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

        foreach (TargetStub::$exportedMessages as $message) {
            $messageColumn[] = $message[0];
        }

        self::assertSame(
            array_map(static fn(string $value): string => "test{$value}", $expected),
            $messageColumn,
            'Filtered messages must match the expected values and order.',
        );
    }

    public function testGetContextMessage(): void
    {
        $target = new TargetStub(
            [
                'logVars' => [
                    'A', '!A.A_b', 'A.A_d',
                    'B.B_a',
                    'C', 'C.C_a',
                    'D',
                ],
                'maskVars' => [
                    'C.C_b',
                    'D.D_a'
                ],
            ],
        );

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

        foreach (['A_a', 'A_c', 'B_a', 'C_a', 'C_b', 'C_c', '***'] as $expected) {
            self::assertStringContainsString(
                $expected,
                $context,
                "Context message must contain '$expected'.",
            );
        }

        foreach (['A_b', 'B_b', 'B_c', 'D_a', 'D_b', 'D_c', 'E_a', 'E_b', 'E_c', 'mySecret'] as $excluded) {
            self::assertStringNotContainsString(
                $excluded,
                $context,
                "Context message must not contain '$excluded'.",
            );
        }
    }

    public function testSetupLevelsThroughArray(): void
    {
        $target = $this
            ->getMockBuilder('yii\\log\\Target')
            ->onlyMethods(['export'])
            ->getMock();

        $target->setLevels(['info', 'error']);

        self::assertEquals(
            Logger::LEVEL_INFO | Logger::LEVEL_ERROR,
            $target->getLevels(),
            'Named levels must be converted to their combined bitmap.',
        );

        $target->setLevels(['trace']);

        self::assertEquals(
            Logger::LEVEL_TRACE,
            $target->getLevels(),
            'A single named level must be converted to its bitmap value.',
        );

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Unrecognized level: unknown level',
        );

        $target->setLevels(['info', 'unknown level']);
    }

    public function testSetupLevelsThroughBitmap(): void
    {
        $target = $this
            ->getMockBuilder('yii\\log\\Target')
            ->onlyMethods(['export'])
            ->getMock();

        $target->setLevels(Logger::LEVEL_INFO | Logger::LEVEL_WARNING);

        self::assertEquals(
            Logger::LEVEL_INFO | Logger::LEVEL_WARNING,
            $target->getLevels(),
            'Combined level bitmap must be preserved.',
        );

        $target->setLevels(Logger::LEVEL_TRACE);

        self::assertEquals(
            Logger::LEVEL_TRACE,
            $target->getLevels(),
            'Single level bitmap must be preserved.',
        );

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Incorrect 128 value',
        );

        $target->setLevels(128);
    }

    public function testGetEnabled(): void
    {
        /** @var Target $target */
        $target = $this
            ->getMockBuilder('yii\\log\\Target')
            ->onlyMethods(['export'])
            ->getMock();

        $target->enabled = true;

        self::assertTrue(
            $target->enabled,
            'Target must be enabled after assigning `true`.',
        );

        $target->enabled = false;

        self::assertFalse(
            $target->enabled,
            'Target must be disabled after assigning `false`.',
        );

        $target->enabled = fn($target) => empty($target->messages);

        self::assertTrue(
            $target->enabled,
            'Enabled callback must be evaluated against the target.',
        );
    }

    #[DataProviderExternal(TargetProvider::class, 'formatMessage')]
    public function testFormatMessage(array $message, bool $microtime, string $expected): void
    {
        /** @var Target $target */
        $target = $this
            ->getMockBuilder(Target::class)
            ->onlyMethods(['export'])
            ->getMock();

        $target->microtime = $microtime;

        date_default_timezone_set('UTC');

        self::assertSame(
            $expected,
            $target->formatMessage($message),
            'Formatted message must match the expected value.',
        );
    }

    public function testGetMessagePrefixFromCallable(): void
    {
        $target = new TargetStub();

        $target->prefix = static fn(array $message): string => $message[0];

        self::assertSame(
            'custom-prefix',
            $target->getMessagePrefix(['custom-prefix']),
            'Configured prefix callable must receive the message.',
        );
    }

    public function testGetMessagePrefixWithAuthenticatedUser(): void
    {
        $identity = $this->createMock(IdentityInterface::class);

        $identity->method('getId')->willReturn(42);

        $user = $this->createMock(User::class);

        $user->method('getIdentity')->with(false)->willReturn($identity);

        $this->mockApplication();

        Yii::$app->set('user', $user);

        self::assertSame(
            $user,
            Yii::$app->get('user'),
            'Application must return the configured user component.',
        );

        $target = new TargetStub();

        self::assertSame(
            '[-][42][-]',
            $target->getMessagePrefix([]),
            'Prefix must include the authenticated user ID.',
        );
    }

    public function testCollectMessageStructure(): void
    {
        $target = new TargetStub(['logVars' => ['_SERVER']]);

        TargetStub::$exportedMessages = [];

        $messages = [
            ['test', 1, 'application', 1_560_428_356.212978, [], 1_888_416]
        ];

        $target->collect($messages, false);

        self::assertCount(
            2,
            TargetStub::$exportedMessages,
            'Collected messages must include the log entry and context entry.',
        );
        self::assertCount(
            6,
            TargetStub::$exportedMessages[0],
            'Log entry must preserve the six-element message structure.',
        );
        self::assertCount(
            6,
            TargetStub::$exportedMessages[1],
            'Context entry must preserve the six-element message structure.',
        );
    }

    public function testBreakProfilingWithFlushWithProfilingDisabled(): void
    {
        $dispatcher = $this->createPartialMock(Dispatcher::class, ['dispatch']);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    fn($messages) => count($messages) === 2
                    && $messages[0][0] === 'token.a'
                    && $messages[0][1] == Logger::LEVEL_PROFILE_BEGIN
                    && $messages[1][0] === 'info',
                ),
                false,
            );

        $logger = new Logger(
            [
                'dispatcher' => $dispatcher,
                'flushInterval' => 2,
            ],
        );

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('info', Logger::LEVEL_INFO, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testNotBreakProfilingWithFlushWithProfilingEnabled(): void
    {
        $dispatcher = $this->createPartialMock(Dispatcher::class, ['dispatch']);

        /**
         * @link https://github.com/sebastianbergmann/phpunit/issues/5063
         */
        $matcher = $this->exactly(2);

        $dispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(
                function (...$parameters) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        $callback = fn($messages): bool => count($messages) === 1 && $messages[0][0] === 'info';

                        self::assertTrue(
                            $callback($parameters[0]),
                            'First dispatch must contain only the non-profiling message.',
                        );
                        self::assertFalse(
                            $parameters[1],
                            'First dispatch must not be final.',
                        );
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $callback = fn($messages): bool => count($messages) === 2
                            && $messages[0][0] === 'token.a'
                            && $messages[0][1] === Logger::LEVEL_PROFILE_BEGIN
                            && $messages[1][0] === 'token.a'
                            && $messages[1][1] === Logger::LEVEL_PROFILE_END;

                        self::assertTrue(
                            $callback($parameters[0]),
                            'Second dispatch must contain the profiling message pair.',
                        );
                        self::assertFalse(
                            $parameters[1],
                            'Second dispatch must not be final.',
                        );
                    }
                },
            );

        $logger = new Logger(
            [
                'profilingAware' => true,
                'dispatcher' => $dispatcher,
                'flushInterval' => 2,
            ],
        );

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('info', Logger::LEVEL_INFO, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testFlushingWithProfilingEnabledAndOverflow(): void
    {
        $dispatcher = $this->createPartialMock(Dispatcher::class, ['dispatch']);

        /**
         * @link https://github.com/sebastianbergmann/phpunit/issues/5063
         */
        $matcher = $this->exactly(3);

        $dispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(
                function (...$parameters) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        $callback = fn($messages): bool => count($messages) === 2
                            && $messages[0][0] === 'token.a'
                            && $messages[0][1] === Logger::LEVEL_PROFILE_BEGIN
                            && $messages[1][0] === 'token.b'
                            && $messages[1][1] === Logger::LEVEL_PROFILE_BEGIN;

                        self::assertTrue(
                            $callback($parameters[0]),
                            'First dispatch must contain both profiling begin messages.',
                        );
                        self::assertFalse(
                            $parameters[1],
                            'First dispatch must not be final.',
                        );
                    }

                    if ($matcher->numberOfInvocations() === 2) {
                        $callback = fn($messages): bool => count($messages) === 1
                            && $messages[0][0] === 'Number of dangling profiling block messages reached flushInterval value and therefore these were flushed. Please consider setting higher flushInterval value or making profiling blocks shorter.';

                        self::assertTrue(
                            $callback($parameters[0]),
                            'Second dispatch must contain the profiling overflow warning.',
                        );
                        self::assertFalse(
                            $parameters[1],
                            'Second dispatch must not be final.',
                        );
                    }

                    if ($matcher->numberOfInvocations() === 3) {
                        $callback = fn($messages): bool => count($messages) === 2
                            && $messages[0][0] === 'token.b'
                            && $messages[0][1] === Logger::LEVEL_PROFILE_END
                            && $messages[1][0] === 'token.a'
                            && $messages[1][1] === Logger::LEVEL_PROFILE_END;

                        self::assertTrue(
                            $callback($parameters[0]),
                            'Third dispatch must contain both profiling end messages.',
                        );
                        self::assertFalse(
                            $parameters[1],
                            'Third dispatch must not be final.',
                        );
                    }
                },
            );

        $logger = new Logger(
            [
                'profilingAware' => true,
                'dispatcher' => $dispatcher,
                'flushInterval' => 2,
            ],
        );

        $logger->log('token.a', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('token.b', Logger::LEVEL_PROFILE_BEGIN, 'category');
        $logger->log('token.b', Logger::LEVEL_PROFILE_END, 'category');
        $logger->log('token.a', Logger::LEVEL_PROFILE_END, 'category');
    }

    public function testWildcardsInMaskVars(): void
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

        $target = new TargetStub(
            [
                'logVars' => ['_SERVER', '_TEST'],
                'maskVars' => [
                    // option 1: exact value(s)
                    '_SERVER.DOCUMENT_ROOT',
                    // option 2: pattern(s)
                    '_TEST.*password*',
                ],
            ],
        );

        $message = $target->getContextMessage();

        self::assertStringContainsString(
            "'DOCUMENT_ROOT' => '***'",
            $message,
            'Context message must mask the document root.',
        );
        self::assertStringNotContainsString(
            $password,
            $message,
            'Context message must not expose wildcard-matched secrets.',
        );
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\LogLevel;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Yii;
use yii\base\InvalidConfigException;
use yii\log\Dispatcher;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yii\log\PsrLogger;
use yii\log\PsrMessage;
use yii\log\PsrTarget;
use yiiunit\framework\log\mocks\RecordingPsrLogger;
use yiiunit\framework\log\providers\PsrLogProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\log\PsrTarget} exporting Yii log messages to a PSR-3 logger.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('log')]
final class PsrTargetTest extends TestCase
{
    private RecordingPsrLogger $logger;
    private PsrTarget $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new RecordingPsrLogger();
        $this->target = new PsrTarget(
            [
                'logger' => $this->logger,
                'logVars' => [],
            ],
        );
    }

    #[DataProviderExternal(PsrLogProvider::class, 'yiiToPsrLevel')]
    public function testMapsYiiLevels(int $yiiLevel, string $psrLevel): void
    {
        $this->target->messages = [
            ['Message', $yiiLevel, 'app.category', 10.25, [], 1024],
        ];

        $this->target->export();

        self::assertSame(
            $psrLevel,
            $this->logger->records[0]['level'],
            'PSR level must match the map.',
        );
        self::assertSame(
            'Message',
            $this->logger->records[0]['message'],
            'Text must be passed through.',
        );
    }

    public function testExportsYiiMetadataAsContext(): void
    {
        $this->target->messages = [
            ['Message', Logger::LEVEL_INFO, 'app.category', 10.25, [['file' => 'index.php', 'line' => 42]], 1024],
        ];

        $this->target->export();

        self::assertSame(
            [
                'trace' => [['file' => 'index.php', 'line' => 42]],
                'memory' => 1024,
                'category' => 'app.category',
            ],
            $this->logger->records[0]['context'],
            'Context must carry trace, memory, and category.',
        );
    }

    public function testOmitsEmptyTraceFromContext(): void
    {
        $this->target->messages = [
            ['Message', Logger::LEVEL_INFO, 'app.category', 10.25, [], 1024],
        ];

        $this->target->export();

        self::assertArrayNotHasKey(
            'trace',
            $this->logger->records[0]['context'],
            'No `trace` key without collected traces.',
        );
    }

    public function testOptionallyExportsOriginalTimestamp(): void
    {
        $this->target->addTimestampToContext = true;

        $this->target->messages = [
            ['Message', Logger::LEVEL_INFO, 'app.category', 10.25, [], 1024],
        ];

        $this->target->export();

        self::assertSame(
            10.25,
            $this->logger->records[0]['context']['timestamp'],
            'Timestamp must be exported.',
        );
    }

    public function testSupportsCustomLevelMap(): void
    {
        $target = new PsrTarget(
            [
                'logger' => $this->logger,
                'levelMap' => [Logger::LEVEL_ERROR => LogLevel::CRITICAL],
            ],
        );

        $target->messages = [
            ['Message', Logger::LEVEL_ERROR, 'app.category', 10.25, [], 1024],
        ];

        $target->export();

        self::assertSame(
            LogLevel::CRITICAL,
            $this->logger->records[0]['level'],
            'Custom map must resolve the level.',
        );
    }

    public function testPreservesPsrMessageLevelAndContext(): void
    {
        $psrMessage = new PsrMessage(
            'User {id}',
            [
                'id' => 42,
                'custom' => 'value',
                'trace' => ['user trace'],
                'memory' => 1,
                'category' => 'user.category',
            ],
            LogLevel::CRITICAL
        );

        $this->target->messages = [
            [$psrMessage, Logger::LEVEL_ERROR, 'app.category', 10.25, ['yii trace'], 1024],
        ];

        $this->target->export();

        self::assertSame(
            LogLevel::CRITICAL,
            $this->logger->records[0]['level'],
            'Original PSR-3 level must win.',
        );
        self::assertSame(
            'User {id}',
            $this->logger->records[0]['message'],
            'Raw message must not be interpolated.',
        );
        self::assertSame(
            [
                'id' => 42,
                'custom' => 'value',
                'trace' => ['user trace'],
                'memory' => 1,
                'category' => 'user.category',
            ],
            $this->logger->records[0]['context'],
            'User context keys must win over Yii metadata.',
        );
    }

    public function testPreservesNullPsrMessageContextMetadata(): void
    {
        $context = [
            'trace' => null,
            'memory' => null,
            'category' => null,
            'timestamp' => null,
        ];

        $this->target->addTimestampToContext = true;
        $this->target->messages = [
            [
                new PsrMessage('Message', $context, LogLevel::INFO),
                Logger::LEVEL_INFO,
                'app.category',
                10.25,
                ['yii trace'],
                1024,
            ],
        ];

        $this->target->export();

        self::assertSame(
            $context,
            $this->logger->records[0]['context'],
            'Explicit null context values must take precedence over Yii metadata.',
        );
    }

    public function testExportsThrowableAsExceptionContext(): void
    {
        $exception = new RuntimeException('Failure');

        $this->target->messages = [
            [$exception, Logger::LEVEL_ERROR, 'app.error', 10.25, [], 1024],
        ];

        $this->target->export();

        self::assertSame(
            (string) $exception,
            $this->logger->records[0]['message'],
            'Exception must be stringified.',
        );
        self::assertSame(
            $exception,
            $this->logger->records[0]['context']['exception'],
            'Exception must be in context.',
        );
    }

    public function testCanExtractThrowableTrace(): void
    {
        $exception = $this->createExceptionWithTrace();

        $this->target->extractExceptionTrace = true;
        $this->target->messages = [
            [$exception, Logger::LEVEL_ERROR, 'app.error', 10.25, ['original trace'], 1024],
        ];

        $this->target->export();

        self::assertSame(
            'Failure',
            $this->logger->records[0]['message'],
            'Message must be the exception message.',
        );
        self::assertSame(
            $exception,
            $this->logger->records[0]['context']['exception'],
            'Exception must be in context.',
        );
        self::assertNotSame(
            ['original trace'],
            $this->logger->records[0]['context']['trace'],
            'Yii trace must be replaced by the exception trace.',
        );
        self::assertNotEmpty(
            $this->logger->records[0]['context']['trace'],
            'Extracted trace must not be empty.',
        );
    }

    public function testExportsComplexYiiMessage(): void
    {
        $this->target->messages = [
            [['key' => 'value'], Logger::LEVEL_INFO, 'app.category', 10.25, [], 1024],
        ];

        $this->target->export();

        self::assertStringContainsString(
            "'key' => 'value'",
            $this->logger->records[0]['message'],
            'Non-string message must be exported as a dump.',
        );
    }

    public function testInheritedYiiLevelFiltering(): void
    {
        $target = new PsrTarget(
            [
                'logger' => $this->logger,
                'logVars' => [],
                'levels' => ['error'],
            ],
        );

        $target->collect(
            [
                ['Error', Logger::LEVEL_ERROR, 'app', 10.0, [], 1024],
                ['Warning', Logger::LEVEL_WARNING, 'app', 11.0, [], 1024],
            ],
            true,
        );

        self::assertCount(
            1,
            $this->logger->records,
            'Only the error message must pass the filter.',
        );
        self::assertSame(
            'Error',
            $this->logger->records[0]['message'],
            'Remaining message must be the error.',
        );
    }

    public function testExactPsrLevelFiltering(): void
    {
        $target = new PsrTarget(
            [
                'logger' => $this->logger,
                'logVars' => [],
                'psrLevels' => [LogLevel::CRITICAL],
            ],
        );

        $target->messages = [
            [new PsrMessage('Critical', [], LogLevel::CRITICAL), Logger::LEVEL_ERROR, 'app', 10.0, [], 1024],
            ['Yii error', Logger::LEVEL_ERROR, 'app', 11.0, [], 1024],
        ];

        $target->export();

        self::assertCount(
            1,
            $this->logger->records,
            'Only the critical message must pass.',
        );
        self::assertSame(
            LogLevel::CRITICAL,
            $this->logger->records[0]['level'],
            'Level must be critical.',
        );
        self::assertSame(
            'Critical',
            $this->logger->records[0]['message'],
            'Message must be the critical one.',
        );
        self::assertSame(
            [LogLevel::CRITICAL],
            $target->getPsrLevels(),
            'Getter must return the normalized levels.',
        );
    }

    public function testEmptyPsrLevelsEnableAllLevels(): void
    {
        $this->target->setPsrLevels([LogLevel::CRITICAL]);
        $this->target->setPsrLevels([]);

        self::assertNull(
            $this->target->getPsrLevels(),
            'An empty PSR-3 level filter must enable all levels.',
        );

        $this->target->setPsrLevels([LogLevel::CRITICAL]);
        $this->target->setPsrLevels(null);

        self::assertNull(
            $this->target->getPsrLevels(),
            'A null PSR-3 level filter must enable all levels.',
        );
    }

    public function testThrowInvalidConfigExceptionForUnknownPsrFilterLevel(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Unrecognized PSR-3 log level: unknown.',
        );

        $this->target->setPsrLevels(['unknown']);
    }

    public function testThrowInvalidConfigExceptionForNonStringPsrFilterLevel(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Unrecognized PSR-3 log level: array.',
        );

        Yii::configure($this->target, ['psrLevels' => [['critical']]]);
    }

    public function testThrowInvalidConfigExceptionWhenLoggerIsMissing(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'PsrTarget::logger must be configured with a PSR-3 logger.',
        );

        new PsrTarget();
    }

    public function testThrowInvalidConfigExceptionForInvalidLevelMap(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'PsrTarget::levelMap must map integer Yii levels to valid PSR-3 level strings.',
        );

        new PsrTarget([
            'logger' => $this->logger,
            'levelMap' => [Logger::LEVEL_ERROR => 'invalid'],
        ]);
    }

    public function testThrowInvalidConfigExceptionForPsrLoggerDestination(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'PsrTarget cannot use PsrLogger as its destination',
        );

        new PsrTarget(['logger' => new PsrLogger(new Logger(['flushInterval' => 0]))]);
    }

    public function testThrowLogRuntimeExceptionForUnmappedYiiLevel(): void
    {
        $this->target->messages = [
            ['Message', 128, 'app', 10.0, [], 1024],
        ];

        $this->expectException(LogRuntimeException::class);
        $this->expectExceptionMessage(
            "Unable to map Yii log level '128' to a PSR-3 level.",
        );

        $this->target->export();
    }

    public function testRoundTripPreservesPsrLevelAndContext(): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);

        new Dispatcher(
            [
                'logger' => $yiiLogger,
                'targets' => [$this->target],
            ],
        );

        $logger = new PsrLogger($yiiLogger, category: 'app.roundtrip');

        $logger->critical('Request {id} failed', ['id' => 42, 'requestId' => 'request-42']);
        $yiiLogger->flush(true);

        self::assertSame(
            LogLevel::CRITICAL,
            $this->logger->records[0]['level'],
            'PSR level must survive.',
        );
        self::assertSame(
            'Request {id} failed',
            $this->logger->records[0]['message'],
            'Raw message must survive.',
        );
        self::assertSame(
            42,
            $this->logger->records[0]['context']['id'],
            'Context `id` must survive.',
        );
        self::assertSame(
            'request-42',
            $this->logger->records[0]['context']['requestId'],
            'Context value must survive.',
        );
        self::assertSame(
            'app.roundtrip',
            $this->logger->records[0]['context']['category'],
            'Category must be added to context.',
        );
    }

    private function createExceptionWithTrace(): RuntimeException
    {
        try {
            throw new RuntimeException('Failure');
        } catch (RuntimeException $exception) {
            return $exception;
        }
    }
}

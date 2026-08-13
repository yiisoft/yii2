<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use Stringable;
use Yii;
use yii\log\Logger;
use yii\log\PsrLogger;
use yii\log\PsrMessage;
use yiiunit\framework\log\providers\PsrLogProvider;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\log\PsrLogger} adapting PSR-3 log calls to the Yii logger.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('log')]
final class PsrLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        Yii::setLogger(null);

        parent::tearDown();
    }

    #[DataProviderExternal(PsrLogProvider::class, 'psrToYiiLevel')]
    public function testMapsPsrLevelsAndPreservesContext(string $psrLevel, int $yiiLevel): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($yiiLogger);

        $context = ['requestId' => 'request-42'];

        $logger->{$psrLevel}('Message', $context);

        self::assertCount(
            1,
            $yiiLogger->messages,
            'Exactly one message must be recorded.',
        );
        self::assertSame(
            $yiiLevel,
            $yiiLogger->messages[0][1],
            'Yii level must match the map.',
        );
        self::assertSame(
            'application',
            $yiiLogger->messages[0][2],
            'Default category must be used.',
        );
        self::assertInstanceOf(
            PsrMessage::class,
            $yiiLogger->messages[0][0],
            'Text must be wrapped in a PsrMessage.',
        );
        self::assertSame(
            'Message',
            $yiiLogger->messages[0][0]->getMessage(),
            'Original text must be preserved.',
        );
        self::assertSame(
            $context,
            $yiiLogger->messages[0][0]->getContext(),
            'Context must be preserved.',
        );
        self::assertSame(
            $psrLevel,
            $yiiLogger->messages[0][0]->getLevel(),
            'Original PSR-3 level must be preserved.',
        );
    }

    public function testUsesCategoryFromContext(): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($yiiLogger, category: 'default.category');

        $logger->info('Message', ['category' => 'context.category']);

        self::assertSame(
            'context.category',
            $yiiLogger->messages[0][2],
            'Context category must override the default.',
        );
    }

    public function testUsesConfiguredCategoryWhenContextCategoryIsNotAString(): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($yiiLogger, category: 'default.category');

        $logger->info('Message', ['category' => ['invalid']]);

        self::assertSame(
            'default.category',
            $yiiLogger->messages[0][2],
            'Non-string category must fall back.',
        );
    }

    public function testResolvesCurrentYiiLoggerForEveryCall(): void
    {
        $first = new Logger(['flushInterval' => 0]);
        $second = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger();

        Yii::setLogger($first);

        $logger->info('First');

        Yii::setLogger($second);

        $logger->info('Second');

        self::assertSame(
            'First',
            $first->messages[0][0]->getMessage(),
            'First call must reach the first logger.',
        );
        self::assertSame(
            'Second',
            $second->messages[0][0]->getMessage(),
            'Second call must reach the swapped logger.',
        );
    }

    public function testExplicitYiiLoggerIsNotReplacedByGlobalLogger(): void
    {
        $explicit = new Logger(['flushInterval' => 0]);
        $global = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($explicit);

        Yii::setLogger($global);

        $logger->info('Message');

        self::assertCount(
            1,
            $explicit->messages,
            'Explicit logger must receive the message.',
        );
        self::assertSame(
            [],
            $global->messages,
            'Global logger must stay untouched.',
        );
    }

    public function testAcceptsStringableMessage(): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($yiiLogger);

        $message = new class () implements Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $logger->info($message);

        self::assertSame(
            'Stringable message',
            $yiiLogger->messages[0][0]->getMessage(),
            "Stringable must be cast to 'string'.",
        );
    }

    public function testSupportsCustomLevelMap(): void
    {
        $yiiLogger = new Logger(['flushInterval' => 0]);
        $logger = new PsrLogger($yiiLogger, ['audit' => Logger::LEVEL_INFO]);

        $logger->log('audit', 'Audit message');

        self::assertSame(
            Logger::LEVEL_INFO,
            $yiiLogger->messages[0][1],
            'Custom map must resolve the Yii level.',
        );
        self::assertSame(
            'audit',
            $yiiLogger->messages[0][0]->getLevel(),
            'Original PSR-3 level must be preserved.',
        );
    }

    public function testThrowInvalidArgumentExceptionForUnknownPsrLevel(): void
    {
        $logger = new PsrLogger(new Logger(['flushInterval' => 0]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unsupported PSR-3 log level: unknown.',
        );

        $logger->log('unknown', 'Message');
    }

    public function testThrowInvalidArgumentExceptionForNonStringPsrLevel(): void
    {
        $logger = new PsrLogger(new Logger(['flushInterval' => 0]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The PSR-3 log level must be a string, int given.',
        );

        $logger->log(1, 'Message');
    }

    public function testThrowInvalidArgumentExceptionForInvalidLevelMap(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The PSR-3 level map must contain string keys and integer values.',
        );

        new PsrLogger(levelMap: ['info' => 'invalid']);
    }
}

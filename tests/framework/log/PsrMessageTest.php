<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use Psr\Log\LogLevel;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Stringable;
use yii\log\Logger;
use yii\log\PsrMessage;
use yii\log\Target;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\log\PsrMessage} storing PSR-3 message data and interpolating context placeholders.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('log')]
final class PsrMessageTest extends TestCase
{
    public function testStoresOriginalDataAndInterpolatesSafeValues(): void
    {
        $stringable = new class () implements Stringable {
            public function __toString(): string
            {
                return 'stringable';
            }
        };

        $unconvertible = new class () {
        };

        $throwing = new class () implements Stringable {
            public function __toString(): string
            {
                throw new RuntimeException('Unable to convert context value.');
            }
        };

        $context = [
            'name' => 'Alexander',
            'attempt' => 3,
            'enabled' => true,
            'missing' => null,
            'stringable' => $stringable,
            'array' => ['not converted'],
            'object' => $unconvertible,
            'throwing' => $throwing,
        ];

        $message = new PsrMessage(
            '{name}:{attempt}:{enabled}:{missing}:{stringable}:{array}:{object}:{throwing}',
            $context,
            LogLevel::NOTICE,
        );

        self::assertSame(
            '{name}:{attempt}:{enabled}:{missing}:{stringable}:{array}:{object}:{throwing}',
            $message->getMessage(),
            'Raw message must not be interpolated.',
        );
        self::assertSame(
            $context,
            $message->getContext(),
            'Context must be stored verbatim.',
        );
        self::assertSame(
            LogLevel::NOTICE,
            $message->getLevel(),
            'Original level must be stored.',
        );
        self::assertSame(
            'Alexander:3:1::stringable:{array}:{object}:{throwing}',
            (string) $message,
            'Only safely convertible values must be interpolated.',
        );
    }

    public function testAcceptsStringableMessage(): void
    {
        $text = new class () implements Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $message = new PsrMessage($text, [], LogLevel::INFO);

        self::assertSame(
            'Stringable message',
            $message->getMessage(),
            'Stringable must be cast eagerly.',
        );
        self::assertSame(
            'Stringable message',
            (string) $message,
            'String conversion must return the cast text.',
        );
    }

    public function testBaseTargetFormatsInterpolatedMessage(): void
    {
        $target = new class () extends Target {
            public function export(): void
            {
            }
        };

        $message = new PsrMessage('User {id} signed in', ['id' => 42], LogLevel::INFO);

        $formatted = $target->formatMessage([$message, Logger::LEVEL_INFO, 'app.auth', 0.0, []]);

        self::assertStringContainsString(
            '[info][app.auth] User 42 signed in',
            $formatted,
            'Formatted output must contain the interpolated text.',
        );
    }
}

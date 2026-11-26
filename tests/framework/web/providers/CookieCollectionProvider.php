<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\web\providers;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use yii\web\Cookie;

/**
 * Data provider for {@see \yiiunit\framework\web\CookieCollectionTest} test cases.
 *
 * Provides representative input/output pairs for cookie lookup by name, value and expiration.
 */
final class CookieCollectionProvider
{
    /**
     * @return array<string, array{string, array<string, Cookie>, bool}>
     */
    public static function has(): array
    {
        return [
            'empty collection' => [
                'test',
                [],
                false,
            ],
            'another cookie name' => [
                'test',
                [
                    'abc' => new Cookie(['value' => 'abc']),
                ],
                false,
            ],
            'cookie without value' => [
                'test',
                [
                    'test' => new Cookie(),
                ],
                false,
            ],
            'null expiration' => [
                'test',
                [
                    'test' => new Cookie(['value' => 'test', 'expire' => null]),
                ],
                true,
            ],
            'default expiration' => [
                'test',
                [
                    'test' => new Cookie(['value' => 'test']),
                ],
                true,
            ],
        ];
    }

    /**
     * Each data set holds a callable that formats a timestamp, and the offset in seconds from the current time.
     *
     * The timestamp is not resolved here: PHPUnit builds every data set before the first test runs, so a value computed
     * in the provider is stale by the time a long suite reaches the test.
     *
     * @return array<string, array{callable(int): (int|string|DateTimeInterface), int, bool}>
     */
    public static function hasWithExpire(): array
    {
        return [
            'numeric string in the future' => [
                static fn (int $time): string => (string) $time,
                100,
                true,
            ],
            'numeric string in the past' => [
                static fn (int $time): string => (string) $time,
                -100,
                false,
            ],
            'integer in the future' => [
                static fn (int $time): int => $time,
                100,
                true,
            ],
            'integer in the past' => [
                static fn (int $time): int => $time,
                -100,
                false,
            ],
            'date and time string in the future' => [
                static fn (int $time): string => date('Y-m-d H:i:s', $time),
                100,
                true,
            ],
            'date string in the past' => [
                static fn (int $time): string => date('Y-m-d', $time),
                -100,
                false,
            ],
            'DateTimeImmutable in the future' => [
                static fn (int $time): DateTimeImmutable => (new DateTimeImmutable())->setTimestamp($time),
                100,
                true,
            ],
            'DateTimeImmutable in the past' => [
                static fn (int $time): DateTimeImmutable => (new DateTimeImmutable())->setTimestamp($time),
                -100,
                false,
            ],
            'DateTime in the future' => [
                static fn (int $time): DateTime => (new DateTime())->setTimestamp($time),
                100,
                true,
            ],
            'DateTime in the past' => [
                static fn (int $time): DateTime => (new DateTime())->setTimestamp($time),
                -100,
                false,
            ],
        ];
    }
}

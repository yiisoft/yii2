<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\providers;

use Psr\Log\LogLevel;
use yii\log\Logger;

/**
 * Data provider for PSR-3 logging interoperability test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PsrLogProvider
{
    /**
     * @return array<string, array{string, int}>
     */
    public static function psrToYiiLevel(): array
    {
        return [
            'alert' => [LogLevel::ALERT, Logger::LEVEL_ERROR],
            'critical' => [LogLevel::CRITICAL, Logger::LEVEL_ERROR],
            'debug' => [LogLevel::DEBUG, Logger::LEVEL_TRACE],
            'emergency' => [LogLevel::EMERGENCY, Logger::LEVEL_ERROR],
            'error' => [LogLevel::ERROR, Logger::LEVEL_ERROR],
            'info' => [LogLevel::INFO, Logger::LEVEL_INFO],
            'notice' => [LogLevel::NOTICE, Logger::LEVEL_WARNING],
            'warning' => [LogLevel::WARNING, Logger::LEVEL_WARNING],
        ];
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function yiiToPsrLevel(): array
    {
        return [
            'error' => [Logger::LEVEL_ERROR, LogLevel::ERROR],
            'info' => [Logger::LEVEL_INFO, LogLevel::INFO],
            'profile begin' => [Logger::LEVEL_PROFILE_BEGIN, LogLevel::DEBUG],
            'profile end' => [Logger::LEVEL_PROFILE_END, LogLevel::DEBUG],
            'profile' => [Logger::LEVEL_PROFILE, LogLevel::DEBUG],
            'trace' => [Logger::LEVEL_TRACE, LogLevel::DEBUG],
            'warning' => [Logger::LEVEL_WARNING, LogLevel::WARNING],
        ];
    }
}

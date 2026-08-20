<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\providers;

use yii\log\Logger;

/**
 * Data provider for {@see \yiiunit\framework\log\LoggerTest}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LoggerProvider
{
    /**
     * @return array<string, array{int, string}>
     */
    public static function levelNames(): array
    {
        return [
            'error' => [Logger::LEVEL_ERROR, 'error'],
            'info' => [Logger::LEVEL_INFO, 'info'],
            'profile begin' => [Logger::LEVEL_PROFILE_BEGIN, 'profile begin'],
            'profile end' => [Logger::LEVEL_PROFILE_END, 'profile end'],
            'profile' => [Logger::LEVEL_PROFILE, 'profile'],
            'trace' => [Logger::LEVEL_TRACE, 'trace'],
            'unknown' => [0, 'unknown'],
            'warning' => [Logger::LEVEL_WARNING, 'warning'],
        ];
    }

    /**
     * @return array<string, array{int}>
     */
    public static function nonProfilingMessages(): array
    {
        return [
            'error' => [Logger::LEVEL_ERROR],
            'info' => [Logger::LEVEL_INFO],
            'profile' => [Logger::LEVEL_PROFILE],
            'trace' => [Logger::LEVEL_TRACE],
            'warning' => [Logger::LEVEL_WARNING],
        ];
    }
}

<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\providers;

use RuntimeException;
use yii\helpers\VarDumper;
use yii\log\Logger;

/**
 * Data provider for log target test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class TargetProvider
{
    /**
     * @return list<array{array<string, int|list<string>>, list<string>}>
     */
    public static function filters(): array
    {
        return [
            [[], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],

            [['levels' => 0], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']],
            [
                ['levels' => Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_ERROR | Logger::LEVEL_TRACE],
                ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'],
            ],
            [['levels' => ['error']], ['B', 'G', 'H', 'I']],
            [['levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H', 'I']],
            [['levels' => ['error', 'warning']], ['B', 'C', 'G', 'H', 'I']],
            [['levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C', 'G', 'H', 'I']],

            [['categories' => ['application']], ['A', 'B', 'C', 'D', 'E']],
            [['categories' => ['application*']], ['A', 'B', 'C', 'D', 'E', 'F']],
            [['categories' => ['application.*']], ['F']],
            [['categories' => ['application.components']], []],
            [['categories' => ['application.components.Test']], ['F']],
            [['categories' => ['application.components.*']], ['F']],
            [['categories' => ['application.*', 'yii.db.*']], ['F', 'G', 'H']],
            [
                ['categories' => ['application.*', 'yii.db.*'], 'except' => ['yii.db.Command.*', 'yii\db\*']],
                ['F', 'G'],
            ],
            [['except' => ['yii\db\*']], ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']],
            [['categories' => ['yii*'], 'except' => ['yii\db\*']], ['G', 'H']],

            [['categories' => ['application', 'yii.db.*'], 'levels' => Logger::LEVEL_ERROR], ['B', 'G', 'H']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR], ['B']],
            [['categories' => ['application'], 'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING], ['B', 'C']],
        ];
    }

    /**
     * @return array<string, array{array, bool, string}>
     */
    public static function formatMessage(): array
    {
        $exception = new RuntimeException('Failure');

        return [
            'complex message' => [
                [['key' => 'value'], Logger::LEVEL_INFO, 'application', 1_508_160_390],
                false,
                '2017-10-16 13:26:30 [info][application] ' . VarDumper::export(['key' => 'value']),
            ],
            'integer timestamp with microseconds' => [
                ['message', Logger::LEVEL_INFO, 'application', 1_508_160_390],
                true,
                '2017-10-16 13:26:30.000000 [info][application] message',
            ],
            'message trace' => [
                [
                    'message',
                    Logger::LEVEL_INFO,
                    'application',
                    1_508_160_390,
                    [['file' => 'index.php', 'line' => 42]],
                ],
                false,
                "2017-10-16 13:26:30 [info][application] message\n    in index.php:42",
            ],
            'throwable message' => [
                [$exception, Logger::LEVEL_ERROR, 'application', 1_508_160_390],
                false,
                '2017-10-16 13:26:30 [error][application] ' . (string) $exception,
            ],
            'timestamp with microseconds' => [
                ['message', Logger::LEVEL_INFO, 'application', 1_508_160_390.6083],
                true,
                '2017-10-16 13:26:30.608300 [info][application] message',
            ],
            'without microseconds' => [
                ['message', Logger::LEVEL_INFO, 'application', 1_508_160_390.6083],
                false,
                '2017-10-16 13:26:30 [info][application] message',
            ],
        ];
    }
}

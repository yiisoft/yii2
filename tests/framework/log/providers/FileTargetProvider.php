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
 * Data provider for {@see \yiiunit\framework\log\FileTargetTest}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class FileTargetProvider
{
    /**
     * @return array<string, array{list<array{string, int, string, float, array}>, list<string>|null}>
     */
    public static function messages(): array
    {
        return [
            'empty formatted messages' => [
                array_fill(0, 10, ['', Logger::LEVEL_INFO, 'application', 0.0, []]),
                null,
            ],
            'multiple messages' => [
                array_fill(0, 3, ['xxx', Logger::LEVEL_INFO, 'application', 0.0, []]),
                ["xxx\n", "xxx\n", "xxx\n"],
            ],
            'null formatted messages' => [
                array_fill(0, 10, ['null', Logger::LEVEL_INFO, 'application', 0.0, []]),
                null,
            ],
            'single message' => [
                [['xxx', Logger::LEVEL_INFO, 'application', 0.0, []]],
                ["xxx\n"],
            ],
            'suppressed formatted message' => [
                [['yyy', Logger::LEVEL_INFO, 'application', 0.0, []]],
                null,
            ],
        ];
    }
}

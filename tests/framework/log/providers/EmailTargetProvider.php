<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\providers;

/**
 * Data provider for {@see \yiiunit\framework\log\EmailTargetTest}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class EmailTargetProvider
{
    /**
     * @return array<string, array{string|null, string}>
     */
    public static function subjects(): array
    {
        return [
            'configured subject' => ['Hello world', 'Hello world'],
            'default subject' => [null, 'Application Log'],
        ];
    }
}

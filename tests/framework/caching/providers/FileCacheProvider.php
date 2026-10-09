<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\caching\providers;

/**
 * Data provider for {@see \yiiunit\framework\caching\FileCacheTest} test cases.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 2.0.56
 */
final class FileCacheProvider
{
    /**
     * Provides cache operations on a key whose cache file does not exist, with their expected result.
     *
     * @phpstan-return array<string, array{string, array<int, mixed>, mixed}>
     */
    public static function missingCacheFile(): array
    {
        return [
            'add' => ['add', ['missing_cache_file', 'value', 1], true],
            'exists' => ['exists', ['missing_cache_file'], false],
            'get' => ['get', ['missing_cache_file'], false],
        ];
    }
}

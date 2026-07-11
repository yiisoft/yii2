<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\db\oci;

/**
 * Stream wrapper stub whose writes report zero bytes, forcing Oracle LOB stream creation to fail.
 *
 * Register it over the `php` protocol so `fopen('php://temp')` yields a stream that cannot buffer a payload.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ZeroWriteStreamWrapper
{
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- PHP streamWrapper API mandates these names.

    /**
     * @var resource|null Stream context set by PHP.
     */
    public $context;

    public function stream_open(string $path, string $mode, int $options, string|null &$openedPath): bool
    {
        return true;
    }

    public function stream_write(string $data): int
    {
        return 0;
    }

    public function stream_flush(): bool
    {
        return true;
    }

    public function stream_close(): void
    {
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}

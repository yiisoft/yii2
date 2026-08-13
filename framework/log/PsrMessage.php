<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\log;

use Stringable;
use Throwable;

/**
 * Stores a PSR-3 message together with its original level and context.
 *
 * String conversion performs the optional PSR-3 placeholder interpolation while ignoring context values that cannot
 * be safely converted to strings.
 *
 * @since 22.0
 */
final readonly class PsrMessage implements Stringable
{
    private string $message;

    /**
     * @param string|Stringable $message The PSR-3 message, cast to `string` eagerly so a throwing {@see Stringable}
     * fails at the logging call site instead of during a later target flush.
     * @param array<array-key, mixed> $context The PSR-3 context.
     * @param string $level The original PSR-3 level.
     */
    public function __construct(
        Stringable|string $message,
        private array $context,
        private string $level,
    ) {
        $this->message = (string) $message;
    }

    /**
     * Returns the message without placeholder interpolation.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the PSR-3 context.
     *
     * @return array<array-key, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Returns the original PSR-3 level.
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * Returns the message with safely convertible context placeholders interpolated.
     */
    public function __toString(): string
    {
        $replace = [];

        foreach ($this->context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replace['{' . $key . '}'] = (string) $value;
                continue;
            }

            if (!$value instanceof Stringable) {
                continue;
            }

            try {
                $replace['{' . $key . '}'] = (string) $value;
            } catch (Throwable) {
                // PSR-3 context values must never cause logging to fail.
            }
        }

        return strtr($this->message, $replace);
    }
}

<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\validators;

/**
 * Wraps a string and exposes it through `__toString()`, for validators that accept stringable objects.
 */
final class StringableValue implements \Stringable
{
    /**
     * @var string value returned by `__toString()`.
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

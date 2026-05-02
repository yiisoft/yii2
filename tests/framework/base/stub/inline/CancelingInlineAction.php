<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\inline;

use yii\base\InlineAction;

/**
 * Inline action subclass whose {@see beforeRun()} returns `false` to verify that the controller method is short
 * circuited.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CancelingInlineAction extends InlineAction
{
    public bool $afterRunCalled = false;

    protected function beforeRun(): bool
    {
        return false;
    }

    protected function afterRun(): void
    {
        $this->afterRunCalled = true;
    }
}

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
 * Inline action subclass that records {@see beforeRun()} and {@see afterRun()} invocations to verify the lifecycle
 * wrapper around the controller method call.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class TrackingInlineAction extends InlineAction
{
    public bool $beforeRunCalled = false;
    public bool $afterRunCalled = false;

    protected function beforeRun(): bool
    {
        $this->beforeRunCalled = true;

        return true;
    }

    protected function afterRun(): void
    {
        $this->afterRunCalled = true;
    }
}

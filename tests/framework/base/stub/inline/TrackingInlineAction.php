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
 * Inline action subclass that appends a token into {@see PingController::$callLog} on each lifecycle hook so the test
 * can assert the exact `beforeRun → controller method → afterRun` sequence.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class TrackingInlineAction extends InlineAction
{
    protected function beforeRun(): bool
    {
        PingController::$callLog[] = 'beforeRun';

        return true;
    }

    protected function afterRun(): void
    {
        PingController::$callLog[] = 'afterRun';
    }
}

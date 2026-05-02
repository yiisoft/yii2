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
 * Records `beforeRunFalse` into {@see PingController::$callLog} when the cancellation hook fires; if {@see afterRun()}
 * were ever invoked it would also record, so the test can assert the resulting log proves nothing else ran after the
 * short-circuit.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CancelingInlineAction extends InlineAction
{
    protected function beforeRun(): bool
    {
        PingController::$callLog[] = 'beforeRunFalse';

        return false;
    }

    protected function afterRun(): void
    {
        PingController::$callLog[] = 'afterRun';
    }
}

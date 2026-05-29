<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\Action;

/**
 * Action recording the `beforeRun()`, `run()`, and `afterRun()` call order for standalone lifecycle tests.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LifecycleRecordingAction extends Action
{
    /** @var list<string> */
    public array $calls = [];

    public function run(): string
    {
        $this->calls[] = 'run';

        return 'done';
    }

    protected function beforeRun(): bool
    {
        $this->calls[] = 'before';

        return true;
    }

    protected function afterRun(): void
    {
        $this->calls[] = 'after';
    }
}

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
 * Action used for testing that returning false from {@see \yii\base\Action::beforeRun()} prevents the action from
 * running.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class CancelingAction extends Action
{
    public bool $ran = false;

    public function run(): string
    {
        $this->ran = true;

        return 'should-not-reach';
    }

    protected function beforeRun(): bool
    {
        return false;
    }
}

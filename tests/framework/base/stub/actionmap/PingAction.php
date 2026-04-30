<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\actionmap;

use yii\base\Action;

/**
 * Action used for testing {@see \yii\base\Module::$actionMap}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PingAction extends Action
{
    public function run(string $name): string
    {
        return "pong-{$name}";
    }
}

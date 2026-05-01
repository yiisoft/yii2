<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\controller;

use yii\base\Action;

/**
 * Action class registered through {@see MappedActionController::actions()} for external-action resolution tests.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ExternalAction extends Action
{
    public function run(): string
    {
        return 'external';
    }
}

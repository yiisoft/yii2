<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

use yii\web\Action;
use yiiunit\framework\base\stub\standalone\ActionTestService;

/**
 * Action whose `run()` depends on a typed service, used to test module component and module DI resolution paths.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class ModuleServiceAction extends Action
{
    public function run(ActionTestService $service): string
    {
        return $service->name();
    }
}

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
 * Action used for testing nullable service injection in {@see \yii\web\Action::resolveStandaloneParams()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class NullableServiceAction extends Action
{
    public function run(?ActionTestService $service): string
    {
        return $service === null ? 'null' : $service->name();
    }
}

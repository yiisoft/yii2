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
 * Action used for testing standalone action resolver with method injection.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class MethodInjectingAction extends Action
{
    public function run(ActionTestService $service, int $id): string
    {
        return $service->name() . '-' . $id;
    }
}

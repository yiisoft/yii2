<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\standalone;

use yii\base\ActionFilter;

/**
 * Action filter used for testing that filters can be applied to standalone actions.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class TrackingFilter extends ActionFilter
{
    public static array $beforeCalls = [];

    public function beforeAction($action): bool
    {
        self::$beforeCalls[] = $action->getUniqueId();

        return true;
    }
}

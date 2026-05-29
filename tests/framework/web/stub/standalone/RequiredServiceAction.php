<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

use yii\web\Action;

/**
 * Action whose `run()` requires an unresolvable non-nullable dependency, used to assert HTTP-aware DI failure mapping.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class RequiredServiceAction extends Action
{
    public function run(UnregisteredDependency $dependency): string
    {
        return $dependency->value();
    }
}

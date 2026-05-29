<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stub\standalone;

/**
 * Service never registered in any container, used to force an unresolvable non-nullable dependency.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class UnregisteredDependency
{
    public function value(): string
    {
        return 'never';
    }
}
